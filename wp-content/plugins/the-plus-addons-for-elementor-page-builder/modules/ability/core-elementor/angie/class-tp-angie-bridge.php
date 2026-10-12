<?php
/**
 * TPAE <-> Angie bridge (PROTOTYPE).
 *
 * Bridges TPAE's WP-Abilities to Elementor's in-editor "Angie" AI assistant,
 * modelled on Elementor's ACF reference module and the UAE/HFE implementation
 * ("WS Form pattern"):
 *
 *   1. PHP exposes a curated ability list + a wildcard execute route (this file).
 *   2. JS (tpae-angie-mcp-server.js) fetches the list, registers each as an Angie
 *      tool via @elementor/angie-sdk, and routes tool calls back to the execute route.
 *   3. Angie calls a tool -> JS POSTs to REST -> this class runs the ability.
 *
 * IMPORTANT - why this exists even though Angie already auto-discovers TPAE abilities:
 * Angie's own `wp-abilities` module enumerates wp_get_abilities() and exposes every
 * third-party `mcp.type=tool` ability unconditionally (verified live: every TPAE
 * ability is already visible to Angie with no bridge). This bridge is therefore an
 * OPTIONAL CURATION layer: it gives Angie a reduced, safer tool surface. To avoid Angie
 * seeing a tool twice (once auto-discovered, once via this bridge), any ability exposed
 * here must be reclassified OFF `mcp.type=tool` at registration so Angie's generic
 * discovery skips it - see README.md in this folder. This prototype does NOT change the
 * abilities' mcp.type; it demonstrates the bridge mechanics only.
 *
 * @package The Plus Addons
 * @since   6.5.2
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Angie_Bridge' ) ) {

	/**
	 * Bridges TPAE's curated free abilities to Elementor's in-editor Angie AI.
	 *
	 * @since 6.5.2
	 */
	class Tp_Angie_Bridge {

		/**
		 * REST namespace for the Angie channel.
		 */
		const REST_NAMESPACE = 'tpae/v1';

		/**
		 * Built JS bundle, relative to the plugin root. Its presence is the master
		 * switch: only when it exists do we reclassify abilities off mcp.type='tool'
		 * and enqueue the editor script. Absent -> the whole feature stays dormant.
		 */
		const BUILT_REL = 'modules/ability/core-elementor/angie/tpae-angie-mcp-server.min.js';

		/**
		 * Ability-name prefixes this bridge exposes. FREE (`tpae/`) only: the free
		 * plugin owns the tooling/composite verbs that need curation, and scoping to
		 * free lets us reclassify only free abilities. Pro (`tpaep/`) widgets stay on
		 * Angie's generic discovery (they need no curation), so nothing double-lists.
		 *
		 * @var string[]
		 */
		private $prefixes = array( 'tpae/' );

		/**
		 * Curation: ability names to HIDE from Angie because its AI mis-uses complex /
		 * composite tools, or because they are redundant mirrors. These stay fully
		 * available on the external MCP channel (Claude/Cursor). Start conservative;
		 * tune from the in-editor test log. Names are full ability ids.
		 *
		 * @var string[]
		 */
		private $angie_hidden = array(
			// Redundant `elementor-*` mirrors of the bare verbs (same behaviour).
			'tpae/elementor-add-container',
			'tpae/elementor-update-container',
			'tpae/elementor-update-element',
			'tpae/elementor-duplicate-element',
			'tpae/elementor-move-element',
			'tpae/elementor-remove-element',
			'tpae/elementor-reorder-elements',
			'tpae/elementor-get-page-structure',
			'tpae/elementor-create-page',
			'tpae/elementor-batch-update',
			// Composite / raw-param tools Angie tends to mis-drive - prefer granular verbs.
			'tpae/build-page',
			'tpae/delete-page-content',
		);

		/**
		 * Singleton.
		 *
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Get the singleton instance.
		 *
		 * @return self
		 */
		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Hook the REST routes and the editor script enqueue.
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
			/*
			 * PERF-008: was 'admin_enqueue_scripts' -- unscoped, so once the three
			 * gate conditions in enqueue_scripts() are met, the ~491KB built bundle
			 * would load on every wp-admin screen (Plugins, Users, Settings, ...),
			 * not just the Elementor editor it's actually for. Every other
			 * editor-only enqueue in this codebase (copy-paste, template-editor,
			 * equal-height, gsap-main) already uses this hook; matched here.
			 */
			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Master switch: is the JS bundle built? Cached; drives both the mcp.type
		 * reclassification (in Tp_Ability_Main) and the editor enqueue.
		 */
		public static function is_built(): bool {
			static $built = null;
			if ( null === $built ) {
				$built = defined( 'L_THEPLUS_PATH' ) && file_exists( L_THEPLUS_PATH . self::BUILT_REL );
			}
			return $built;
		}

		/**
		 * Register the two Angie routes.
		 *
		 * A single wildcard execute route is used (not per-ability) because
		 * rest_api_init can fire before wp_abilities_api_init, so the ability
		 * list is not yet known when routes are registered. Routes are declared
		 * WITHOUT a trailing slash: WP strips it from the registered key and then
		 * a trailing-slash request no longer matches (returns rest_no_route). The
		 * JS client posts slash-free paths to match.
		 */
		public function register_rest_routes(): void {
			register_rest_route(
				self::REST_NAMESPACE,
				'/angie/abilities',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'handle_abilities_list' ),
					'permission_callback' => array( $this, 'check_permission' ),
				)
			);

			register_rest_route(
				self::REST_NAMESPACE,
				'/angie/(?P<ability_id>[a-zA-Z0-9_-]+)',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'handle_execute' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'ability_id' => array(
							'required'          => true,
							'validate_callback' => static function ( $param ) {
								return is_string( $param ) && (bool) preg_match( '/^[a-zA-Z0-9_-]+$/', $param );
							},
						),
					),
				)
			);
		}

		/**
		 * Route-level capability gate. Individual abilities re-check their own
		 * permissions on execute(), so this is defence in depth.
		 */
		public function check_permission(): bool {
			return current_user_can( 'edit_posts' );
		}

		/**
		 * Whether a given ability should be exposed to Angie.
		 *
		 * @param \WP_Ability $ability The ability to test.
		 * @return bool
		 */
		private function is_exposed( \WP_Ability $ability ): bool {
			$name = $ability->get_name();

			$has_prefix = false;
			foreach ( $this->prefixes as $prefix ) {
				if ( 0 === strpos( $name, $prefix ) ) {
					$has_prefix = true;
					break;
				}
			}
			if ( ! $has_prefix ) {
				return false;
			}

			// mcp.type is intentionally NOT checked here: when the bundle is built these
			// abilities are reclassified off mcp.type='tool' (so Angie's generic discovery
			// skips them) and this bridge re-registers the curated subset instead.
			return ! in_array( $name, $this->angie_hidden, true );
		}

		/**
		 * Build the runtime map of Angie id => full ability name for the exposed set.
		 * Collisions (two abilities mapping to the same Angie id) are dropped and logged.
		 *
		 * @return array<string,string>
		 */
		public function get_exposed_map(): array {
			$map = array();

			if ( ! function_exists( 'wp_get_abilities' ) ) {
				return $map;
			}

			foreach ( wp_get_abilities() as $ability ) {
				if ( ! $ability instanceof \WP_Ability || ! $this->is_exposed( $ability ) ) {
					continue;
				}

				$angie_id = self::ability_name_to_angie_id( $ability->get_name() );

				// First one wins; a collision means a duplicate id - skip the later one.
				if ( ! isset( $map[ $angie_id ] ) ) {
					$map[ $angie_id ] = $ability->get_name();
				}
			}

			return $map;
		}

		/**
		 * POST /tpae/v1/angie/abilities/ -> curated tool schemas for the JS to register.
		 *
		 * @return array<int,array<string,mixed>>
		 */
		public function handle_abilities_list() {
			$result = array();

			foreach ( $this->get_exposed_map() as $angie_id => $full_name ) {
				$ability = $this->get_ability( $full_name );
				if ( null === $ability ) {
					continue;
				}

				$result[] = array(
					'type'          => 'tool',
					'name'          => $angie_id,
					'label'         => $ability->get_label(),
					'description'   => $ability->get_description(),
					'input_schema'  => $this->schema_of( $ability, 'input' ),
					'output_schema' => $this->schema_of( $ability, 'output' ),
				);
			}

			return $result;
		}

		/**
		 * POST /tpae/v1/angie/{ability_id}/ -> resolve + execute one ability.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return array<string,mixed>|WP_Error
		 */
		public function handle_execute( $request ) {
			$angie_id = (string) $request['ability_id'];
			$map      = $this->get_exposed_map();

			if ( ! isset( $map[ $angie_id ] ) ) {
				return new WP_Error(
					'tpae_angie_unknown_tool',
					sprintf(
						/* translators: %s is the requested Angie tool id. */
						__( 'No exposed TPAE ability maps to "%s".', 'tpebl' ),
						$angie_id
					),
					array( 'status' => 404 )
				);
			}

			$ability = $this->get_ability( $map[ $angie_id ] );
			if ( null === $ability ) {
				return new WP_Error( 'tpae_angie_missing_ability', __( 'Ability disappeared from the registry.', 'tpebl' ), array( 'status' => 500 ) );
			}

			$input = $request->get_json_params();
			$input = is_array( $input ) ? $input : array();

			// WP_Ability::execute() runs the ability's own permission + input validation.
			$result = $ability->execute( $input );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return array(
				'ok'     => true,
				'result' => $result,
			);
		}

		/**
		 * Enqueue the JS MCP server, only when Angie is present.
		 *
		 * Gated the same way UAE gates theirs: Angie active (ANGIE_VERSION) AND the
		 * TPAE abilities switch on. Fails closed.
		 */
		public function enqueue_scripts(): void {
			if ( ! defined( 'ANGIE_VERSION' ) ) {
				return;
			}

			$conn = get_option( 'theplus_api_connection_data', array() );
			if ( empty( $conn['theplus_ability_switch'] ) || 'on' !== $conn['theplus_ability_switch'] ) {
				return;
			}

			if ( ! function_exists( 'wp_enqueue_script_module' ) ) {
				return; // WP < 6.5; ship a classic-script fallback build if needed.
			}

			// Enqueue the BUILT bundle only. Absent -> feature dormant (see is_built()).
			if ( ! self::is_built() ) {
				return;
			}

			$ver = defined( 'L_THEPLUS_VERSION' ) ? L_THEPLUS_VERSION : '1.0.0';

			/*
			Ship our own REST root + nonce rather than depending on core's wpApiSettings
			(not guaranteed present in the Elementor editor). A data-only classic script
			(src=false) prints `window.tpaeAngie` synchronously in the footer; the type=module
			bundle is deferred, so the global is always set before the module boots.
				*/
			wp_register_script( 'tpae-angie-data', false, array(), $ver, array( 'in_footer' => true ) );
			wp_enqueue_script( 'tpae-angie-data' );
			wp_localize_script(
				'tpae-angie-data',
				'tpaeAngie',
				array(
					'root'  => esc_url_raw( rest_url() ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			);

			wp_enqueue_script_module(
				'tpae-angie-mcp-server',
				L_THEPLUS_URL . self::BUILT_REL,
				array(),
				$ver,
				array( 'in_footer' => true )
			);
		}

		/* ------------------------------------------------------------------ helpers */

		/**
		 * Ability name -> Angie tool id. Keeps a single namespace marker so TPAE tools
		 * stay distinct in Angie's global tool registry, but collapses the doubled
		 * prefix the per-widget names carry:
		 *   tpae/add-container   -> tpae-add-container
		 *   tpae/tpae-icon       -> tpae-icon        (not tpae-tpae-icon)
		 *   tpaep/tpaep-button   -> tpaep-button     (not tpaep-tpaep-button)
		 * Angie tool ids must match ^[a-zA-Z0-9_-]+$.
		 *
		 * @param string $ability_name Full ability name (e.g. tpae/add-container).
		 * @return string
		 */
		public static function ability_name_to_angie_id( string $ability_name ): string {
			$angie_id = str_replace( '/', '-', $ability_name );
			// Collapse a doubled namespace prefix (tpaep first: it is the longer match).
			$angie_id = (string) preg_replace( '/^(tpaep|tpae)-\1-/', '$1-', $angie_id );
			return (string) preg_replace( '/[^a-zA-Z0-9_-]/', '', $angie_id );
		}

		/**
		 * Resolve a full ability name to its WP_Ability instance.
		 *
		 * @param string $full_name Full ability name.
		 * @return \WP_Ability|null
		 */
		private function get_ability( string $full_name ): ?\WP_Ability {
			if ( function_exists( 'wp_get_ability' ) ) {
				$ability = wp_get_ability( $full_name );
				return $ability instanceof \WP_Ability ? $ability : null;
			}

			foreach ( wp_get_abilities() as $ability ) {
				if ( $ability instanceof \WP_Ability && $ability->get_name() === $full_name ) {
					return $ability;
				}
			}
			return null;
		}

		/**
		 * Return an ability's input or output JSON schema, with a safe default.
		 *
		 * @param \WP_Ability $ability The ability.
		 * @param string      $which   Either 'input' or 'output'.
		 * @return array<string,mixed>
		 */
		private function schema_of( \WP_Ability $ability, string $which ): array {
			$method = 'input' === $which ? 'get_input_schema' : 'get_output_schema';
			if ( method_exists( $ability, $method ) ) {
				$schema = $ability->{$method}();
				if ( is_array( $schema ) && array() !== $schema ) {
					return $schema;
				}
			}
			return 'input' === $which
				? array(
					'type'       => 'object',
					'properties' => (object) array(),
				)
				: array( 'type' => 'object' );
		}
	}
}

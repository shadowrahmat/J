<?php
/**
 * TPAE "get-design-guide" ability.
 *
 * Serves TPAE's Agent Skills (SKILL.md + reference docs) to a connected AI client
 * (Claude/Cursor via MCP, or Elementor's Angie via the bridge), so the model loads
 * TPAE's build process and design guidance before it touches the site. Read-only;
 * returns Markdown.
 *
 * Serves ALL skills under modules/ability/skills/ - the `skill` argument selects
 * which (default `tpae-design`, the umbrella); companion skills are `tpae-motion`,
 * `tpae-woocommerce`, `tpae-theme-builder`. Within a skill, parts are discovered
 * dynamically:
 *   - `workflow` -> SKILL.md
 *   - `<name>`   -> references/<name>.md
 * so new skills and reference docs are served automatically with no change here.
 *
 * Registered under the `tpae/` namespace; loaded from
 * Tp_Ability_Main::tp_register_abilities().
 *
 * @package The Plus Addons
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the skills root (with trailing slash).
 */
function tpae_skills_root(): string {
	return L_THEPLUS_PATH . 'modules/ability/skills/';
}

/**
 * Discover skill names: every directory under the skills root that has a SKILL.md.
 *
 * @return string[]
 */
function tpae_skill_names(): array {
	$names = array();
	$dirs  = glob( tpae_skills_root() . '*', GLOB_ONLYDIR );
	if ( is_array( $dirs ) ) {
		sort( $dirs );
		foreach ( $dirs as $dir ) {
			if ( is_file( $dir . '/SKILL.md' ) ) {
				$slug = sanitize_key( basename( $dir ) );
				if ( '' !== $slug ) {
					$names[] = $slug;
				}
			}
		}
	}
	return $names;
}

/**
 * The default skill (umbrella) when the caller omits `skill`.
 */
function tpae_default_skill(): string {
	$names = tpae_skill_names();
	if ( in_array( 'tpae-design', $names, true ) ) {
		return 'tpae-design';
	}
	return $names[0] ?? '';
}

/**
 * Servable parts for one skill: slug => path relative to that skill dir. `workflow`
 * is SKILL.md; every references/*.md becomes a part named by its basename. Server-
 * controlled (a directory listing), never built from input. Returns empty for an
 * unknown skill.
 *
 * @param string $skill Skill name (validated against tpae_skill_names()).
 * @return array<string,string>
 */
function tpae_skill_parts( string $skill ): array {
	$skill = sanitize_key( $skill );
	if ( ! in_array( $skill, tpae_skill_names(), true ) ) {
		return array();
	}

	$parts = array();
	$dir   = tpae_skills_root() . $skill . '/';

	if ( is_file( $dir . 'SKILL.md' ) ) {
		$parts['workflow'] = 'SKILL.md';
	}

	$refs = glob( $dir . 'references/*.md' );
	if ( is_array( $refs ) ) {
		sort( $refs );
		foreach ( $refs as $file ) {
			$slug = sanitize_key( basename( $file, '.md' ) );
			if ( '' !== $slug && ! isset( $parts[ $slug ] ) ) {
				$parts[ $slug ] = 'references/' . basename( $file );
			}
		}
	}

	return $parts;
}

/**
 * Union of every part slug across all skills, for the input-schema enum.
 *
 * @return string[]
 */
function tpae_all_part_slugs(): array {
	$all = array();
	foreach ( tpae_skill_names() as $skill ) {
		foreach ( array_keys( tpae_skill_parts( $skill ) ) as $slug ) {
			$all[ $slug ] = true;
		}
	}
	return array_values( array_keys( $all ) );
}

if ( function_exists( 'wp_register_ability' ) ) {
	wp_register_ability(
		'tpae/get-design-guide',
		array(
			'label'               => __( 'Get Design Guide', 'tpebl' ),
			'description'         => __( 'Serves TPAE build skills and design references (Markdown) that govern how pages are planned, built, and verified. Fetch the "workflow" part of the "tpae-design" skill at the start of every session; load a companion skill (tpae-motion, tpae-woocommerce, tpae-theme-builder) for that domain.', 'tpebl' ),
			'category'            => 'tpae',
			'input_schema'        => array(
				'type'                 => 'object',
				'properties'           => array(
					'skill' => array(
						'type'        => 'string',
						'description' => __( 'Which skill to read. Defaults to "tpae-design" (the umbrella).', 'tpebl' ),
						'enum'        => tpae_skill_names(),
					),
					'part'  => array(
						'type'        => 'array',
						'description' => __( 'Which parts of the selected skill to return. Omit for "workflow" (the SKILL.md). Each references/*.md is a part named by its filename.', 'tpebl' ),
						'items'       => array(
							'type' => 'string',
							'enum' => tpae_all_part_slugs(),
						),
					),
				),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'       => 'object',
				'properties' => array(
					'skill' => array( 'type' => 'string' ),
					'parts' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'part'    => array( 'type' => 'string' ),
								'content' => array( 'type' => 'string' ),
							),
						),
					),
				),
			),
			'execute_callback'    => 'tpae_get_design_guide_ability',
			'permission_callback' => 'tpae_design_guide_permission',
			'meta'                => array(
				'show_in_rest' => true,
				'mcp'          => array(
					'public' => true,
					'type'   => 'tool',
				),
			),
		)
	);
}

/**
 * Permission check: anyone who can build pages may read the guides.
 *
 * @param array|null $input Ability input (unused; no post targeting).
 * @return bool
 */
function tpae_design_guide_permission( ?array $input = null ): bool {
	return current_user_can( 'edit_posts' );
}

/**
 * Serve the requested skill parts (SKILL.md / references) as Markdown.
 *
 * @param array $input Ability input.
 * @return array<string,mixed>|WP_Error
 */
function tpae_get_design_guide_ability( array $input ) {
	$skill = isset( $input['skill'] ) ? sanitize_key( (string) $input['skill'] ) : '';
	if ( '' === $skill ) {
		$skill = tpae_default_skill();
	}

	if ( ! in_array( $skill, tpae_skill_names(), true ) ) {
		return new WP_Error( 'unknown_skill', __( 'Unknown TPAE skill.', 'tpebl' ) );
	}

	$map = tpae_skill_parts( $skill );
	if ( array() === $map ) {
		return new WP_Error( 'skill_missing', __( 'The requested TPAE skill has no readable content.', 'tpebl' ) );
	}

	$requested = isset( $input['part'] ) && is_array( $input['part'] ) ? array_values( $input['part'] ) : array();
	if ( array() === $requested ) {
		$requested = array( 'workflow' );
	}

	$dir       = tpae_skills_root() . $skill . '/';
	$base_real = realpath( $dir );
	if ( false === $base_real ) {
		return new WP_Error( 'skill_missing', __( 'The requested TPAE skill directory was not found.', 'tpebl' ) );
	}

	$parts = array();
	$seen  = array();

	foreach ( $requested as $slug ) {
		$slug = sanitize_key( (string) $slug );

		if ( isset( $seen[ $slug ] ) || ! isset( $map[ $slug ] ) ) {
			continue;
		}
		$seen[ $slug ] = true;

		$file_real = realpath( $dir . $map[ $slug ] );

		// Defence in depth: the resolved file must stay inside this skill dir.
		if ( false === $file_real || 0 !== strpos( $file_real, $base_real ) || ! is_file( $file_real ) ) {
			continue;
		}

		$content = file_get_contents( $file_real ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading a bundled plugin file by a whitelisted, realpath-validated path.
		if ( false === $content ) {
			continue;
		}

		$parts[] = array(
			'part'    => $slug,
			'content' => $content,
		);
	}

	if ( array() === $parts ) {
		return new WP_Error( 'no_parts', __( 'No valid parts were requested for this skill.', 'tpebl' ) );
	}

	return array(
		'skill' => $skill,
		'parts' => $parts,
	);
}

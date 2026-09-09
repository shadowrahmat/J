<?php
/**
 * Shared context resolution for the Plus Addons dynamic tags.
 *
 * The post term tags each tested the archive first, so on a category or tag archive
 * every card in a loop reported the archive's term instead of its own.
 *
 * @link       https://posimyth.com/
 * @since      6.5.1
 *
 * @package    ThePlus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'L_ThePlus_Dynamic_Tag_Context' ) ) {

	/**
	 * Resolves which post or term a dynamic tag should read from.
	 *
	 * @since 6.5.1
	 */
	class L_ThePlus_Dynamic_Tag_Context {

		/**
		 * The post ID a tag should read from.
		 *
		 * @since 6.5.1
		 *
		 * @param bool $allow_editor_fallback Accept the editor's previewed post when the loop
		 *                                    has none. Skip it when a queried term is better.
		 *
		 * @return int Post ID, or 0 when none resolves.
		 */
		public static function get_post_id( bool $allow_editor_fallback = true ): int {

			$post_id = absint( get_the_ID() );

			if ( $post_id ) {
				return $post_id;
			}

			if ( $allow_editor_fallback && ! empty( $_REQUEST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- editor preview only, value is cast via absint().
				return absint( wp_unslash( $_REQUEST['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- editor preview only.
			}

			return 0;
		}

		/**
		 * Whether a post is being rendered that is not the post this request queried for.
		 *
		 * True inside a loop skin, an Elementor loop item, or a per card template.
		 *
		 * @since 6.5.1
		 *
		 * @return bool
		 */
		public static function is_loop_context(): bool {

			$post_id = absint( get_the_ID() );

			return ( $post_id && $post_id !== absint( get_queried_object_id() ) );
		}

		/**
		 * The terms a tag should read from, for the given taxonomy.
		 *
		 * @since 6.5.1
		 *
		 * @param string $taxonomy Taxonomy slug.
		 *
		 * @return array List of WP_Term, empty when nothing resolves.
		 */
		public static function get_terms( string $taxonomy ): array {

			if ( '' === $taxonomy ) {
				return array();
			}

			// Inside a loop the rendered post wins over the queried archive.
			if ( self::is_loop_context() ) {
				return self::post_terms( absint( get_the_ID() ), $taxonomy );
			}

			$queried = get_queried_object();

			if ( $queried instanceof WP_Term && $queried->taxonomy === $taxonomy ) {
				return array( $queried );
			}

			$post_id = self::get_post_id();

			return $post_id ? self::post_terms( $post_id, $taxonomy ) : array();
		}

		/**
		 * The single term a tag should read from, for the given taxonomy.
		 *
		 * @since 6.5.1
		 *
		 * @param string $taxonomy Taxonomy slug.
		 *
		 * @return WP_Term|null
		 */
		public static function get_term( string $taxonomy ) {

			$terms = self::get_terms( $taxonomy );

			return ! empty( $terms[0] ) ? $terms[0] : null;
		}

		/**
		 * A post's terms for a taxonomy, normalised to a plain list.
		 *
		 * @since 6.5.1
		 *
		 * @param int    $post_id  Post ID.
		 * @param string $taxonomy Taxonomy slug.
		 *
		 * @return array List of WP_Term.
		 */
		protected static function post_terms( int $post_id, string $taxonomy ): array {

			if ( ! $post_id ) {
				return array();
			}

			$terms = get_the_terms( $post_id, $taxonomy );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return array();
			}

			return array_values( $terms );
		}
	}
}

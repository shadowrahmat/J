<?php
/**
 * TPAE page-lifecycle abilities: duplicate-page and set-post-status.
 *
 * These back the tpae-design skill's own instructions - "duplicate-then-edit"
 * as the safety net, and "publishing needs explicit approval" - which previously
 * had no executable ability. Registered under the `tpae/` namespace alongside the
 * other core abilities (see layout-abilities.php).
 *
 * @package The Plus Addons
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_register_ability(
	'tpae/duplicate-page',
	array(
		'label'               => __( 'Duplicate Page', 'tpebl' ),
		'description'         => __( 'Duplicates a page/post - its title, content, and full Elementor data - as a new draft. The safe "duplicate-then-edit" starting point before risky changes.', 'tpebl' ),
		'category'            => 'tpae',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'post_id'   => array(
					'type'        => 'integer',
					'description' => 'Source page/post ID to duplicate.',
				),
				'new_title' => array(
					'type'        => 'string',
					'description' => 'Title for the copy. Defaults to "<original> (Copy)".',
				),
				'status'    => array(
					'type'        => 'string',
					'enum'        => array( 'draft', 'publish', 'pending', 'private' ),
					'description' => 'Status for the copy. Default: draft.',
					'default'     => 'draft',
				),
			),
			'required'             => array( 'post_id' ),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'new_post_id' => array( 'type' => 'integer' ),
				'edit_url'    => array( 'type' => 'string' ),
				'url'         => array( 'type' => 'string' ),
			),
		),
		'execute_callback'    => 'tpae_mcp_duplicate_page_ability',
		'permission_callback' => 'tpae_mcp_page_lifecycle_permission',
		'meta'                => array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
				'type'   => 'tool',
			),
		),
	)
);

wp_register_ability(
	'tpae/set-post-status',
	array(
		'label'               => __( 'Set Post Status', 'tpebl' ),
		'description'         => __( 'Changes a page/post status (draft, publish, pending, private, or trash). Publishing makes it live across the site - get the user\'s explicit approval first.', 'tpebl' ),
		'category'            => 'tpae',
		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'Page/post ID.',
				),
				'status'  => array(
					'type'        => 'string',
					'enum'        => array( 'draft', 'publish', 'pending', 'private', 'trash' ),
					'description' => 'New status.',
				),
			),
			'required'             => array( 'post_id', 'status' ),
			'additionalProperties' => false,
		),
		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'success' => array( 'type' => 'boolean' ),
				'post_id' => array( 'type' => 'integer' ),
				'status'  => array( 'type' => 'string' ),
				'url'     => array( 'type' => 'string' ),
			),
		),
		'execute_callback'    => 'tpae_mcp_set_post_status_ability',
		'permission_callback' => 'tpae_mcp_set_status_permission',
		'meta'                => array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
				'type'   => 'tool',
			),
		),
	)
);

/**
 * Whether the current user holds the target post type's publish capability.
 * $post_id may be 0 (no post yet, e.g. duplicate-page's source-side check) -
 * in that case there is nothing to look up a post type from, so this passes
 * by default and the caller is expected to have its own post_id gate first.
 *
 * @param int $post_id Post ID to resolve a post type from, or 0.
 * @return bool
 */
function tpae_mcp_can_publish_post( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return true;
	}
	$obj = get_post_type_object( (string) get_post_type( $post_id ) );
	return ! $obj || current_user_can( $obj->cap->publish_posts );
}

/**
 * Statuses that WordPress gates behind the post type's publish capability.
 *
 * Core treats "private" and "future" exactly like "publish" for capability
 * purposes (see _wp_translate_postdata()), so a guard that only tests for
 * "publish" leaves the other two open. Both lifecycle abilities allow
 * "private" in their input_schema enum, which was the remaining half of #766.
 * Kept as one list so extending an enum cannot silently reopen the hole.
 *
 * @param string $status Requested post status.
 * @return bool
 */
function tpae_mcp_status_needs_publish_cap( string $status ): bool {
	return in_array( $status, array( 'publish', 'private', 'future' ), true );
}

/**
 * Can edit posts, and (when a post_id is given) that specific post. Also
 * requires the publish capability whenever the requested status is
 * "publish" - duplicate-page's own input_schema allows status=publish, so
 * without this an editor without publish rights could use it to create a
 * live, public post from a draft they can only edit (see #766).
 *
 * @param array|null $input Ability input; may carry a post_id to target-check.
 * @return bool
 */
function tpae_mcp_page_lifecycle_permission( ?array $input = null ): bool {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}
	$post_id = absint( $input['post_id'] ?? 0 );
	if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( tpae_mcp_status_needs_publish_cap( (string) ( $input['status'] ?? '' ) ) && ! tpae_mcp_can_publish_post( $post_id ) ) {
		return false;
	}
	return true;
}

/**
 * Edit the post; and for publish, hold the post-type's publish capability;
 * and for trash, hold the delete capability too.
 *
 * The input_schema's status enum includes "trash", and the execute_callback
 * calls wp_trash_post() for it -- a destructive, delete-adjacent operation
 * (wp_trash_post() does no capability checking of its own). Without the
 * delete_post check below, a role granted edit_post but deliberately denied
 * delete_post (a normal WP capability split, e.g. an editorial-workflow role
 * that may edit any post but must not remove one) could trash any post they
 * can edit. class-page-abilities.php's check_delete_permission() already
 * requires both caps for the far milder delete-page-content ability (which
 * only blanks Elementor content, never the post itself) -- this ability
 * actually invokes wp_trash_post() and had no equivalent check. Same shape as
 * the already-fixed tpae/duplicate-page bug (#766): the schema accepted more
 * than this callback checked.
 *
 * @since 6.5.2
 *
 * @param array|null $input Ability input; reads post_id and the requested status.
 * @return bool
 */
function tpae_mcp_set_status_permission( ?array $input = null ): bool {
	$post_id = absint( $input['post_id'] ?? 0 );
	if ( $post_id <= 0 ) {
		return current_user_can( 'edit_posts' );
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	$status = (string) ( $input['status'] ?? '' );
	if ( tpae_mcp_status_needs_publish_cap( $status ) && ! tpae_mcp_can_publish_post( $post_id ) ) {
		return false;
	}
	if ( 'trash' === $status && ! current_user_can( 'delete_post', $post_id ) ) {
		return false;
	}
	return true;
}

/**
 * Duplicate a page/post (title, content, and Elementor meta) as a new draft.
 *
 * @param array $input Ability input (post_id, optional new_title, optional status).
 * @return array<string,mixed>|WP_Error
 */
function tpae_mcp_duplicate_page_ability( array $input ) {
	$post_id = absint( $input['post_id'] ?? 0 );
	if ( $post_id <= 0 ) {
		return new WP_Error( 'missing_post_id', __( 'The post_id parameter is required.', 'tpebl' ) );
	}

	$src = get_post( $post_id );
	if ( ! $src instanceof WP_Post ) {
		return new WP_Error( 'invalid_post', __( 'Source page was not found.', 'tpebl' ) );
	}

	$status = ( isset( $input['status'] ) && in_array( $input['status'], array( 'draft', 'publish', 'pending', 'private' ), true ) ) ? (string) $input['status'] : 'draft';
	$title  = ( isset( $input['new_title'] ) && '' !== (string) $input['new_title'] )
		? sanitize_text_field( (string) $input['new_title'] )
		: $src->post_title . ' (Copy)';

	$new_id = wp_insert_post(
		array(
			'post_title'     => $title,
			'post_content'   => $src->post_content,
			'post_excerpt'   => $src->post_excerpt,
			'post_status'    => $status,
			'post_type'      => $src->post_type,
			'post_parent'    => $src->post_parent,
			'menu_order'     => $src->menu_order,
			'comment_status' => $src->comment_status,
			'ping_status'    => $src->ping_status,
		),
		true
	);
	if ( is_wp_error( $new_id ) ) {
		return $new_id;
	}

	// Copy Elementor + template meta so the copy renders identically.
	$copy_meta = array( '_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_elementor_version', '_elementor_controls_usage', '_wp_page_template' );
	foreach ( $copy_meta as $mk ) {
		$v = get_post_meta( $post_id, $mk, true );
		if ( '' !== $v && false !== $v && null !== $v ) {
			update_post_meta( $new_id, $mk, wp_slash( $v ) );
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	return array(
		'new_post_id' => (int) $new_id,
		'edit_url'    => admin_url( 'post.php?post=' . (int) $new_id . '&action=elementor' ),
		'url'         => (string) get_permalink( $new_id ),
	);
}

/**
 * Change a page/post status (draft, publish, pending, private, or trash).
 *
 * @param array $input Ability input (post_id and status).
 * @return array<string,mixed>|WP_Error
 */
function tpae_mcp_set_post_status_ability( array $input ) {
	$post_id = absint( $input['post_id'] ?? 0 );
	$status  = (string) ( $input['status'] ?? '' );
	$allowed = array( 'draft', 'publish', 'pending', 'private', 'trash' );

	if ( $post_id <= 0 || ! in_array( $status, $allowed, true ) ) {
		return new WP_Error( 'missing_params', __( 'post_id and a valid status are required.', 'tpebl' ) );
	}
	if ( ! get_post( $post_id ) instanceof WP_Post ) {
		return new WP_Error( 'invalid_post', __( 'Post not found.', 'tpebl' ) );
	}

	if ( 'trash' === $status ) {
		if ( ! wp_trash_post( $post_id ) ) {
			return new WP_Error( 'trash_failed', __( 'Failed to trash the post.', 'tpebl' ) );
		}
	} else {
		$r = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $status,
			),
			true
		);
		if ( is_wp_error( $r ) ) {
			return $r;
		}
	}

	$fresh = get_post( $post_id );
	return array(
		'success' => true,
		'post_id' => $post_id,
		'status'  => $fresh instanceof WP_Post ? $fresh->post_status : $status,
		'url'     => (string) get_permalink( $post_id ),
	);
}

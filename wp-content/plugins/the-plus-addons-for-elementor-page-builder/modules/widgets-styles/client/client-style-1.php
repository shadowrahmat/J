<?php
/**
 * Client Style-1
 *
 * @package ThePlus
 * @since 1.0.0
 * @version 5.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( 'clrepeater' === $content_from ) {
	$client_url = $clientlink;
}

if ( 'clrepeater' !== $content_from ) {
	$postid     = get_the_ID();
	$client_url = get_post_meta( get_the_id(), 'theplus_clients_url', true );

	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php } ?>

	<div class="client-post-content">
		
		<?php
		/* Inside the 'clrepeater' !== $content_from branch, so this is always the post title. */
		$client_accessible_label = get_the_title();
		?>
		<div class="client-content-logo">
			<a href="<?php echo esc_url( $client_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $client_accessible_label ); ?>">
				<?php require L_THEPLUS_WSTYLES . 'client/format-image.php'; ?>
			</a>
		</div>

		<?php if ( 'yes' === $display_post_title ) { ?>

			<div class="post-content-bottom">
				<?php include L_THEPLUS_WSTYLES . 'client/post-meta-title.php'; ?>
			</div>

		<?php } ?>
	</div>

<?php if ( 'clrepeater' !== $content_from ) { ?>
	</article>
<?php } ?>

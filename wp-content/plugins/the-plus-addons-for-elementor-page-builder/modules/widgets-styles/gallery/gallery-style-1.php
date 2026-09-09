<?php
/**
 * Gallery style 1
 *
 * @package ThePlus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $attachment ) {
	$image_id = $attachment->ID;
} else {
	$image_id = $image_id;
}

$full_image = '';
$full_image = wp_get_attachment_url( $image_id, 'full' );

$bg_attr = '';
if ( 'metro' === $layout ) {
	if ( ! empty( $full_image ) ) {
		$bg_attr = 'style="background:url(' . esc_url( $full_image ) . ')"';
	} else {
		$bg_attr = l_theplus_loading_image_grid( $postid, 'background' );
	}
} ?>
<div class="gallery-list-content">

<?php if ( 'metro' !== $layout ) { ?>
<div class="post-content-image">
	<?php include L_THEPLUS_WSTYLES . 'gallery/format-image.php'; ?>
</div>
<?php } ?>
<div class="post-content-center"<?php echo ( 'metro' === $layout && ( empty( $settings['display_box_link'] ) || 'yes' !== $settings['display_box_link'] ) ) ? ' style="pointer-events:none"' : ''; ?>>		
	<div class="post-hover-content">
		<?php
		if ( 'yes' === $display_icon_zoom ) {
			include L_THEPLUS_WSTYLES . 'gallery/meta-icon.php';
		}
		?>
		<?php if ( ! empty( $image_icon ) && ! empty( $list_img ) ) { ?>
			<div class="gallery-list-icon"><?php echo wp_kses_post( $list_img ); ?></div>
		<?php } ?>
		<?php
		if ( 'yes' === $display_title ) {
			include L_THEPLUS_WSTYLES . 'gallery/meta-title.php';
		}
		?>
		<?php
		if ( 'yes' === $display_excerpt && ! empty( $caption ) ) {
			include L_THEPLUS_WSTYLES . 'gallery/get-excerpt.php';
		}
		?>
	</div>
</div>
<?php if ( 'metro' === $layout ) { ?>
	<?php if ( empty( $settings['display_box_link'] ) || 'yes' !== $settings['display_box_link'] ) { ?>
		<a class="gallery-bg-image-metro" href="<?php echo esc_url( $full_image ); ?>" <?php echo wp_kses_post( $popup_attr_icon1 ); ?> <?php echo wp_kses_post( $bg_attr ); ?>></a>
	<?php } else { ?>
		<div class="gallery-bg-image-metro" <?php echo wp_kses_post( $bg_attr ); ?>></div>
	<?php } ?>
<?php } ?>
</div>
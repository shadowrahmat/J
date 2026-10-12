<?php
/**
 * Get Meta Title Here
 *
 * @package ThePlus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $post_title_tag ) && empty( $post_title_tag ) ) {
	$post_title_tag = 'h3';
}

$tm_title = '';
if ( 'repeater' === $selct_source ) {
	$tm_title = $item['memberTitle'];
} else {
	$tm_title = get_the_title();
}

/*
 * TM2 (widget-test/team-member, Medium): for repeater items this used to
 * call get_the_permalink() unconditionally, which has no repeater item to
 * describe and returns the current page's own URL - so the member name
 * linked right back to the page it's displayed on, one of the 6-of-10
 * "links that go nowhere" measured in the audit. Use the member's own
 * configured URL ($member_url, set from item['customUrl'] in the repeater
 * loop) instead, and only wrap in <a> when it is actually set. The
 * post-type path is unchanged - get_the_permalink() is correct there.
 */
if ( 'repeater' === $selct_source ) {
	$tm_title_url = ! empty( $member_url ) ? $member_url : '';
} else {
	$tm_title_url = get_the_permalink();
}

?>
<<?php echo esc_attr( l_theplus_validate_html_tag( $post_title_tag ) ); ?> class="post-title">
	<?php if ( ! empty( $tm_title_url ) ) : ?>
	<a href="<?php echo esc_url( $tm_title_url ); ?>"><?php echo esc_html( $tm_title ); ?></a>
	<?php else : ?>
	<?php echo esc_html( $tm_title ); ?>
	<?php endif; ?>
</<?php echo esc_attr( l_theplus_validate_html_tag( $post_title_tag ) ); ?>>
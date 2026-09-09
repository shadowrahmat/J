<?php
/**
 * Testimonial get-excerpt
 *
 * @package ThePlus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( 'tlrepeater' === $con_from ) {
	$testimonial_author_text = wpautop( $testi_author );
} else {
	$testimonial_author_text = get_post_meta( get_the_id(), 'theplus_testimonial_author_text', true );
	$testimonial_author_text = wpautop( $testimonial_author_text );
}

if ( ! empty( $testimonial_author_text ) ) {

	if ( 'tlrepeater' === $con_from ) {

		$excerpt = '';

		if ( 'default' === $descby_limit ) {
			?>
				<div class="entry-content scroll-<?php echo esc_attr( $cntscroll_on ); ?>"><?php echo wp_kses_post( $testimonial_author_text ); ?></div>
			<?php
		} elseif ( 'words' === $descby_limit ) {

			$total = explode( ' ', $testimonial_author_text );
			$words = explode( ' ', $testimonial_author_text );

			$remaining_words = implode( ' ', array_slice( $total, $desc_limit - 1 ) );
			$limit_words     = implode( ' ', array_splice( $words, 0, $desc_limit - 1 ) );

			/*
			 * This branch computed both halves but never assigned $excerpt, so selecting the
			 * "words" description limit rendered an empty description rather than a truncated
			 * one -- only "letters" and "default" produced output. Mirrors the letters branch
			 * below and the words branch in post-meta-title.php.
			 *
			 * The separator is a space because the split is on word boundaries: without it the
			 * visible head would run into the hidden tail when the toggle expands.
			 */
			if ( count( $total ) >= $desc_limit ) {
				$excerpt = $limit_words . ' <span class="testi-more-text" style = "display:none" >' . wp_kses_post( $remaining_words ) . '</span><a ' . $attr . ' class="testi-readbtn"> ' . esc_html( $redmor_txt ) . ' </a>';
			} else {
				$excerpt = $limit_words;
			}

		} elseif ( 'letters' === $descby_limit ) {

			$ltn = strlen( $testimonial_author_text );

			$limit_words     = substr( $testimonial_author_text, 0, $desc_limit );
			$remaining_words = substr( $testimonial_author_text, $desc_limit, $ltn );

			if ( strlen( $testimonial_author_text ) > $desc_limit ) {
				/*
				 * $attr is a complete attribute string built in tp_testimonial_listout.php --
				 * data-readData='<json>' -- whose payload is already escaped with
				 * htmlspecialchars( ..., ENT_QUOTES ). Passing it through esc_attr() escaped its
				 * own quote delimiters too, so the browser received the JSON wrapped in literal
				 * apostrophes and JSON.parse() failed, leaving the read-more toggle without its
				 * labels. Emitted raw to match post-meta-title.php; wp_kses_post() still filters
				 * the assembled markup below.
				 */
				$excerpt = $limit_words . '<span class="testi-more-text" style = "display:none" >' . wp_kses_post( $remaining_words ) . '</span><a ' . $attr . ' class="testi-readbtn"> ' . esc_html( $redmor_txt ) . ' </a>';
			} else {
				$excerpt = $limit_words;
			}
		}
	}
	?>
		
<div class="entry-content">
	<?php
	if ( 'tlrepeater' === $con_from ) {
		echo wp_kses_post( $excerpt );
	} else {
		echo wp_kses_post( $testimonial_author_text );
	}
	?>
</div>
<?php } ?>

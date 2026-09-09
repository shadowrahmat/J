<?php
/**
 * Plus Addons.
 *
 * @link       https://posimyth.com/
 * @since      6.1.1
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $theplus_options, $post_type_options;

add_image_size( 'tp-image-grid', 700, 700, true );

function l_theplus_validate_html_tag( $check_tag ) {

	$tags = array( 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'span', 'p', 'header', 'footer', 'article', 'aside', 'main', 'nav', 'section' );

	return in_array( strtolower( $check_tag ), $tags ) ? $check_tag : 'div';
}

/* WOOCOMMERCE Mini Cart */
function l_theplus_woocomerce_ajax_cart_update( $fragments ) {
	if ( class_exists( 'woocommerce' ) ) {
		ob_start();
		?>			
			
			<div class="cart-wrap"><span><?php echo WC()->cart->get_cart_contents_count(); ?></span></div>
		<?php
		$fragments['.cart-wrap'] = ob_get_clean();
		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'l_theplus_woocomerce_ajax_cart_update', 10, 3 );

function l_theplus_get_thumb_url() {
	return L_THEPLUS_ASSETS_URL . 'images/placeholder-grid.jpg';
}

class L_Theplus_MetaBox {

	public static function get( $name ) {
		global $post;

		if ( isset( $post ) && ! empty( $post->ID ) ) {
			return get_post_meta( $post->ID, $name, true );
		}

		return false;
	}
}
function l_theplus_get_option( $options_type, $field ) {
	// Static variables persist within the same PHP request (reset on next request).
	// wp_cache layer persists across requests when Redis/Memcached is active (1-hour TTL).
	static $cached_options   = null;
	static $cached_post_type = null;

	if ( null === $cached_options ) {
		$cached_options = wp_cache_get( 'tpae_theplus_options', 'theplus_addons' );

		if ( false === $cached_options ) {
			$cached_options = get_option( 'theplus_options' );
			wp_cache_set( 'tpae_theplus_options', $cached_options, 'theplus_addons', HOUR_IN_SECONDS );
		}
	}

	if ( null === $cached_post_type ) {
		$cached_post_type = wp_cache_get( 'tpae_post_type_options', 'theplus_addons' );

		if ( false === $cached_post_type ) {
			$cached_post_type = get_option( 'post_type_options' );
			wp_cache_set( 'tpae_post_type_options', $cached_post_type, 'theplus_addons', HOUR_IN_SECONDS );
		}
	}

	$theplus_options   = $cached_options;
	$post_type_options = $cached_post_type;
	$values            = '';

	if ( $options_type == 'general' ) {
		if ( isset( $theplus_options[ $field ] ) && ! empty( $theplus_options[ $field ] ) ) {
			$values = $theplus_options[ $field ];
		}
	}
	if ( $options_type == 'post_type' ) {
		if ( isset( $post_type_options[ $field ] ) && ! empty( $post_type_options[ $field ] ) ) {
			$values = $post_type_options[ $field ];
		}
	}
	return $values;
}

/**
 * Invalidate the wp_cache layer of l_theplus_get_option when the backing
 * wp_options row is written.
 *
 * Without this, the wp_cache (1-hour TTL) returns stale widget config after
 * admin saves a TPAE dashboard setting — for up to HOUR_IN_SECONDS on transient-
 * backed sites and longer on Redis/Memcached-backed sites until eviction.
 *
 * Note: the static $cached_options inside l_theplus_get_option is function-local
 * and cannot be reset from this hook. In the unusual case where the SAME request
 * both writes the option AND re-reads it via l_theplus_get_option(), the re-read
 * returns the pre-write value. Subsequent requests always read fresh data.
 *
 * @since 6.5.0
 */
function l_theplus_invalidate_options_cache() {
	wp_cache_delete( 'tpae_theplus_options', 'theplus_addons' );
}
add_action( 'update_option_theplus_options', 'l_theplus_invalidate_options_cache' );
add_action( 'add_option_theplus_options',    'l_theplus_invalidate_options_cache' );
add_action( 'delete_option_theplus_options', 'l_theplus_invalidate_options_cache' );

function l_theplus_invalidate_post_type_options_cache() {
	wp_cache_delete( 'tpae_post_type_options', 'theplus_addons' );
}
add_action( 'update_option_post_type_options', 'l_theplus_invalidate_post_type_options_cache' );
add_action( 'add_option_post_type_options',    'l_theplus_invalidate_post_type_options_cache' );
add_action( 'delete_option_post_type_options', 'l_theplus_invalidate_post_type_options_cache' );

function l_theplus_excerpt( $limit ) {
	$limit = ! empty( $limit ) ? (int) $limit : 0;

	if ( method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
		WPBMap::addAllMappedShortcodes();
	}

	global $post;
	$excerpt = explode( ' ', get_the_excerpt(), $limit );
	if ( count( $excerpt ) >= $limit ) {
		array_pop( $excerpt );
		$excerpt = implode( ' ', $excerpt ) . '...';
	} else {
		$excerpt = implode( ' ', $excerpt );
	}

	$excerpt = preg_replace( '`[[^]]*]`', '', $excerpt );

	return $excerpt;
}

/**
 * Blog listing limit content
 *
 * @since v6.3.15
 */
function l_limit_words( $string, $word_limit ) {
	$words = explode( " ", $string );
	return implode( " ", array_splice( $words, 0, $word_limit ) );
}

function l_theplus_loading_image_grid( $postid = '', $type = '' ) {
	global $post;
	$content_image = '';
	if ( $type != 'background' ) {
		$image_url     = L_THEPLUS_ASSETS_URL . 'images/placeholder-grid.jpg';
		$content_image = '<img width="600" height="600" loading="lazy" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_the_title() ) . '"/>';

		return $content_image;

	} elseif ( $type == 'background' ) {

		$image_url = L_THEPLUS_ASSETS_URL . 'images/placeholder-grid.jpg';
		$data_src  = 'style="background-image:url(' . esc_url( $image_url ) . ');" ';

		return $data_src;

	}
}
function l_theplus_loading_bg_image( $postid = '' ) {
	global $post;

	$content_image = '';

	if ( ! empty( $postid ) ) {

		$featured_image = get_the_post_thumbnail_url( $postid, 'full' );
		if ( empty( $featured_image ) ) {
			$featured_image = l_theplus_get_thumb_url();
		}

		$content_image = 'style="background-image:url(' . esc_url( $featured_image ) . ');"';

		return $content_image;
	} else {
		return $content_image;
	}
}

/**
 * Simple decrypt function
 *
 * @since 6.0.4
 */
function L_tp_plus_simple_decrypt( $string, $action = 'dy' ) {

	$option_name_key = 'tp_key_random_generate';
    $secret_key = get_option( $option_name_key );
	
	if ( empty( $secret_key ) ) {
        $secret_key = wp_generate_password( 32, true, true );
        add_option( $option_name_key, $secret_key, '', 'no' );
    }

	$key = hash( 'sha256', $secret_key, true );
    $cipher = 'aes-256-gcm';

	if ( $action === 'ey' ) {
        $iv = random_bytes(12); 
        $tag = '';

        $ciphertext = openssl_encrypt( $string, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ( false === $ciphertext ) {
            return false;
        }

        $encoded = base64_encode( $iv . $tag . $ciphertext );
		$encoded = str_replace( ['+', '/', '='], ['-', '_', ''], $encoded );
		return $encoded;
    } elseif ( $action === 'dy' ) {

		$string = str_replace( ['-', '_'], ['+', '/'], $string );

		$padding = strlen($string) % 4;
		if ($padding) {
			$string .= str_repeat('=', 4 - $padding);
		}

        $decoded = base64_decode( $string, true );
		
        if ( false === $decoded || strlen($decoded) < 28 ) {
			return false;
		}

        $iv  = substr( $decoded, 0, 12 );
        $tag = substr( $decoded, 12, 16 );
        $ciphertext = substr( $decoded, 28 );

		if ( empty( $iv ) || empty( $tag ) || empty( $ciphertext ) ) {
            return false;
        }

        return openssl_decrypt( $ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag );
	}

	return false;
}

/**
 * Metro layout for ajax load
 *
 * @since 6.1.1
 */
function L_theplus_load_metro_style_layout( $columns = '1', $metro_column = '3', $metro_style = 'style-1' ) {
	$i = ! empty( $columns ) ? $columns : 1;

	if ( ! empty( $metro_column ) ) {
		// style-3
		if ( $metro_column == '3' && 'style-1' === $metro_style ) {
			$i = ( $i <= 10 ) ? $i : ( $i % 10 );
		}
		if ( $metro_column == '3' && 'style-2' === $metro_style ) {
			$i = ( $i <= 9 ) ? $i : ( $i % 9 );
		}
		if ( $metro_column == '3' && 'style-3' === $metro_style ) {
			$i = ( $i <= 15 ) ? $i : ( $i % 15 );
		}
		if ( $metro_column == '3' && 'style-4' === $metro_style ) {
			$i = ( $i <= 8 ) ? $i : ( $i % 8 );
		}
		// style-4
		if ( $metro_column == '4' && 'style-1' === $metro_style ) {
			$i = ( $i <= 12 ) ? $i : ( $i % 12 );
		}
		if ( $metro_column == '4' && 'style-2' === $metro_style ) {
			$i = ( $i <= 14 ) ? $i : ( $i % 14 );
		}
		if ( $metro_column == '4' && 'style-3' === $metro_style ) {
			$i = ( $i <= 12 ) ? $i : ( $i % 12 );
		}
		// style-5
		if ( $metro_column == '5' && 'style-1' === $metro_style ) {
			$i = ( $i <= 18 ) ? $i : ( $i % 18 );
		}
		// style-6
		if ( $metro_column == '6' && 'style-1' === $metro_style ) {
			$i = ( $i <= 16 ) ? $i : ( $i % 16 );
		}
	}

	return $i;
}

add_action(
	'elementor/widgets/register',
	function ( $widgets_manager ) {
		$elementor_widget_blacklist = array( 'plus-elementor-widget' );

		foreach ( $elementor_widget_blacklist as $widget_name ) {
			$widgets_manager->unregister( $widget_name );
		}
	},
	15
);

/**
 * Registered widgets.
 *
 * @since 5.4.1
 */
function l_registered_widgets() {
	$widgets = include L_THEPLUS_PATH . 'config.php';

	return apply_filters( 'theplus/l_registered_widgets', $widgets );
}


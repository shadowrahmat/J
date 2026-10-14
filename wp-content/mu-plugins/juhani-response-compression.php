<?php
/**
 * Plugin Name: Juhani Frontend Response Compression
 * Description: Compress public HTML when PHP zlib is available and server compression is absent. Does not cache pages or alter their content.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'template_redirect', function () {
    if (
        PHP_SAPI === 'cli' ||
        is_admin() ||
        is_user_logged_in() ||
        is_feed() ||
        is_preview() ||
        headers_sent() ||
        ! extension_loaded( 'zlib' ) ||
        ini_get( 'zlib.output_compression' ) ||
        in_array( 'ob_gzhandler', ob_list_handlers(), true )
    ) {
        return;
    }

    // If Apache already compresses responses, let it own compression.
    if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_deflate', apache_get_modules(), true ) ) {
        return;
    }

    // PHP negotiates gzip/deflate and sets Content-Encoding and Vary itself.
    ini_set( 'zlib.output_compression', '1' );
}, -100 );

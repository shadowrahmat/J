<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      5.3.4
 * @version    6.4.2
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Theplus\Notices;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Dashboard_Overview' ) ) {

	/**
	 * This class used for only load All Notice Files
	 *
	 * @since 5.3.4
	 * @version    6.4.2
	 */
	class Tp_Dashboard_Overview {

		/**
		 * Instance
		 *
		 * @since 5.3.4
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * API Overview Option
		 *
		 * @var string
		 */
		public $T_P_R_S_S_U_R_L = 'https://theplusaddons.com/wp-content/tpae-feed-cache.json';

		/**
		 * API Overview Data
		 *
		 * @var string
		 */
		public $overview_data = array();

		/**
		 * API Transient key
		 *
		 * @var string
		 */
		public $transient_key = 'tp_dashboard_overview';

		/**
		 * Short-lived marker written when the feed fetch fails, so a failing or
		 * slow endpoint is not retried on every dashboard request.
		 *
		 * Kept separate from $transient_key on purpose: the success payload is
		 * handed to the React dashboard as its "whatsnew" data, so a failure
		 * must not be stored under the same key where it would render as
		 * content.
		 *
		 * @since 6.5.2
		 * @var string
		 */
		public $retry_key = 'tp_dashboard_overview_retry';

		/**
		 * How long to wait before retrying after a failed fetch.
		 *
		 * @since 6.5.2
		 * @var int
		 */
		public $retry_after = 900;

		/**
		 * Seconds to wait on the feed endpoint.
		 *
		 * Was 25, which blocked the request for up to 25 seconds on a slow
		 * host. This runs while an administrator waits for a page, so it is
		 * bounded tightly; the data is non-essential and its absence only
		 * hides one dashboard card.
		 *
		 * @since 6.5.2
		 * @var int
		 */
		public $request_timeout = 5;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 5.3.4
		 * @access public
		 * @static
		 * @return instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Perform some compatibility checks to make sure basic requirements are meet.
		 *
		 * @since 5.3.4
		 * @access public
		 */
		public function __construct() {
			/*
			 * Deliberately NOT fetched here. This class is instantiated during
			 * plugin load on every admin request (widgets_loader.php ->
			 * class-tp-notices-main.php), so calling out to the network in the
			 * constructor blocked every admin page -- the Elementor editor
			 * included -- before anything else could run.
			 *
			 * admin_init also fires on admin-ajax.php, and it fires before the
			 * wp_ajax_* action, so the cache is warm by the time
			 * class-tpae-dashboard-ajax.php reads the transient. That read is
			 * the only consumer of this data anywhere in the plugin.
			 */
			add_action( 'admin_init', array( $this, 'tp_maybe_refresh_overview' ) );
		}

		/**
		 * Fetch the feed only when the TPAE dashboard is what is being loaded.
		 *
		 * @since 6.5.2
		 * @access public
		 */
		public function tp_maybe_refresh_overview() {
			if ( ! $this->tp_is_dashboard_request() ) {
				return;
			}

			$this->tp_call_api_dashboard_overview();
		}

		/**
		 * Is this request the TPAE dashboard screen, or its AJAX call?
		 *
		 * The data feeds one card on that screen and is read nowhere else, so
		 * every other admin request has no reason to fetch it.
		 *
		 * @since 6.5.2
		 * @access private
		 * @return bool
		 */
		private function tp_is_dashboard_request() {
			/*
			 * Routing gate only, read-only, no state change -- the nonce and
			 * capability checks live in the handler this runs ahead of
			 * (class-tpae-dashboard-ajax.php::tpae_dashboard_ajax_call).
			 */
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			if ( 'theplus_welcome_page' === $page ) {
				return true;
			}

			if ( wp_doing_ajax() ) {
				$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

				if ( 'tpae_dashboard_ajax_call' === $action ) {
					return true;
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			return false;
		}

		/**
		 * Whats's New Data Get
		 *
		 * @since 6.4.2
		 * @version 6.5.2
		 */
		public function tp_call_api_dashboard_overview() {
			$data = get_transient( $this->transient_key );

			if ( false === $data || empty( $data ) ) {

				/*
				 * A failed fetch used to write nothing, so the next request
				 * tried again -- and the next. With an unreachable host that
				 * was an unbounded retry loop, one blocking request per page
				 * load. Back off instead.
				 */
				if ( false !== get_transient( $this->retry_key ) ) {
					return;
				}

				$feed_url = $this->T_P_R_S_S_U_R_L;

				$response = wp_remote_get( $feed_url, array( 'timeout' => $this->request_timeout ) );

				$status_code = wp_remote_retrieve_response_code( $response );

				if ( is_wp_error( $response ) || 200 !== (int) $status_code ) {
					$this->overview_data = array(
						'HTTP_CODE' => $status_code,
						'success'   => 0,
						'message'   => 'RSS feed fetch failed',
						'data'      => array(),
					);

					set_transient( $this->retry_key, 1, $this->retry_after );

					return;
				}

				$body = wp_remote_retrieve_body( $response );

				$data = json_decode( $body, true );

				if ( ! is_array( $data ) ) {
					/* Malformed JSON is a failed fetch too, and was retried just as hard. */
					set_transient( $this->retry_key, 1, $this->retry_after );

					return;
				}

				$this->overview_data = array(
					'HTTP_CODE' => $status_code,
					'success'   => 1,
					'message'   => 'RSS data fetched successfully',
					'data'      => $data,
				);

				set_transient( $this->transient_key, $this->overview_data, 4 * DAY_IN_SECONDS );

				delete_transient( $this->retry_key );

			} else {
				$this->overview_data = $data;
			}
		}
	}

	Tp_Dashboard_Overview::instance();
}

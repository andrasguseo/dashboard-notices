<?php
/**
 * Plugin Name:       Dashboard Notices
 * Plugin URI:
 * GitHub Plugin URI: https://github.com/andrasguseo/dashboard-notices
 * Description:       The plugin hides admin notices and shows them only on a dedicated page.
 * Version:           1.1.0
 * Plugin Class:      AGU_Dashboard_Notices
 * Author:            Andras Guseo
 * Author URI:        https://andrasguseo.com
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       agu-dashboard-notices
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

if ( ! class_exists( 'AGU_Dashboard_Notices' ) ) {
	class AGU_Dashboard_Notices {

		protected const PLUGIN_SLUG = 'dashboard-notices';

		/**
		 * Constructor.
		 */
		public function __construct() {
			require_once( plugin_dir_path( __FILE__ ) . 'vendor/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php' );

			add_action( 'admin_init', [ 'PAnD', 'init' ] );

			add_action( 'admin_menu', [ $this, 'add_notices_page' ] );

			add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );

			add_action( 'admin_bar_menu', [ $this, 'admin_bar_item' ], 500 );

			add_action( 'admin_notices', [ $this, 'admin_notice' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_custom_scripts' ] );
		}

		/**
		 * Add an admin page for notices.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function add_notices_page() {
			$page_title = 'Notices <span id="notice-count" class="notice-count">0</span>';

			add_dashboard_page(
				'Notices',           // Page title
				$page_title,           // Menu title
				'manage_options',          // Capability required
				'dashboard-notices', // Menu slug
				[ $this, 'render_notices_page' ]    // Callback function to display the page content
			);
		}

		/**
		 * Render the notices page.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function render_notices_page() {
			$item = $this->get_random_item();

			echo '<div class="wrap">';
			echo '<h2>Notices</h2>';
			echo '<p id="hurray">';
			esc_html_e( 'Hurray, there are no notices! 🎉', 'agu-dashboard-notices' );
			echo '</p>';
			echo '<p class="donate">';
			printf(
				esc_html__(
					'If you find this plugin useful, please consider %1$schipping in%2$s to my %3$s. Thanks!',
					'agu-dashboard-notices'
				),
				'<a href="https://paypal.me/guseo?country.x=CH&locale.x=en_US" target="_blank">',
				'</a>',
				'<span class="emoji">' . $item . '</span>'
			);
			echo '</p>';
			echo '</div>';
		}

		/**
		 * Add a body class to the notices page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $classes The list of body classes.
		 *
		 * @return string
		 */
		public function add_body_class( $classes ) {
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'dashboard-notices' ) {
				$classes .= ' dashboard-notices';
			}

			return $classes;
		}

		/**
		 * Add an entry to the admin bar.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Admin_Bar $admin_bar
		 *
		 * @return void
		 */
		function admin_bar_item( WP_Admin_Bar $admin_bar ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$title = '🔔 <span id="admin-bar-notice-count" class="notice-count">0</span>';
			$admin_bar->add_menu(
				[
					'id'     => 'dashboard-notices',
					'parent' => 'top-secondary',
					'group'  => null,
					'title'  => $title,
					'href'   => admin_url( 'admin.php?page=dashboard-notices' ),
					'meta'   => [
						'title' => __( 'Notices', 'agu-dashboard-notices' ),
					],
				]
			);

			// Link to show notices
			$show_notices_link = esc_url( add_query_arg( 'show_notices', '1', $_SERVER['REQUEST_URI'] ) );

			$admin_bar->add_menu(
				[
					'id'     => 'dashboard-notices-display',
					'parent' => 'dashboard-notices',
					'title'  => __( 'Display notices', 'agu-dashboard-notices' ),
					'href'   => $show_notices_link,
					'meta'   => [
						'title' => __( 'Display notices', 'agu-dashboard-notices' ),
						'class' => 'my_menu_item_class',
					],
				]
			);
		}

		/**
		 * Add our own admin notice.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_notice() {
			$dismiss_slug = 'notice-one-' . $this->dismiss_notice_days();

			// Bail, if notice is on dismiss.
			if ( ! PAnD::is_admin_notice_active( $dismiss_slug ) ) {
				return;
			}

			if (
				! $this->in_url_param()
				&& ! $this->is_on_page()
			) {
				echo '<div data-dismissible="' . $dismiss_slug . '" id="notice--dashboard-notices" class="notice is-dismissible notice--dashboard-notices"><p>';
				printf(
					esc_html__( 'Your can %1$sfind your notices here%2$s.', 'agu-dashboard-notices' ),
					'<a href="' . admin_url( 'index.php?page=dashboard-notices' ) . '">',
					'</a>'
				);
				echo '<span style="float:right">Dismiss for 7 days →</span></p></div>';
			}
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function enqueue_custom_scripts() {
			// Enqueue base CSS file
			wp_enqueue_style(
				self::PLUGIN_SLUG . '-base-style',
				plugin_dir_url( __FILE__ ) . 'resources/css/base.css',
				[],
				'1.0',
				'all'
			);

			// Enqueue script
			wp_enqueue_script(
				self::PLUGIN_SLUG . '-script',
				plugin_dir_url( __FILE__ ) . 'resources/js/script.js',
				[],
				'1.0',
				true
			);

			// Bail, if using the URL parameter.
			if ( $this->in_url_param() ) {
				return;
			}

			// Enqueue CSS file
			wp_enqueue_style(
				self::PLUGIN_SLUG . '-style',
				plugin_dir_url( __FILE__ ) . 'resources/css/hide.css',
				[],
				'1.0',
				'all'
			);

		}

		/**
		 * Return a random item.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_random_item() {
			$items      = [
				'☕',
				'🍕',
				'🍩',
				'🥗',
				'🍔',
				'🥩',
				'🥐',
				'🍪',
				'🌮',
				'🍣',
				'🥩',
			];
			$random_key = array_rand( $items );

			return $items[ $random_key ];
		}

		/**
		 * Check the url parameter value.
		 *
		 * @since 1.1.0
		 * @return bool
		 */
		public function in_url_param() {
			if (
				isset( $_GET['show_notices'] )
				&& $_GET['show_notices'] === "1"
			) {
				return true;
			}

			return false;
		}

		/**
		 * Check if we're on the Notices page.
		 *
		 * @since 1.1.0
		 * @return bool
		 */
		public function is_on_page() {
			if (
				isset( $_GET['page'] )
				&& $_GET['page'] === 'dashboard-notices'
			) {
				return true;
			}

			return false;
		}

		/**
		 * The number of days the notice should sleep.
		 *
		 * @since 1.1.1
		 * @return int
		 */
		public function dismiss_notice_days() {
			return (int) apply_filters( 'agu_dashboard_notices_dismiss_notice_days', 7 );
		}
	}

	new AGU_Dashboard_Notices();
}
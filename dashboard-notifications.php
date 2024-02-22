<?php
/**
 * Plugin Name:       Dashboard Notifications
 * Plugin URI:
 * GitHub Plugin URI: https://github.com/andrasguseo/dashboard-notifications
 * Description:       The plugin hides admin notifications and shows them only on a dedicated page.
 * Version:           1.0.0
 * Plugin Class:      AGU_Dashboard_Notifications
 * Author:            Andras Guseo
 * Author URI:        https://andrasguseo.com
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       agu-dashboard-notifications
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

if ( ! class_exists( 'AGU_Dashboard_Notifications' ) ) {
	class AGU_Dashboard_Notifications {

		protected const PLUGIN_SLUG = 'dashboard-notifications';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'add_notifications_page' ] );

			add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );

			add_action( 'admin_bar_menu', [ $this, 'admin_bar_item' ], 500 );

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_custom_scripts' ] );
		}

		/**
		 * Add an admin page for notifications.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function add_notifications_page() {
			$page_title = 'Notifications <span id="notification-count" class="notification-count">0</span>';

			add_dashboard_page(
				'Notifications',           // Page title
				$page_title,           // Menu title
				'manage_options',          // Capability required
				'dashboard-notifications', // Menu slug
				[ $this, 'render_notifications_page' ]    // Callback function to display the page content
			);
		}

		/**
		 * Render the notifications page.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function render_notifications_page() {
			$item = $this->get_random_item();

			echo '<div class="wrap">';
			echo '<h2>Notifications</h2>';
			echo '<p id="content">';
			esc_html_e( 'Hurray, there are no annoying notifications! 🎉', 'agu-dashboard-notifications' );
			echo '</p>';
			echo '<p class="donate">';
			printf(
				esc_html__(
					'If you find this plugin useful, please consider %1$schipping in%2$s to my %3$s. Thanks!',
					'agu-dashboard-notifications'
				),
				'<a href="https://paypal.me/guseo?country.x=CH&locale.x=en_US" target="_blank">',
				'</a>',
				'<span class="emoji">' . $item . '</span>'
			);
			echo '</p>';
			echo '</div>';
		}

		/**
		 * Add a body class to the notifications page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $classes The list of body classes.
		 *
		 * @return string
		 */
		public function add_body_class( $classes ) {
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'dashboard-notifications' ) {
				$classes .= ' dashboard-notifications';
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

			$title = '🔔 <span id="admin-bar-notification-count" class="notification-count">0</span>';
			$admin_bar->add_menu(
				[
					'id'     => 'dashboard-notifications',
					'parent' => 'top-secondary',
					'group'  => null,
					'title'  => $title,
					'href'   => admin_url( 'admin.php?page=dashboard-notifications' ),
					'meta'   => [
						'title' => __( 'Notifications', 'agu-dashboard-notifications' ),
					],
				]
			);

			// Link to show notices
			$show_notices_link = esc_url( add_query_arg( 'show_notices', '1', $_SERVER['REQUEST_URI'] ) );

			$admin_bar->add_menu(
				[
					'id'     => 'dashboard-notifications-display',
					'parent' => 'dashboard-notifications',
					'title'  => __( 'Display notifications', 'agu-dashboard-notifications' ),
					'href'   => $show_notices_link,
					'meta'   => [
						'title' => __( 'Display notifications', 'agu-dashboard-notifications' ),
						'class' => 'my_menu_item_class',
					],
				]
			);
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function enqueue_custom_scripts() {
			// Bail, if using the URL parameter.
			if (
				isset( $_GET['show_notices'] )
				&& $_GET['show_notices'] === "1"
			) {
				return;
			}

			// Enqueue CSS file
			wp_enqueue_style(
				self::PLUGIN_SLUG . '-style',
				plugin_dir_url( __FILE__ ) . 'resources/css/style.css',
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
	}

	new AGU_Dashboard_Notifications();
}
<?php
/**
 * Plugin Name:       Archive Comments
 * Plugin URI:        https://andrasguseo.com/archive-comments
 * GitHub Plugin URI: https://github.com/andrasguseo/archive-comments
 * Description:       Makes it possible to archive comments. Archived comments can be viewed on a separate page, and they can be unarchived.
 * Version:           1.0.0
 * Plugin Class:      AGU_Archive_Comments
 * Author:            Andras Guseo
 * Author URI:        https://andrasguseo.com
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       archive-comments
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

if ( ! class_exists( 'AGU_Archive_Comments' ) ) {
	class AGU_Archive_Comments {

		public function __construct() {
			// Add row action to comments list table
			add_filter( 'comment_row_actions', [ $this, 'add_archive_row_action' ], 10, 2 );

			// Handle archiving of comments
			add_action( 'admin_init', [ $this, 'handle_comment_archiving' ] );

			// Load text domain
			add_action( 'admin_init', [ $this, 'load_textdomain' ] );

			// Add archived comments count to comment list views
			add_filter( 'views_edit-comments', [ $this, 'add_archived_comments_count' ] );

			// Add submenu under Comments
			add_action( 'admin_menu', [ $this, 'add_archived_comments_submenu' ] );

			// Add link to the plugin meta row.
			add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Load text domain.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		function load_textdomain() {
			$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
			load_plugin_textdomain( 'archive-comments', false, $plugin_rel_path );
		}

		/**
		 * Add row action to comments list table.
		 *
		 * @param string[]   $actions An array of comment actions.
		 * @param WP_Comment $comment The comment object.
		 *
		 * @return string[]
		 * @since 1.0.0
		 */
		public function add_archive_row_action( $actions, $comment ) {
			$actions[ 'archive' ] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( admin_url( "comment.php?action=archivecomment&c=$comment->comment_ID" ) ),
				esc_attr( __( 'Archive this comment', 'archive-comments' ) ),
				esc_html__( 'Archive', 'archive-comments' )
			);

			return $actions;
		}

		/**
		 * Handle the archiving and unarchiving of comments.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function handle_comment_archiving() {
			// Archiving
			if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'archivecomment' ) {
				$comment_id = isset( $_GET[ 'c' ] ) ? intval( $_GET[ 'c' ] ) : 0;
				if ( $comment_id > 0 ) {
					$this->set_comment_status( $comment_id, 'archive' );
					wp_redirect( admin_url( 'edit-comments.php' ) );
					exit;
				}
			}

			// Unarchiving
			if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'unarchivecomment' ) {
				$comment_id = isset( $_GET[ 'c' ] ) ? intval( $_GET[ 'c' ] ) : 0;
				if ( $comment_id > 0 ) {
					$this->set_comment_status( $comment_id, 0 );
					wp_redirect( admin_url( 'edit-comments.php?page=archived-comments' ) );
					exit;
				}
			}
		}

		/**
		 * Set the comment status.
		 * Note: WordPress doesn't officially support custom statuses for comments.
		 *
		 * @param int      $comment_id     The Comment ID.
		 * @param string   $comment_status The new comment status.
		 * @param WP_Error $wp_error       Whether to return a WP_Error object if there is a failure. Default false.
		 *
		 * @return bool|WP_Error True on success, false or WP_Error on failure.
		 *
		 * @since 1.0.0
		 * @see   wp_set_comment_status()
		 */
		public function set_comment_status( $comment_id, $comment_status = 'archive', $wp_error = false ) {
			global $wpdb;

			$comment_old = clone get_comment( $comment_id );

			if ( ! $wpdb->update( $wpdb->comments, [ 'comment_approved' => $comment_status ], [ 'comment_ID' => $comment_old->comment_ID ] ) ) {
				if ( $wp_error ) {
					return new WP_Error( 'db_update_error', __( 'Could not update comment status.' ), $wpdb->last_error );
				} else {
					return false;
				}
			}

			clean_comment_cache( $comment_old->comment_ID );

			$comment = get_comment( $comment_old->comment_ID );

			wp_update_comment_count( $comment->comment_post_ID );

			return true;
		}

		/**
		 * Add archived comments count to comment list views.
		 *
		 * @param string[] $views An array of available list table views.
		 *
		 * @return string[]
		 * @since 1.0.0
		 */
		public function add_archived_comments_count( $views ) {
			global $wpdb;

			$archived_count = $wpdb->get_var( "
    SELECT COUNT(comment_ID)
    FROM $wpdb->comments
    WHERE comment_approved = 'archive'
    AND comment_type = 'comment'
" );

			$views[ 'archive' ] = "<a href='edit-comments.php?page=archived-comments'>" . sprintf( __( 'Archived (%s)', 'archive-comments' ), number_format_i18n( $archived_count ) ) . '</a>';

			return $views;
		}


		/**
		 * Add submenu for Archived Comments under Comments
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_archived_comments_submenu() {
			add_comments_page(
				esc_html_x( 'Archived Comments', 'Browser title', 'archive-comments' ),
				esc_html_x( 'Archived', 'Menu label', 'archive-comments' ),
				'moderate_comments',
				'archived-comments',
				[ $this, 'display_archived_comments_page' ]
			);
		}

		/**
		 * The callback function to display archived comments
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function display_archived_comments_page() {
			// Add your HTML/PHP code here to display archived comments
			// You can use WP_List_Table to display the comments similar to the default Comments page
			// Example usage: https://developer.wordpress.org/reference/classes/wp_list_table/

			echo '<div class="wrap">';
			echo '<h1>' . esc_html__( 'Archived Comments', 'archive-comments' ) . '</h1>';
			$archived_comments = $this->get_archived_comments();

			if ( empty( $archived_comments ) ) {
				esc_html_e( "No archived comments", 'archive-comments' );
			} else {
				?>
				<table class="wp-list-table widefat fixed striped table-view-list comments">
					<tr>
						<th id="id" class="manage-column column-id"
						    style="width: 50px;"><?php esc_html_e( 'ID', 'archive-comments' ); ?></th>
						<th id="author"
						    class="manage-column column-author"><?php esc_html_e( 'Author' ); ?></th>
						<th id="comment"
						    class="manage-column column-comment column-primary"><?php esc_html_e( 'Comment' ); ?></th>
						<th id="response"
						    class="manage-column column-response"><?php esc_html_e( 'In response to' ); ?></th>
						<th id="date"
						    class="manage-column column-date"><?php echo esc_html_x( 'Submitted on', 'column name' ); ?></th>
						<th id="unarchive" class="manage-column column-unarchive"
						    style="width: 100px;"><?php esc_html_e( 'Unarchive', 'archive-comments' ); ?></th>
					</tr>
					<?php
					foreach ( $archived_comments as $comment ) {
						$post = get_post( $comment->comment_post_ID );
						echo '<tr>';
						echo '<td>' . $comment->comment_ID . '</td>';
						echo '<td>' . $comment->comment_author . '</td>';
						echo '<td>' . nl2br( $comment->comment_content ) . '</td>';
						echo '<td>';
						echo '<a target="_blank" href="' . home_url( $post->post_name ) . '">';
						echo $post->post_title;
						echo '</a>';
						echo '<br><small>';
						esc_html_e( '(Opens in new window)', 'archive-comments' );
						echo '</small>';
						echo '</td>';
						echo '<td>' . $comment->comment_date . '</td>';
						echo '<td>';
						printf(
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( admin_url( "comment.php?action=unarchivecomment&c=$comment->comment_ID" ) ),
							esc_attr_x( 'Unarchive this comment', 'aria label', 'archive-comments' ),
							esc_html__( 'Unarchive', 'archive-comments' )
						);
						echo '</td>';
						echo '</tr>';
					}
					?>
				</table>
				<?php
			}

			echo '</div>';
		}

		/**
		 * Get the archived comments from the database.
		 *
		 * @return array|object|stdClass[]|null
		 * @since 1.0.0
		 */
		public function get_archived_comments() {
			global $wpdb;

			$archived_comments = $wpdb->get_results( "
SELECT *
FROM $wpdb->comments
WHERE comment_approved = 'archive'
AND comment_type = 'comment'
" );

			return $archived_comments;
		}

		public function plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$links[] = '<a href="https://paypal.me/guseo?country.x=CH&locale.x=en_US" target="_blank">' . esc_html__( 'Chip in to my coffee', 'archive-comments' ) . '</a>';
			}

			return $links;
		}

	} // class

	// Instantiate the plugin class
	$agu_archive_comments = new AGU_Archive_Comments();
}


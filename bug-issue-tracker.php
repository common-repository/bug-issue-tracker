<?php

/*
Plugin Name: Bug & Issue Tracker
Plugin URI: https://wordpress.org/plugins/bug-issue-tracker/
Description: Add tasks to Todoist via WordPress dashboard.
Version: 1.0
Author: Jaro Varga
Author URI: http://jarovarga.sk
License: MIT
Domain Path: /languages
Text Domain: bug-issue-tracker

Copyright (c) 2015 Jaro Varga

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

class bug_issue_tracker
{
	private $todoist = '';

	function bug_issue_tracker()
	{
		add_action( 'admin_init', array( $this, 'init' ) );

		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	function install()
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'bug_issue_tracker';

		$sql = "CREATE TABLE $table_name (
				id int(4) NOT NULL AUTO_INCREMENT,
				author int(4) NOT NULL,
				time int(11) NOT NULL,
				label tinytext NOT NULL,
				priority_level int(4) NOT NULL,
				subject text NOT NULL,
				quick_comment text NOT NULL,
				attachment bigint(20) NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";

		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}

	function init()
	{
		if ( ! is_admin() ) return;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_bug-issue-tracker', array( $this, 'ajax' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

		load_plugin_textdomain( 'bug-issue-tracker', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	function enqueue_scripts()
	{
		wp_enqueue_media();

		wp_enqueue_style( 'bug-issue-tracker', plugins_url( 'bug-issue-tracker.css', __FILE__ ), array(), '1.0' );

		wp_enqueue_script( 'bug-issue-tracker', plugins_url( 'bug-issue-tracker.js', __FILE__ ), array( 'jquery' ), '1.0' );

		wp_localize_script( 'bug-issue-tracker', 'i18n', array( 'attach_file' => __( 'Attach File', 'bug-issue-tracker' ) ) );
	}

	function ajax()
	{
		$subject = filter_var( $_POST['bug-issue-tracker-subject'], FILTER_SANITIZE_STRING );

		if ( empty( $subject ) ) {
			echo json_encode( array( 'status' => false,
									 'message'=> __( 'Sorry, but we can\'t solve a problem that doesn\'t exist.', 'bug-issue-tracker' ) ) );

			exit;
		}

		$label = filter_var( $_POST['bug-issue-tracker-label'], FILTER_SANITIZE_STRING );
		$priority_level = filter_var( $_POST['bug-issue-tracker-priority-level'], FILTER_SANITIZE_NUMBER_INT );
		$quick_comment = filter_var( $_POST['bug-issue-tracker-quick-comment'], FILTER_SANITIZE_STRING );
		$attachment_id = filter_var( $_POST['bug-issue-tracker-attachment-id'], FILTER_SANITIZE_NUMBER_INT );
		$attachment_url = filter_var( $_POST['bug-issue-tracker-attachment-url'], FILTER_SANITIZE_URL );

		$current_user = wp_get_current_user();

		$header = 'MIME-Version: 1.0' . "\r\n"
				. 'Content-type: text/plain; charset=utf-8' . "\r\n";

		$body = '<date every day> '
			  . ( $priority_level ? '!!' . $priority_level . ' ' : '' )
			  . ( $label ? '@' . $label . ' ' : '' )
			  . '[' . $current_user->user_email . '](mailto:' . $current_user->user_email . '?subject=' . $subject . ')'
			  . ( $quick_comment ? "\r\n\r\n" . $quick_comment : '' )
			  . ( $attachment_url ? "\r\n\r\n" . $attachment_url : '' );

		if ( mail( $this->todoist, $subject, $body, $header ) ) {
			global $wpdb;

			$wpdb->insert( $wpdb->prefix . 'bug_issue_tracker', array( 'author' => $current_user->ID,
																	   'time' => time(),
																	   'label' => $label,
																	   'priority_level' => $priority_level,
																	   'subject' => $subject,
																	   'quick_comment' => $quick_comment,
																	   'attachment' => $attachment_id ) );

			echo json_encode( array( 'status' => true,
									 'message'=> __( 'Your request has been submitted successfully! We\'ll be contacting you as quickly as possible.', 'bug-issue-tracker' ) ) );
		}
		else {
			echo json_encode( array( 'status' => false,
									 'message'=> __( 'Oops, something went wrong! Please try again later or contact your system administrator.', 'bug-issue-tracker' ) ) );

			exit;
		}

		unset( $priority_level, $subject, $quick_comment, $attachment, $label );

		exit;
	}

	function add_dashboard_widget()
	{
		add_meta_box( 'bug-issue-tracker', 'Bug & Issue Tracker', array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high' );
	}

	function dashboard_widget()
	{

	?>

		<form method="post">
			<div class="bug-issue-tracker-priority-levels">
				<label title="<?php _e( 'Priority 1', 'bug-issue-tracker' ); ?>">
					<input type="radio" name="bug-issue-tracker-priority-level" value="1">
					<span class="bug-issue-tracker-priority-level-1"></span>
				</label>
				<label title="<?php _e( 'Priority 2', 'bug-issue-tracker' ); ?>">
					<input type="radio" name="bug-issue-tracker-priority-level" value="2">
					<span class="bug-issue-tracker-priority-level-2"></span>
				</label>
				<label title="<?php _e( 'Priority 3', 'bug-issue-tracker' ); ?>">
					<input type="radio" name="bug-issue-tracker-priority-level" value="3">
					<span class="bug-issue-tracker-priority-level-3"></span>
				</label>
				<label title="<?php _e( 'Priority 4', 'bug-issue-tracker' ); ?>">
					<input type="radio" name="bug-issue-tracker-priority-level" value="" checked>
					<span class="bug-issue-tracker-priority-level-4"></span>
				</label>
			</div>
			<div class="bug-issue-tracker-labels">
				<label><input type="checkbox" name="bug-issue-tracker-label" value="bug"><?php _e( 'Bug', 'bug-issue-tracker' ); ?></label>
			</div>
			<div class="input-text-wrap">
				<label for="bug-issue-tracker-subject" class="prompt"><?php _e( 'What\'s the matter?', 'bug-issue-tracker' ); ?></label>
				<input type="text" name="bug-issue-tracker-subject" autocomplete="off">
			</div>
			<div class="textarea-wrap">
				<label for="bug-issue-tracker-quick-comment" class="prompt"><?php _e( 'Quick Comment', 'bug-issue-tracker' ); ?></label>
				<textarea name="bug-issue-tracker-quick-comment" autocomplete="off" rows="3"></textarea>
			</div>
			<div class="bug-issue-tracker-attachment">
				<input type="hidden" name="bug-issue-tracker-attachment-id">
				<input type="hidden" name="bug-issue-tracker-attachment-url">
			</div>
			<p class="submit">
				<button class="button insert-media"><?php _e( 'Attach File', 'bug-issue-tracker' ); ?></button>
				<button class="button button-primary"><?php _e( 'Submit', 'bug-issue-tracker' ); ?></button>
			</p>
			<div class="bug-issue-tracker-status"></div>
		</form>

	<?php

	}
}

$GLOBALS['bug_issue_tracker'] = new bug_issue_tracker();

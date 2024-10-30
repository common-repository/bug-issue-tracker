=== Bug & Issue Tracker ===
Tags: bug, issue, tracker, todoist
Requires at least: 3.5
Tested up to: 4.4
Stable tag: 1.0
License URI: https://opensource.org/licenses/MIT

Add tasks to Todoist via WordPress dashboard.

== Description ==

Add tasks to Todoist via WordPress dashboard.

TODOIST is a powerful task manager for personal or collaborative productivity that lets you manage your to do list from your inbox, browser, desktop, or mobile device. For people who want to accomplish great things in less time, with less effort.

Sign up for an account on [todoist.com](https://todoist.com).

TODOIST is a registered trademark of [Doist Ltd.](https://doist.com)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bug-issue-tracker/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Add the unique email address of the project on line `37` in `bug-issue-tracker.php`.

== Frequently Asked Questions ==

= Where I find the unique email address of the project? =
You can find it by right-clicking on the project you’d like to send your tasks to and choosing “Email tasks to this project”.

= Backup? =
Yes!

`<?php

global $wpdb;

$table_name = $wpdb->prefix . 'bug_issue_tracker';
$results = $wpdb->get_results( "SELECT * FROM $table_name" );

var_dump( $results );

?>`

== Screenshots ==

1. Bug & Issue Tracker

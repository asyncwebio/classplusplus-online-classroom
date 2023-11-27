<?php
/**
 * Uninstall file for the Custom Plugin
 * php version        7.0
 *
 * @category Plugin
 *
 * @package Cpponlineclassroom
 *
 * @author HigherEdLab.com <manish.katyan@higheredlab.com>
 *
 * @license GPLv3-or-later https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @link https://higheredlab.com/
 */

// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete plugin options
delete_option( 'cpp_settings' );

// Delete custom database tables
global $wpdb;
$table_name = $wpdb->prefix . 'cpp_online_classroom';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );


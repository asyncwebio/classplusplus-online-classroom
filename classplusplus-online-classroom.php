<?php
/**
 * Plugin Name:       Class++: AI-powered Online Classrooms
 * Description:       AI-powered Online Classrooms that improve learning and reduce drop-offs
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * php version        7.0
 * Version:           1.0.1
 * Author:            @higheredlab
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       classplusplus-online-classroom
 *
 * @category Plugin
 *
 * @package Cpponlineclassroom
 *
 * @author @higheredlab
 *
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @link https://classplusplus.ai/
 */

// set global variable for current user.

global $current_logged_in_wp_user;

add_action( 'admin_menu', 'cpp_init_menu' );

/**
 * Init Admin Menu.
 *
 * @return void
 */
function cpp_init_menu() {
	// phpcs:disable
	add_menu_page( __( 'Class++', 'cpp' ), __( 'Class++', 'cpp' ), 'manage_options', 'cpp', 'cpp_admin_page', 'dashicons-welcome-learn-more', '2.1' );

}


add_action( 'admin_init', 'register_cpp_plugin_settings' );

/**
 * Add Site meta to store bbb settings
 *
 * @return void
 */
function register_cpp_plugin_settings() {
	// Register the settings
	register_setting( 'cpp-plugin-settings', 'cpp_settings' );
}

/**
 * Init Admin Page.
 *
 * @return void
 */
function cpp_admin_page() {
	// phpcs:disable
	require_once plugin_dir_path( __FILE__ ) . 'templates/app.php';
}


add_action( 'admin_enqueue_scripts', 'cpp_admin_enqueue_scripts' );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function cpp_admin_enqueue_scripts() {
	wp_enqueue_style( 'bbb-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );
	wp_enqueue_script( 'bbb-script', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-element' ), '1.0.0', true );
}




register_activation_hook( __FILE__, "cpp_plugin_activation" );

/**
 * On plugin activation create a  db table
 *
 * @return void
 */
function cpp_plugin_activation() {


	// Insert DB Tables
	// WP Globals
	global $table_prefix, $wpdb;

	// Customer Table
	$cpp_online_classroom = $table_prefix . 'cpp_online_classroom';

	error_log( "====== Trying to add table $cpp_online_classroom ======" );
	// Create Customer Table if not exist
	if ( $wpdb->get_var( "show tables like '$cpp_online_classroom'" ) != $cpp_online_classroom ) {

		// Query - Create Table
		$sql = "CREATE TABLE `$cpp_online_classroom` (";
		$sql .= " `id` int(11) NOT NULL auto_increment, ";
		$sql .= " `name` varchar(500) NOT NULL, ";
		$sql .= " `bbb_id` varchar(500) NOT NULL, ";
		$sql .= " `record` boolean DEFAULT 1, ";
		$sql .= " `presentation` varchar(500) NOT NULL, ";
		$sql .= " `access_code` varchar(500) DEFAULT NULL, ";

		// mute users on join
		$sql .= " `mute_user_on_join` boolean DEFAULT 0, ";

		// Require moderator approval before joining
		$sql .= " `require_moderator_approval` boolean DEFAULT 0, ";

		// All users join as moderators
		$sql .= " `all_users_join_as_moderator` boolean DEFAULT 0, ";

		// Branding settings
		//logo
		$sql .= " `logo_url` varchar(500) DEFAULT NULL, ";

		//logout url
		$sql .= " `logout_url` varchar(500) DEFAULT NULL, ";

		//color
		$sql .= " `primary_color` varchar(500) DEFAULT NULL, ";

		//welcome message
		$sql .= " `welcome_message` varchar(500) DEFAULT NULL, ";

		//advanced settings
		// Enable moderator to unmute users
		$sql .= " `enable_moderator_to_unmute_users` boolean DEFAULT 0, ";

		// Skip audio check
		$sql .= " `skip_check_audio` boolean DEFAULT 0, ";

		//Disable listen only mode
		$sql .= " `disable_listen_only_mode` boolean DEFAULT 0, ";

		// Enable user's private chats
		$sql .= " `enable_user_private_chats` boolean DEFAULT 0, ";

		//class Layout
		$sql .= " `class_layout` varchar(500) DEFAULT NULL, ";

		//addtional join params
		$sql .= " `additional_join_params` varchar(500) DEFAULT NULL, ";

		// sessions count
		$sql .= " `sessions_count` int(11) NOT NULL DEFAULT 0, ";

		// last session
		$sql .= " `last_session` TIMESTAMP, ";

		// created at
		$sql .= " `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
		// updated at

		$sql .= " `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
		$sql .= " PRIMARY KEY `customer_id` (`id`) ";

		// get wpdb charset
		$charset_collate = $wpdb->get_charset_collate();

		$sql .= ")";

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

		// Create Table
		dbDelta( $sql );

		error_log( "====== Table $cpp_online_classroom created ======" );
	} else {
		error_log( "====== Table $cpp_online_classroom already exists ======" );
	}
}




// Register uninstall hook
register_uninstall_hook( __FILE__, 'cpp_plugin_uninstall_cleanup' );

/**
 * On plugin uninstall, drop the db table.
 */
function cpp_plugin_uninstall_cleanup() {
    global $wpdb;

    // Table Name
    $table_name = $wpdb->prefix . 'cpp_online_classroom';

    error_log( "====== BigBlueButton online classroom plugin uninstalled. Deleting Table $table_name ======" );

    // Drop the table if it exists
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}



/**
 * Initi api endpoint to save bbb settings
 *
 * @return void
 */

function cpp_create_api_endpoint() {
	global $current_logged_in_wp_user;
	$data = wp_get_current_user();
	$current_logged_in_wp_user = clone $data;

	// route for getting settings
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/get-settings/',
		array(
			'methods' => 'GET',
			'callback' => 'cpp_get_settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for saving settings
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/save-settings/',
		array(
			'methods' => 'POST',
			'callback' => 'cpp_save_settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting classes

	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/get-classes/',
		array(
			'methods' => 'GET',
			'callback' => 'cpp_get_classes_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for creating a new class
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/create-class/',
		array(
			'methods' => 'POST',
			'callback' => 'cpp_create_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for editing a class
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/edit-class/',
		array(
			'methods' => 'POST',
			'callback' => 'cpp_edit_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for deleting a class
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/delete-class/',
		array(
			'methods' => 'DELETE',
			'callback' => 'cpp_delete_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for starting a class
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/start-class/',
		array(
			'methods' => 'POST',
			'callback' => 'cpp_start_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for joing a class
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/join-class/',
		array(
			'methods' => 'GET',
			'callback' => 'cpp_join_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting a class recording
	register_rest_route(
		'classplusplus-online-classroom/v1',
		'/get-recordings/',
		array(
			'methods' => 'GET',
			'callback' => 'cpp_get_recording_request',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'cpp_create_api_endpoint' );



/**
 * Save BBB settings.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function cpp_save_settings_request( WP_REST_Request $request ) {
	$request_body = file_get_contents( 'php://input' );
	$settings = sanitize_text_field( $request_body );
	update_option( 'cpp_settings', $settings );
	// payload object
	$payload = array(
		"data" => $settings,
	);
	return rest_ensure_response( $payload );
}



/**
 * Get BBB settings.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function cpp_get_settings_request( WP_REST_Request $request ) {
	$settings = sanitize_text_field( get_option( 'cpp_settings' ) );
	// payload object
	$payload = array(
		"data" => json_decode( $settings ),
	);

	return rest_ensure_response( $payload );
}


/**
 * Handle get class.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function cpp_get_classes_request( WP_REST_Request $request ) {
	global $wpdb;
	// Check if URL query param 'id' is present.
	$id = absint( $request->get_param( 'id' ) );

	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';
	// Set null value.
	$classes = null;

	if ( $id ) {
		// Use prepared statement to prevent SQL injection.
		$classes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $cpp_online_classroom WHERE id = %d", $id ) );
	} else {
		// Order by updated_at desc.
		$classes = $wpdb->get_results( "SELECT * FROM $cpp_online_classroom ORDER BY updated_at DESC" );
	}

	// Payload object.
	$payload = array(
		'data' => $classes,
	);

	return rest_ensure_response( $payload );
}


/**
 * Handle Create class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function cpp_create_class_request( WP_REST_Request $request ) {
	$request_body = file_get_contents( 'php://input' );
	$class_data = sanitize_text_field( $request_body );
	$class_data = json_decode( $class_data );
	$name = $class_data->name;
	$bbb_id = $class_data->bbb_id;
	$record = $class_data->record;
	$presentation = $class_data->presentation;
	$access_code = $class_data->access_code;
	$mute_user_on_join = $class_data->mute_user_on_join;
	$require_moderator_approval = $class_data->require_moderator_approval;
	$all_users_join_as_moderator = $class_data->all_users_join_as_moderator;
	$logo_url = $class_data->logo_url;
	$logout_url = $class_data->logout_url;
	$primary_color = $class_data->primary_color;
	$welcome_message = $class_data->welcome_message;
	$enable_moderator_to_unmute_users = $class_data->enable_moderator_to_unmute_users;
	$skip_check_audio = $class_data->skip_check_audio;
	$disable_listen_only_mode = $class_data->disable_listen_only_mode;
	$enable_user_private_chats = $class_data->enable_user_private_chats;
	$class_layout = $class_data->class_layout;
	$additional_join_params = $class_data->additional_join_params;
	$class = add_cpp_class(
		array(
			'name' => $name,
			'bbb_id' => $bbb_id,
			'record' => $record,
			'presentation' => $presentation,
			'access_code' => $access_code,
			'mute_user_on_join' => $mute_user_on_join,
			'require_moderator_approval' => $require_moderator_approval,
			'all_users_join_as_moderator' => $all_users_join_as_moderator,
			'logo_url' => $logo_url,
			'logout_url' => $logout_url,
			'primary_color' => $primary_color,
			'welcome_message' => $welcome_message,
			'enable_moderator_to_unmute_users' => $enable_moderator_to_unmute_users,
			'skip_check_audio' => $skip_check_audio,
			'disable_listen_only_mode' => $disable_listen_only_mode,
			'enable_user_private_chats' => $enable_user_private_chats,
			'class_layout' => $class_layout,
			'additional_join_params' => $additional_join_params,

		)
	);
	// payload object
	$payload = array(
		"data" => $class
	);
	return rest_ensure_response( $payload );
}


/**
 * Handle delete class request.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response|WP_Error $response Response object or error.
 */
function cpp_delete_class_request( WP_REST_Request $request ) {
	global $wpdb;

	$id = absint( $request->get_param( 'id' ) );

	// Check if the ID is empty.
	if ( empty( $id ) ) {
		return new WP_Error( 'bad_request', 'ID is required', array( 'status' => 400 ) );
	}

	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';
	$result = $wpdb->delete( $cpp_online_classroom, array( 'id' => $id ) );

	// Check if the delete operation was successful.
	if ( false === $result ) {
		return new WP_Error( 'delete_error', 'Error deleting class', array( 'status' => 500 ) );
	}

	// Return 200 response.
	return rest_ensure_response( null )->set_status( 200 );
}


/**
 * Handle edit class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function cpp_edit_class_request( WP_REST_Request $request ) {
	global $wpdb;
	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';

	$id = sanitize_text_field( $request->get_param( 'id' ) );

	$request_body = file_get_contents( 'php://input' );
	$class_data = sanitize_text_field( $request_body );
	$class_data = json_decode( $class_data );

	$name = $class_data->name;
	$record = $class_data->record;
	$presentation = $class_data->presentation;
	$access_code = $class_data->access_code;
	$mute_user_on_join = $class_data->mute_user_on_join;
	$require_moderator_approval = $class_data->require_moderator_approval;
	$all_users_join_as_moderator = $class_data->all_users_join_as_moderator;
	$logo_url = $class_data->logo_url;
	$logout_url = $class_data->logout_url;
	$primary_color = $class_data->primary_color;
	$welcome_message = $class_data->welcome_message;
	$enable_moderator_to_unmute_users = $class_data->enable_moderator_to_unmute_users;
	$skip_check_audio = $class_data->skip_check_audio;
	$disable_listen_only_mode = $class_data->disable_listen_only_mode;
	$enable_user_private_chats = $class_data->enable_user_private_chats;
	$class_layout = $class_data->class_layout;
	$additional_join_params = $class_data->additional_join_params;

	$wpdb->update(
		$cpp_online_classroom,
		array(
			'name' => $name,
			'record' => $record,
			'presentation' => $presentation,
			'access_code' => $access_code,
			'mute_user_on_join' => $mute_user_on_join,
			'require_moderator_approval' => $require_moderator_approval,
			'all_users_join_as_moderator' => $all_users_join_as_moderator,
			'logo_url' => $logo_url,
			'logout_url' => $logout_url,
			'primary_color' => $primary_color,
			'welcome_message' => $welcome_message,
			'enable_moderator_to_unmute_users' => $enable_moderator_to_unmute_users,
			'skip_check_audio' => $skip_check_audio,
			'disable_listen_only_mode' => $disable_listen_only_mode,
			'enable_user_private_chats' => $enable_user_private_chats,
			'class_layout' => $class_layout,
			'additional_join_params' => $additional_join_params,
		),
		array( 'id' => $id )
	);
	// Return 200 response.
	return rest_ensure_response( null )->set_status( 200 );
}

/**
 * Handle start class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */

function cpp_start_class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';
	// get id from request
	$id = absint( $request->get_param( 'id' ) );

	// get bbb settings
	$settings = sanitize_text_field( get_option( 'cpp_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$bbb_class = $wpdb->get_results( "SELECT * FROM $cpp_online_classroom WHERE id = $id" );
	$bbb_class = $bbb_class[0];
	$create_meeting_params = array(
		'name' => $bbb_class->name,
		'meetingID' => $bbb_class->bbb_id,
		'record' => $bbb_class->record == 1 ? 'true' : 'false',
		"muteOnStart" => $bbb_class->mute_user_on_join == 1 ? 'true' : 'false',
		"logo" => $bbb_class->logo_url,
		"logoutURL" => $bbb_class->logout_url,
		"meetingLayout" => $bbb_class->class_layout,
		"allowModsToUnmuteUsers" => $bbb_class->enable_moderator_to_unmute_users == 1 ? 'true' : 'false',
		"welcome" => $bbb_class->welcome_message,
	);

	try {
		// parse additional join params as json and add to create_meeting_params

		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$create_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {
		error_log( "====== Error parsing additional join params $e ======" );
	}

	$query = http_build_query( $create_meeting_params );
	$action_url = get_cpp_url( 'create', $query, $bbb_url, $bbb_secret );
	$presentation = $bbb_class->presentation;
	$presentation_body = "";

	// if presentation url is present then  pre upload presentation
	if ( $presentation ) {
		$presentation_body = "<modules><module name='presentation'><document url='$presentation' filename='presentation.pdf'/></module></modules>";
	}

	// make post api call to create meeting
	$req_body = array(
		'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
	);

	if ( $presentation_body ) {
		$req_body['body'] = $presentation_body;
	}

	$response = wp_remote_post(
		$action_url,
		$req_body
	);

	// response is in xml format
	$response = wp_remote_retrieve_body( $response );
	$response = simplexml_load_string( $response );
	if ( $response->returncode == "FAILED" ) {
		return new WP_Error( 'bad_request', $response->message, array( 'status' => 400 ) );
	}

	// update last session
	$wpdb->update(
		$cpp_online_classroom,
		array(
			'last_session' => current_time( 'mysql' ),
			'sessions_count' => $bbb_class->sessions_count + 1,
		),
		array( 'id' => $id )
	);

	// get current user name
	$current_user_name = $current_logged_in_wp_user->display_name;
	if ( ! $current_user_name ) {
		$current_user_name = $current_logged_in_wp_user->user_login;
	}
	// get user avatar
	$current_user_avatar = get_avatar_url( $current_logged_in_wp_user->ID, array( 'size' => 96 ) );


	// Join Params
	$join_meeting_params = array(
		// get user name from session
		'meetingID' => $bbb_class->bbb_id,
		'role' => 'MODERATOR',
	);

	// add username if present
	if ( $current_user_name ) {
		$join_meeting_params['fullName'] = $current_user_name;
	} else {
		$join_meeting_params['fullName'] = 'Moderator';
	}

	// add avatarURL if present
	if ( $current_user_avatar ) {
		$join_meeting_params['avatarURL'] = $current_user_avatar;
	}

	$brand_color = $bbb_class->primary_color;

	$css = "
    :root{
        --color-primary: $brand_color;

        --btn-primary-active-bg: var(--color-primary);
        --btn-primary-hover-bg: var(--color-primary);
        --color-success: var(--color-primary);
        --btn-primary-bg:var(--color-primary);
        --btn-default-color:var(--color-primary);
        }
        #message-input, #message-input-wrapper{
            background: #fff !important;
        }
        .icon-bbb-upload{
            color: none !important;
        }

          button.select {
            background-color: var(--color-primary) !important;

          }
    ";

	// remove new lines fron $css
	$css = str_replace( array( "\r", "\n" ), '', $css );

	$join_meeting_params["userdata-bbb_listen_only_mode"] = $bbb_class->disable_listen_only_mode == 1 ? 'false' : 'true';
	$join_meeting_params["lockSettingsDisablePrivateChat"] = $bbb_class->enable_user_private_chats == 0 ? 'false' : 'true';
	$join_meeting_params['userdata-bbb_skip_check_audio'] = $bbb_class->skip_check_audio == 1 ? 'true' : 'false';
	$join_meeting_params["meetingLayout"] = $bbb_class->class_layout ? $bbb_class->class_layout : 'SMART_LAYOUT';

	if ( $bbb_class->primary_color ) {
		$join_meeting_params['userdata-bbb_custom_style'] = $css;
	}

	try {
		// parse additional join params as json and add to create_meeting_params
		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$join_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {

	}





	$query = http_build_query( $join_meeting_params );
	$action_url = get_cpp_url( 'join', $query, $bbb_url, $bbb_secret );

	$payload = array(
		"data" => $action_url,
	);
	return rest_ensure_response( $payload );
}

/**
 * Handle join class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */

function cpp_join_class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';
	// get id from request
	$id = absint( $request->get_param( 'id' ) );
	$join_name = sanitize_text_field( $request->get_param( 'join_name' ) );
	$access_code = sanitize_text_field( $request->get_param( 'access_code' ) );


	// get bbb settings
	$settings = sanitize_text_field( get_option( 'cpp_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$bbb_class = $wpdb->get_results( "SELECT * FROM $cpp_online_classroom WHERE id = $id" );
	$bbb_class = $bbb_class[0];

	if ( $bbb_class->access_code && $bbb_class->access_code != $access_code ) {
		return new WP_Error( 'bad_request', 'Access code is incorrect', array( 'status' => 400 ) );
	}

	// get current user name
	$current_user_name = $join_name ? $join_name : $current_logged_in_wp_user->display_name;
	if ( ! $current_user_name ) {
		$current_user_name = $current_logged_in_wp_user->user_login ? $current_logged_in_wp_user->user_login : 'User-' . rand( 1, 1000 );
	}
	// get user avatar
	$current_user_avatar = get_avatar_url( $current_logged_in_wp_user->ID, array( 'size' => 96 ) );


	$join_meeting_params = array(
		// get user name from session
		'meetingID' => $bbb_class->bbb_id,
		'role' => $bbb_class->all_users_join_as_moderator == '1' ? 'MODERATOR' : 'VIEWER',
	);
	$brand_color = $bbb_class->primary_color;

	$css = "
    :root{
        --color-primary: $brand_color;
        --btn-primary-active-bg: var(--color-primary);
        --btn-primary-hover-bg: var(--color-primary);
        --color-success: var(--color-primary);
        --btn-primary-bg:var(--color-primary);
        --btn-default-color:var(--color-primary);
        }
        #message-input, #message-input-wrapper{
            background: #fff !important;
        }
        .icon-bbb-upload{
            color: none !important;
        }
        button.select {
            background-color: var(--color-primary) !important;

          }
    ";

	// remove new lines fron $css
	$css = str_replace( array( "\r", "\n" ), '', $css );


	$join_meeting_params["userdata-bbb_listen_only_mode"] = $bbb_class->disable_listen_only_mode == 1 ? 'false' : 'true';
	$join_meeting_params["lockSettingsDisablePrivateChat"] = $bbb_class->enable_user_private_chats == 0 ? 'false' : 'true';
	$join_meeting_params['userdata-bbb_skip_check_audio'] = $bbb_class->skip_check_audio == 1 ? 'true' : 'false';
	$join_meeting_params["meetingLayout"] = $bbb_class->class_layout ? $bbb_class->class_layout : 'SMART_LAYOUT';

	if ( $bbb_class->primary_color ) {
		$join_meeting_params['userdata-bbb_custom_style'] = $css;
	}

	try {
		// parse additional join params as json and add to create_meeting_params
		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$join_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {

	}


	// add username if present
	if ( $current_user_name ) {
		$join_meeting_params['fullName'] = $current_user_name;
	} else {
		$join_meeting_params['fullName'] = 'Moderator';
	}

	// add avatarURL if present
	if ( $current_user_avatar ) {
		$join_meeting_params['avatarURL'] = $current_user_avatar;
	}

	$query = http_build_query( $join_meeting_params );
	$action_url = get_cpp_url( 'join', $query, $bbb_url, $bbb_secret );
	wp_redirect( $action_url );
	exit;
}

/**
 * Handle get class recording request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function cpp_get_recording_request( WP_REST_Request $request ) {
	// get bbb settings
	$settings = sanitize_text_field( get_option( 'cpp_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;

	$get_recordings_params = array(
		'meetingID' => sanitize_text_field( $request->get_param( 'meetingID' ) ),
	);

	$query = http_build_query( $get_recordings_params );
	$action_url = get_cpp_url( 'getRecordings', $query, $bbb_url, $bbb_secret );
	$response = wp_remote_get( $action_url );
	$response = wp_remote_retrieve_body( $response );
	$response = simplexml_load_string( $response );
	if ( $response->returncode == "FAILED" ) {
		return new WP_Error( 'bad_request', $response->message, array( 'status' => 404 ) );
	}


	$payload = array(
		"data" => $response->recordings,
	);

	return rest_ensure_response( $payload );
}



/**
 * Helper function got generate bbb url
 *
 * @param string $action     bbb action
 * @param string $query      bbb params
 * @param string $bbb_url    bbb url
 * @param string $bbb_secret bbb secret
 *
 * @return string $url       bbb action url
 */
function get_cpp_url( $action, $query, $bbb_url, $bbb_secret ) {
	$checksum = sha1( $action . $query . $bbb_secret );

	// if bbb_url is not ends with / then add it
	if ( substr( $bbb_url, -1 ) != '/' ) {
		$bbb_url .= '/';
	}
	$url = $bbb_url . $action . '?' . $query . '&checksum=' . $checksum;
	return $url;
}


/**
 * Create an entry in cpp_online_classroom table
 *
 * @param array $data class data
 *
 * @return array $newClass class data
 */
function add_cpp_class( $data ) {
	global $wpdb;
	$cpp_online_classroom = $wpdb->prefix . 'cpp_online_classroom';
	$wpdb->insert(
		$cpp_online_classroom,
		$data
	);
	$id = $wpdb->insert_id;
	$newClass = $wpdb->get_results( "SELECT * FROM $cpp_online_classroom WHERE id = $id" );
	// return created class
	return $newClass[0];
}

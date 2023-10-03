<?php
/**
 * Plugin Name:       BigBlueButton Online Class
 * Description:       Seamless integration with BigBlueButton
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * php version        7.0
 * Version:           0.1.0
 * Author:            Asyncweb technologies
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bbb-online-classroom
 *
 * @category Plugin
 *
 * @package Bbbonlineclassroom
 *
 * @author BigBlueButton Online Class <manish.katyan@higheredlab.com>
 *
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @link https://marketingllama.ai/
 */

// set global variable for current user.

global $current_logged_in_wp_user;

add_action( 'admin_menu', 'bbb_init_menu' );

/**
 * Init Admin Menu.
 *
 * @return void
 */
function bbb_init_menu() {
	// phpcs:disable
	add_menu_page( __( 'BigBlueButton', 'bbb' ), __( 'BigBlueButton', 'bbb' ), 'manage_options', 'bbb', 'Bbb_Admin_page', 'dashicons-welcome-learn-more', '2.1' );

}


add_action( 'admin_init', 'Register_Bbb_Plugin_settings' );

/**
 * Add Site meta to store bbb settings
 *
 * @return void
 */
function Register_Bbb_Plugin_settings() {
	// Register the settings
	register_setting( 'bbb-plugin-settings', 'bbb_settings' );
}

/**
 * Init Admin Page.
 *
 * @return void
 */
function Bbb_Admin_page() {
	// phpcs:disable
	require_once plugin_dir_path( __FILE__ ) . 'templates/app.php';
}


add_action( 'admin_enqueue_scripts', 'Bbb_Admin_Enqueue_scripts' );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function Bbb_Admin_Enqueue_scripts() {
	wp_enqueue_style( 'bbb-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );
	wp_enqueue_script( 'bbb-script', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-element' ), '1.0.0', true );
}




register_activation_hook( __FILE__, "Bbb_Plugin_activation" );

/**
 * On plugin activation create a  db table
 *
 * @return void
 */
function Bbb_Plugin_activation() {


	// Insert DB Tables
	// WP Globals
	global $table_prefix, $wpdb;

	// Customer Table
	$bbb_online_classroom = $table_prefix . 'bbb_online_classroom';

	error_log( "====== Trying to add table $bbb_online_classroom ======" );
	// Create Customer Table if not exist
	if ( $wpdb->get_var( "show tables like '$bbb_online_classroom'" ) != $bbb_online_classroom ) {

		// Query - Create Table
		$sql = "CREATE TABLE `$bbb_online_classroom` (";
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

		error_log( "====== Table $bbb_online_classroom created ======" );
	} else {
		error_log( "====== Table $bbb_online_classroom already exists ======" );
	}
}




register_uninstall_hook( __FILE__, "Bbb_Plugin_Uninstall_cleanup" );

/**
 * On plugin uninstall drop the db table
 *
 * @return void
 */
function Bbb_Plugin_Uninstall_cleanup() {
	global $wpdb;
	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	error_log( "====== BigBlueButton online classroom plugin uninstalled. Deleting Table $bbb_online_classroom ======" );
	$wpdb->query( "DROP TABLE IF EXISTS $bbb_online_classroom" );
}


/**
 * Initi api endpoint to save bbb settings
 *
 * @return void
 */

function Bbb_Create_Api_endpoint() {
	global $current_logged_in_wp_user;
	$data = wp_get_current_user();
	$current_logged_in_wp_user = clone $data;

	// route for getting settings
	register_rest_route(
		'bbb-online-classroom/v1',
		'/get-settings/',
		array(
			'methods' => 'GET',
			'callback' => 'Bbb_Handle_Get_Settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for saving settings
	register_rest_route(
		'bbb-online-classroom/v1',
		'/save-settings/',
		array(
			'methods' => 'POST',
			'callback' => 'Bbb_Handle_Save_Settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting classes

	register_rest_route(
		'bbb-online-classroom/v1',
		'/get-classes/',
		array(
			'methods' => 'GET',
			'callback' => 'Bbb_Handle_Get_Classes_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for creating a new class
	register_rest_route(
		'bbb-online-classroom/v1',
		'/create-class/',
		array(
			'methods' => 'POST',
			'callback' => 'Bbb_Handle_Create_Class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for editing a class
	register_rest_route(
		'bbb-online-classroom/v1',
		'/edit-class/',
		array(
			'methods' => 'POST',
			'callback' => 'Bbb_Handle_Edit_Class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for deleting a class
	register_rest_route(
		'bbb-online-classroom/v1',
		'/delete-class/',
		array(
			'methods' => 'DELETE',
			'callback' => 'Bbb_Handle_Delete_Class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for starting a class
	register_rest_route(
		'bbb-online-classroom/v1',
		'/start-class/',
		array(
			'methods' => 'POST',
			'callback' => 'Bbb_Handle_Start_Class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for joing a class
	register_rest_route(
		'bbb-online-classroom/v1',
		'/join-class/',
		array(
			'methods' => 'GET',
			'callback' => 'Bbb_Handle_Join_Class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting a class recording
	register_rest_route(
		'bbb-online-classroom/v1',
		'/get-recordings/',
		array(
			'methods' => 'GET',
			'callback' => 'Bbb_Handle_Get_Recording_request',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'Bbb_Create_Api_endpoint' );



/**
 *  Save bbb settings
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function Bbb_Handle_Save_Settings_request( WP_REST_Request $request ) {
	$request_body = file_get_contents( 'php://input' );
	$settings = sanitize_text_field( $request_body );
	update_option( 'bbb_settings', $settings );
	// payload object
	$payload = array(
		"data" => $settings,
	);
	return rest_ensure_response( $payload );
}


/**
 * Get bbb settings
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function Bbb_Handle_Get_Settings_request( WP_REST_Request $request ) {
	$settings = sanitize_text_field( get_option( 'bbb_settings' ) );
	// payload object
	$payload = array(
		"data" => json_decode( $settings ),
	);

	return rest_ensure_response( $payload );
}

/**
 * Handle get class
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function Bbb_Handle_Get_Classes_request( WP_REST_Request $request ) {
	global $wpdb;
	// check if url query parm id is present?
	$id = sanitize_text_field( $request->get_param( 'id' ) );
	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	// set null value
	$classes = null;
	if ( $id ) {
		$classes = $wpdb->get_results( "SELECT * FROM $bbb_online_classroom WHERE id = $id" );
	} else {
		// oder by updated_at desc
		$classes = $wpdb->get_results( "SELECT * FROM $bbb_online_classroom ORDER BY updated_at DESC" );
	}
	// payload object
	$payload = array(
		"data" => $classes,
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
function Bbb_Handle_Create_Class_request( WP_REST_Request $request ) {
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
	$class = Add_Bbb_class(
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
 * Handle  delete class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function Bbb_Handle_Delete_Class_request( WP_REST_Request $request ) {

	global $wpdb;
	$id = sanitize_text_field( $request->get_param( 'id' ) );
	// give bad request if id is not present
	if ( ! $id ) {
		return new WP_Error( 'bad_request', 'id is required', array( 'status' => 400 ) );
	}
	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	$wpdb->delete( $bbb_online_classroom, array( 'id' => $id ) );

	// return 200 response
	return new WP_REST_Response( null, 200 );
}

/**
 * Handle edit class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */
function Bbb_Handle_Edit_Class_request( WP_REST_Request $request ) {
	global $wpdb;
	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';

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
		$bbb_online_classroom,
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
	return new WP_REST_Response( null, 200 );
}

/**
 * Handle start class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */

function Bbb_Handle_Start_Class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	// get id from request
	$id = sanitize_text_field( $request->get_param( 'id' ) );

	// get bbb settings
	$settings = sanitize_text_field( get_option( 'bbb_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$bbb_class = $wpdb->get_results( "SELECT * FROM $bbb_online_classroom WHERE id = $id" );
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
	$action_url = Get_Bbb_url( 'create', $query, $bbb_url, $bbb_secret );
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
		$bbb_online_classroom,
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
	$action_url = Get_Bbb_url( 'join', $query, $bbb_url, $bbb_secret );

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

function Bbb_Handle_Join_Class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	// get id from request
	$id = sanitize_text_field( $request->get_param( 'id' ) );
	$join_name = sanitize_text_field( $request->get_param( 'join_name' ) );
	$access_code = sanitize_text_field( $request->get_param( 'access_code' ) );


	// get bbb settings
	$settings = sanitize_text_field( get_option( 'bbb_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$bbb_class = $wpdb->get_results( "SELECT * FROM $bbb_online_classroom WHERE id = $id" );
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
	$action_url = Get_Bbb_url( 'join', $query, $bbb_url, $bbb_secret );
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
function Bbb_Handle_Get_Recording_request( WP_REST_Request $request ) {
	// get bbb settings
	$settings = sanitize_text_field( get_option( 'bbb_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;

	$get_recordings_params = array(
		'meetingID' => sanitize_text_field( $request->get_param( 'meetingID' ) ),
	);

	$query = http_build_query( $get_recordings_params );
	$action_url = Get_Bbb_url( 'getRecordings', $query, $bbb_url, $bbb_secret );
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
function Get_Bbb_url( $action, $query, $bbb_url, $bbb_secret ) {
	$checksum = sha1( $action . $query . $bbb_secret );

	// if bbb_url is not ends with / then add it
	if ( substr( $bbb_url, -1 ) != '/' ) {
		$bbb_url .= '/';
	}
	$url = $bbb_url . $action . '?' . $query . '&checksum=' . $checksum;
	return $url;
}


/**
 * Create an entry in bbb_online_classroom table
 *
 * @param array $data class data
 *
 * @return array $newClass class data
 */
function Add_Bbb_class( $data ) {
	global $wpdb;
	$bbb_online_classroom = $wpdb->prefix . 'bbb_online_classroom';
	$wpdb->insert(
		$bbb_online_classroom,
		$data
	);
	$id = $wpdb->insert_id;
	$newClass = $wpdb->get_results( "SELECT * FROM $bbb_online_classroom WHERE id = $id" );
	// return created class
	return $newClass[0];
}

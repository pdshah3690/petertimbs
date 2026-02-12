<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://feathertechlabs.com
 * @since             27-08
 * @package           Petertimbs
 *
 * @wordpress-plugin
 * Plugin Name:       Petertimbs
 * Plugin URI:        https://feathertechlabs.com/wordpress/plugins/petertimbs
 * Description:       Manage rest apis for mobile app
 * Version:           27-08
 * Author:            Feather Techlabs
 * Author URI:        https://feathertechlabs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       petertimbs
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
require_once plugin_dir_path( __FILE__ ).'public/classes/jwt/JWT.php';
use \Firebase\JWT\JWT;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PETERTIMBS_VERSION', '1.0.0' );

add_action("send_scheduled_push_notifications", "send_scheduled_push_notifications");
function send_scheduled_push_notifications() {
    global $wpdb;

    $from = date("Y-m-d H:i:s", strtotime("-1 Hour"));
    $to = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE is_sent = 0 and schedule_at >= '".$from."' and schedule_at <= '".$to."'";
    $data = $wpdb->get_results($sql, ARRAY_A);    
    $api_key = "AAAAczGtHOk:APA91bG66Vi0YsvbaVkDlRwJ0YjRSLp3tfFG3tk-GLl-7HY6ydXoLzZ_B2Ns1e3qxXmQjqBifBxpjaKaqD6BJpFWm9ud6SpPw8LkAdCOh5lDLhFvJlOXKE4ll3bVr6pe-VWQQwfJC6dD";
    $api_url = "https://fcm.googleapis.com/fcm/send";

    foreach($data as $d) {
        $device_token = get_user_meta($d["user_id"], "device_token", 1);
        $metadata = ['product_id' => $d['product_id'] ];

        if(empty($device_token)) {
            continue;
        }
        $message = array(
            'title' => $d['title'],
            'body' => $d['body']
        );
        $args = [
            "headers" => [
                "Authorization" => "key=" . $api_key,
                'content-type' => 'application/json'
            ],
            "body" => json_encode([
                "to" => $device_token,
                "notification" => $message,
                "data" => $metadata,
                'priority' => 'high',
            ])
        ];
        $response = wp_remote_post($api_url, $args);
        // echo '<pre>';
        // print_r($response);
        // exit();
        $wpdb->update(
            $wpdb->prefix."notification",
            ["is_sent" => 1],
            ["id" => $d["id"]]
        );
    }
    
    
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-petertimbs-activator.php
 */
function activate_petertimbs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs-activator.php';
	$activator = new Petertimbs_Activator;
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-petertimbs-deactivator.php
 */
function deactivate_petertimbs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs-deactivator.php';
	Petertimbs_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_petertimbs' );
register_deactivation_hook( __FILE__, 'deactivate_petertimbs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_petertimbs() {
	$plugin = new Petertimbs();
	$plugin->run();
}

function pt_validate_request($request) {
	if(empty($request->get_param('oauth_token'))) {
		$response = [
			"success" => false,
			"message" => "Invalid access."
		];
        wp_send_json($response, 400);
	}
	try {
		$user = JWT::decode($request->get_param('oauth_token'), API_KEY, ['HS256']);
		$userdata = get_userdata($user->user_id);

		$user->email = $userdata->user_email;
		$user->first_name = get_user_meta($user->user_id, 'first_name', true);
		$user->last_name = get_user_meta($user->user_id, 'last_name', true);
		$user->billing_address_1 = get_user_meta($user->user_id, 'billing_address_1', true);
		$user->billing_address_2 = get_user_meta($user->user_id, 'billing_address_2', true);
		$user->billing_city = get_user_meta($user->user_id, 'billing_city', true);
		$user->billing_state = get_user_meta($user->user_id, 'billing_state', true);
		$user->billing_postcode = get_user_meta($user->user_id, 'billing_postcode', true);
		return $user;
	} catch (Exception $e) {
		$response = [
			"success" => false,
			"message" => "Invalid token."
		];
        wp_send_json($response, 400);
	}
}
function set_option($val) {
	return "Choose an option";
}
function _get_address_postcode($address) {
    $address = str_replace(", ", "+", $address);
    $address = str_replace(" ", "+", $address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyAYyxEGB5J4cZGFf9TWu4N0gy0KQDWtFeI";
    $data = json_decode(file_get_contents($url), true);
    $data = end($data['results'][0]['address_components']);
    return $data['short_name'];
}
function _check_specific_delivery_postcodes($postcode) {
    $option = get_option("peter_timbs");
    $specific_postcodes = [];
    $sunday = empty($option['sunday_delivery_postcodes']) ? [] : explode(",", $option['sunday_delivery_postcodes']);
    $monday = empty($option['monday_delivery_postcodes']) ? [] : explode(",", $option['monday_delivery_postcodes']);
    $tuesday = empty($option['tuesday_delivery_postcodes']) ? [] : explode(",", $option['tuesday_delivery_postcodes']);
    $wednesday = empty($option['wednesday_delivery_postcodes']) ? [] : explode(",", $option['wednesday_delivery_postcodes']);
    $thursday = empty($option['thursday_delivery_postcodes']) ? [] : explode(",", $option['thursday_delivery_postcodes']);
    $friday = empty($option['friday_delivery_postcodes']) ? [] : explode(",", $option['friday_delivery_postcodes']);
    $saturday = empty($option['saturday_delivery_postcodes']) ? [] : explode(",", $option['saturday_delivery_postcodes']);

    $north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
    $south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);

    $week_days = [1, 2, 3, 4, 5, 6, 7];
    foreach ($monday as $value) {
        $specific_postcodes[$value][] = 1;
    }
    foreach ($tuesday as $value) {
        $specific_postcodes[$value][] = 2;
    }
    foreach ($wednesday as $value) {
        $specific_postcodes[$value][] = 3;
    }
    foreach ($thursday as $value) {
        $specific_postcodes[$value][] = 4;
    }
    foreach ($friday as $value) {
        $specific_postcodes[$value][] = 5;
    }
    foreach ($saturday as $value) {
        $specific_postcodes[$value][] = 6;
    }
    foreach ($sunday as $value) {
        $specific_postcodes[$value][] = 7;
    }
    $final_data = [];
    foreach ($specific_postcodes as $key => $days) {
        $final_data[$key] = array_values( array_diff($week_days, $days) );
    }

    // if(empty($final_data[$postcode])){
    //     if( in_array($postcode,$north_island_postcodes) || in_array($postcode,$south_island_postcodes) )
    //     {   
    //         $final_data[$postcode] = $postcode;
    //     }
    // }
    if(empty($final_data[$postcode])) {
        return false;
    }
    return $final_data[$postcode];
}
function _get_postcode_disabled_dates($postcode_days, &$disabled_dates) {
    for($i=1; $i<=60; $i++){
        $day_no = date("N", strtotime($i." days"));
        if(!in_array($day_no, $postcode_days)) {
            $disabled_dates[] = date("Y-m-d", strtotime($i." days"));
        }
    }
}
function _get_user_cards($user_id) {
    global $wpdb;
    $sql = "SELECT pt.token_id, pt.token, pt.type, ptm.meta_key, ptm.meta_value FROM {$wpdb->prefix}woocommerce_payment_tokens as pt
    INNER JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as ptm
    ON pt.token_id = ptm.payment_token_id
    WHERE pt.user_id = ".$user_id;
    $data = $wpdb->get_results($sql, ARRAY_A);
    $cardsData = [];
    foreach($data as $d) {
        $cardsData[$d['token_id']]["token"] = $d['token'];
        $cardsData[$d['token_id']]["token_id"] = $d['token_id'];
        $cardsData[$d['token_id']][$d['meta_key']] = $d['meta_value'];
    }
    $cards = [];
    foreach ($cardsData as $c) {
        $cards[] = $c;
    }
    return $cards;
}
function _get_pickup_disabled_dates($is_meal) {
    if(!$is_meal){
        $dates = [date("Y-m-d"), date("Y-m-d", strtotime("+1 Days"))];
    }
    $disable_days = ["sunday","monday"];
    for($i=0; $i<=60; $i++){
        $day = strtolower(date("l", strtotime($i." days")));
        if(in_array($day, $disable_days)) {
            $dates[] = date("Y-m-d", strtotime($i." days"));
        }
    }
    array_push($dates,"2023-12-25", "2023-12-26","2024-02-06", "2024-03-29", "2024-04-01", "2024-06-03", "2024-06-28", "2024-10-28", "2024-11-15", "2024-12-25", "2024-12-26");
    
    return $dates;
}
run_petertimbs();
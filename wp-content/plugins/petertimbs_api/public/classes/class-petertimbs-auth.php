<?php

/**
 * Auth Class.
 *
 * @link       https://FeatherTechlabs.com
 * @since      1.0.0
 *
 * @package    Petertimbs
 * @subpackage Petertimbs/public
 * @author     FeatherTechlabs <dev@FeatherTechlabs.com>
 */
use \Firebase\JWT\JWT;
require_once( plugin_dir_path( dirname( __FILE__ ) ) .'/twilio/Twilio/autoload.php');
use Twilio\Rest\Client;

class Petertimbs_Auth {

    /* Constructor */
    public function __construct() {
        $this->twilio_sid =  TWILIO_SID;
        $this->twilio_auth_token = TWILIO_AUTH_TOKEN

        $this->twilio_from_number = TWILIO_FROM_NUMBER;
    }

    /**
     * Process login
     *
     * @since    1.0.0
     */
    public function login($request) {
        $response = [];
        $status = 200;
        $check = wp_authenticate($request->get_param('email'), $request->get_param('password'));
        if(!empty($check->errors)) {
            $response['success'] = false;
            $status = 400;

            if($check->errors['empty_username']) {
                $response['message'] = "Email address is required.";
            }
            if($check->errors['empty_password']) {
                $response['message'] = "Password is required.";
            }
            if($check->errors['invalid_email']) {
                $response['message'] = "Invalid email address.";
            }
            if($check->errors['incorrect_password']) {
                $response['message'] = "Incorrect password.";
            }
        } else {
            $opt = get_option('peter_timbs');
            $is_verified = get_user_meta($check->ID, 'is_mobile_verified', 1);
            if(!empty($request->get_param('device_token'))) {
                update_user_meta($check->ID, "device_token", $request->get_param('device_token'));
            }
            $response['success'] = true;
            $status = 200;
            $key = API_KEY;
            $token = array(
                "user_id" => $check->ID,
                "iat" => 1356999524,
                "nbf" => 1357000000
            );

            $jwt = JWT::encode($token, $key);

            $response['data'] = [
                "_token" => $jwt,
                "email"  => $check->user_email,
                "name"   => $check->display_name,
                "is_verified" => ($is_verified) ? true : false,
                'delivery_range' => $opt['delivery_distance'],
                'store_location' => "70+Edgeware+Rd+Edgeware+Christchurch+NZ"
            ];
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function singup($request) {
        $post = $request->get_json_params();
        $response = [];
        $status = 400;
        if(email_exists($post['email'])) {
            $response['success'] = false;
            $response['message'] = "Email aready exists!";
        }

        if(empty($post['email'])) {
            $response['success'] = false;
            $response['message'] = "Provide email address!";
        }

        if(empty($post['phone'])) {
            $response['success'] = false;
            $response['message'] = "Provide valid phone number!";
        }


        if(empty($post['last_name'])) {
            $response['success'] = false;
            $response['message'] = "Provide valid last name!";
        }

        if(empty($post['first_name'])) {
            $response['success'] = false;
            $response['message'] = "Provide valid first name!";
        }

        $uppercase = preg_match('@[A-Z]@', $post['password']);
        $lowercase = preg_match('@[a-z]@', $post['password']);
        $number    = preg_match('@[0-9]@', $post['password']);
        $specialChars = preg_match('@[^\w]@', $post['password']);

        if((!$uppercase) || (!$lowercase) || (!$number) || (!$specialChars) || (strlen($post['password']) < 12)){
            $response['success'] = false;
            $response['message'] = "the password should contain at least 12 characters and use upper and lower case letters, numbers and a special character!";
        }

        if(empty($response)) {
            $user_id = wp_insert_user([
                'user_login' => $post['email'],
                'user_email' => $post['email'],
                'user_pass'  => $post['password'],
                'role'       => 'customer'
            ]);
            if($user_id) {
                $status = 200;
                update_user_meta($user_id, "device_token", $post['device_token'] );
                update_user_meta($user_id, 'billing_phone', $post['phone']);
                update_user_meta($user_id, 'first_name', $post['first_name']);
                update_user_meta($user_id, 'last_name', $post['last_name']);
                update_user_meta($user_id, 'billing_first_name', $post['first_name']);
                update_user_meta($user_id, 'billing_last_name', $post['last_name']);
                update_user_meta($user_id, 'is_mobile_verified', 1);
                update_user_meta($user_id, "loyalty_card_number", $post['loyalty_card_number']);

                if(!empty($post['full_address'])) {
                    update_user_meta($user_id, "full_address", $post['full_address']);
                }
                if(!empty($post['description'])) {
                    update_user_meta($user_id, "full_address", $post['description']);
                }
                if(!empty($post['billing_address_1'])) {
                    update_user_meta($user_id, "billing_address_1", $post['billing_address_1']);
                }
                if(!empty($post['billing_address_2']) && $post['billing_address_2'] != "undefined") {
                    update_user_meta($user_id, "billing_address_2", $post['billing_address_2']);
                }
                if(!empty($post['billing_city'])) {
                    update_user_meta($user_id, "billing_city", $post['billing_city']);
                }
                if(!empty($post['billing_state'])) {
                    update_user_meta($user_id, "billing_state", $post['billing_state']);
                }
                if(!empty($post['billing_postcode'])) {
                    update_user_meta($user_id, "billing_postcode", $post['billing_postcode']);
                }
                update_user_meta($user_id, "billing_country", 'NZ');

                $response['success'] = true;
                $response['message'] = "User created!";
                $response['data'] = [
                    'email' => $post['email'],
                    'phone' => $post['phone'],
                    'otp'   => $otp
                ];
            }
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function forgot_password($request) {
        $post = $request->get_json_params();
        $status = 400;
        $response=[];
        if(!email_exists($post['email'])) {
            $response['success'] = false;
            $response['message'] = "Email not found!";
        }
        if(empty($post['email'])) {
            $response['success'] = false;
            $response['message'] = "Provide email address!";
        }

        if(empty($response)) {
            $user = get_user_by('email', $post['email']);
            $reset_key = get_password_reset_key( $user );
            do_action('woocommerce_reset_password_notification', $user->user_login, $reset_key);
            $status = 200;
            $response['success'] = true;
            $response['message'] = "Reset password email sent!";
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function verify_otp($request) {
        $post = $request->get_json_params();
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}phone_otp WHERE phone = '".$post['phone']."' AND otp = '".$post['otp']."' LIMIT 0, 1";
        $data = $wpdb->get_row($sql, ARRAY_A);

        if(!empty($data)) {
            $phone = $data['phone'];
            $otp   = $data['otp'];
            if($phone == $post['phone'] && $otp == $post['otp']) {
                $status = 200;
                $delete = [
                    "phone" => $phone
                ];
                $wpdb->delete($wpdb->prefix."phone_otp", $delete);
                $response = [
                    "success" => true,
                    "message" => "Well done, your number is verified and your account has been created."
                ];
            } else {
                $status = 200;
                $response = [
                    "success" => false,
                    "message" => "Sorry, you may have entered the wrong code please try again"
                ];
            }
        } else {
            $status = 200;
            $response = [
                "success" => false,
                "message" => "Sorry, you may have entered the wrong code please try again"
            ];
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function get_profile($request) {
        $this->user = pt_validate_request($request);
        $post = $request->get_json_params();
        $response['success'] = true;
        $response['user'] = $this->user;
        $response['user']->billing_phone = get_user_meta($this->user->user_id, "billing_phone", true);
        $response['user']->profile_pic = get_user_meta($this->user->user_id, "profile_pic", true);
        $response['user']->full_address = get_user_meta($this->user->user_id, "full_address", true);

        $response['user']->loyalty_card_number = get_user_meta($this->user->user_id, "loyalty_card_number", true);
        $response['user']->billing_company = get_user_meta($this->user->user_id, "billing_company", true);
        $response['user']->billing_company = (empty($response['user']->billing_company) || $response['user']->billing_company == "undefined") ? "" : $response['user']->billing_company;
        $response['user']->loyalty_card_number = (empty($response['user']->loyalty_card_number) || $response['user']->loyalty_card_number == "undefined") ? "" : $response['user']->loyalty_card_number;

        $key = API_KEY;
        $token = array(
            "user_id" => $this->user->user_id,
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        $jwt = JWT::encode($token, $key);
        $response['user']->_token = $jwt;

        global $wpdb;

        $sql = "SELECT pt.token_id, pt.token, pt.type, ptm.meta_key, ptm.meta_value FROM {$wpdb->prefix}woocommerce_payment_tokens as pt
        INNER JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as ptm
        ON pt.token_id = ptm.payment_token_id
        WHERE pt.user_id = ".$this->user->user_id;
        $data = $wpdb->get_results($sql, ARRAY_A);
        $cards = [];
        foreach($data as $d) {
            $cards[$d['token_id']]["token"] = $d['token'];
            $cards[$d['token_id']]["token_id"] = $d['token_id'];
            $cards[$d['token_id']][$d['meta_key']] = $d['meta_value'];
        }
        $response['user']->cardList = $cards;

        unset($response['user']->iat);
        unset($response['user']->nbf);
        wp_send_json($response, 200);
        wp_die();
    }

    public function get_address($request) {
        $this->user = pt_validate_request($request);
        $response['full_address'] = get_user_meta($this->user->user_id, "full_address", true);
        $response['success'] = true;
        wp_send_json($response, $status);
        wp_die();
    }

    public function update_profile($request) {
        $this->user = pt_validate_request($request);

        $post = $request->get_body_params();
        $file = $request->get_file_params();

        $phone =  get_user_meta($this->user->user_id, "billing_phone", true);

        if(!empty($post['first_name'])) {
            update_user_meta($this->user->user_id, "first_name", $post['first_name']);
        }
        if(!empty($post['last_name'])) {
            update_user_meta($this->user->user_id, "last_name", $post['last_name']);
        }
        if(!empty($post['billing_address_1'])) {
            update_user_meta($this->user->user_id, "billing_address_1", $post['billing_address_1']);
        }
        if(!empty($post['billing_address_2']) && $post['billing_address_2'] != "undefined") {
            update_user_meta($this->user->user_id, "billing_address_2", $post['billing_address_2']);
        }
        if(!empty($post['billing_city'])) {
            update_user_meta($this->user->user_id, "billing_city", $post['billing_city']);
        }
        if(!empty($post['billing_state'])) {
            update_user_meta($this->user->user_id, "billing_state", $post['billing_state']);
        }
        if(!empty($post['billing_postcode'])) {
            update_user_meta($this->user->user_id, "billing_postcode", $post['billing_postcode']);
        }
        if(!empty($post['billing_company']) && $post['billing_company'] != "undefined") {
            update_user_meta($this->user->user_id, "billing_company", $post['billing_company']);
        }
        if(!empty($post['loyalty_card_number'])) {
            update_user_meta($this->user->user_id, "loyalty_card_number", $post['loyalty_card_number']);
        }
        if(!empty($post['password'])) {
            wp_set_password( $post['password'], $this->user->user_id);
        }
        if(!empty($post['full_address'])) {
            update_user_meta($this->user->user_id, "full_address", $post['full_address']);
        }
        if(!empty($post['description'])) {
            update_user_meta($this->user->user_id, "full_address", $post['description']);
        }
        if(!empty($file) && !empty($file['profile_pic'])) {
            require_once ABSPATH."wp-admin/includes/file.php";
            $obj = wp_handle_upload($file['profile_pic'], array('test_form' => FALSE));
            update_user_meta($this->user->user_id, "profile_pic", $obj['url']);
        }
        if(!empty($post['billing_phone'])) {
            update_user_meta($this->user->user_id, "_tmp_phone", $post['billing_phone']);
        }

        if(!empty($post['email']) && $this->user->email != $post['email']) {
            if(false == get_user_by( 'email', $post['email'] ) ) {
                $args = array(
                    'ID'         => $this->user->user_id,
                    'user_email' => $post['email']
                );
                wp_update_user( $args );
                update_user_meta($this->user->user_id, "billing_email", $post['email']);
            } else {
                $response['success'] = false;
                $response['message'] = "This E-mail is already exists";
                wp_send_json($response, 200);
                wp_die();
            }
        }

        $is_phone_changed = false;
        if(!empty($post['billing_phone']) && $phone != $post['billing_phone']) {
            $otp = rand(1000, 9999);
            $this->send_otp_notification($post['billing_phone'], $otp);
            $is_phone_changed = true;
            update_user_meta($this->user->user_id, "new_otp", $otp);
        }


        $response['success'] = true;
        $response['user'] = $post;
        $response['user']['full_address'] = get_user_meta($this->user->user_id, 'full_address', true);;
        $response['user']['is_phone_changed'] = $is_phone_changed;
        unset($response['user']['oauth_token']);
        wp_send_json($response, 200);
        wp_die();
    }

    public function update_phone($request) {
        $this->user = pt_validate_request($request);
        $post = $request->get_body_params();
        $otp  = get_user_meta( $this->user->user_id, "new_otp", true );
        $new_phone = get_user_meta($this->user->user_id, "_tmp_phone", true);
        if($otp == $post['otp']) {
            update_user_meta($this->user->user_id, "new_otp", $otp);
            update_user_meta($this->user->user_id, "billing_phone", $new_phone);
            $response['success'] = true;
            $response['message'] = 'Well done, your number is verified.';
        } else {
            $response = [
                "success" => false,
                "message" => "Sorry, you may have entered the wrong code please try again"
            ];
        }
        $response['otp'] = $otp;
        $response['request_otp'] = $post['otp'];
        wp_send_json($response, 200);
        wp_die();
    }

    private function send_otp_notification($phone, $otp) {
        try {
            $phone = str_replace("+", "", $phone);
            $phone = "+64".$phone;
            $client = new Client($this->twilio_sid, $this->twilio_auth_token);
            $send = [
                'body' => "Your Peter Timbs Meats verification code is: ".$otp,
                'from' => $this->twilio_from_number
            ];
            $response = $client->messages->create($phone, $send);
        } catch(Execption $e) {
            wp_mail("pdshah3690@gmail.com", "debugging", $e->getMessage());
        }
    }

    public function verify_email($request) {
        $post = $request->get_json_params();
        $response = [];
        $status = 200;
        if(email_exists($post['email'])) {
            $response['success'] = false;
            $response['message'] = "Sorry, that email already exists, please try again or you can use the forgot password feature.";
        } else {
            $opt = get_option('peter_timbs');
            $response['success'] = true;
            $response['delivery_range'] = $opt['delivery_distance'];
            $response['store_location'] = "70+Edgeware+Rd+Edgeware+Christchurch+NZ";
            $response['message'] = "Email doesn't exist!";
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function send_otp($request) {
        global $wpdb;
        $post = $request->get_json_params();
        $response = [];
        $status = 200;
        $otp = rand(1000, 9999);
        $data = [
            "phone" => $post['phone'],
            "otp"   => $otp
        ];
        $wpdb->insert($wpdb->prefix."phone_otp", $data);
        $this->send_otp_notification($post['phone'], $otp);
        $response['success'] = true;
        $response['otp'] = $otp;
        $response['request'] = $post;
        wp_send_json($response, $status);
        wp_die();
    }

    public function notification_index($request) {
        global $wpdb;
        $this->user = pt_validate_request($request);
        $user_id = $this->user->user_id;

        $status = 200;
        $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE user_id = ".$this->user->user_id." and is_sent = 1 ORDER BY id DESC";
        // $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE user_id = ".$this->user->user_id."  ORDER BY id DESC";
        $data = $wpdb->get_results($sql, ARRAY_A);
        $response = [];
        foreach($data as $value){
            $response[] = [
                'id' => $value['id'],
                'title' => $value['title'],
                'content' => $value['body'],
                'date' => $value['schedule_at'],
                'is_seen' => $value['is_seen'],
            ];
        }
        wp_send_json($response, $status);
        wp_die();
    }

    public function notification_seen($request) {
        global $wpdb;

        $this->user = pt_validate_request($request);
        $user_id = $this->user->user_id;
        $post = $request->get_json_params();

        if($post['id'] == -1){
            $query = $wpdb->update(
                $wpdb->prefix."notification",
                ["is_seen" => 1],
                ["user_id" => $user_id]
            );
        }
        else{
            $query = $wpdb->update(
                $wpdb->prefix."notification",
                ["is_seen" => 1],
                ["id" => $post['id']]
            );
        }


        $response['message'] = "Notification Seen!";

        $status = 200;
        wp_send_json($response, $status);
        wp_die();
    }

    public function delete_account($request) {
        global $wpdb;
        $this->user = pt_validate_request($request);

        $to = "sales@petertimbsmeats.co.nz";
        $subject = "User Account Delete Request";
        $message = "<p>Hi,</p>
                    <p>The below user has requested that their account be deleted via the mobile app</p>
                    <p>Full Name: ". $this->user->first_name . " ". $this->user->last_name." </p>
                    <p>Email: ".$this->user->email."</p>";
        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Sales Peter Timbs Meats <sales@petertimbsmeats.co.nz>');

        wp_mail($to, $subject, $message, $headers);
        wp_send_json(["success" => true]);
        wp_die();
    }

    public function guest_login($request) {
        $response = [];
        $status = 200;

        $response['success'] = true;
        $status = 200;
        $key = API_KEY;
        $token = array(
            "user_id" => 0,
            "user_email" => "guest_".time()."@petertimbs.co.nz",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        $jwt = JWT::encode($token, $key);

        $response['data'] = [
            "_token" => $jwt,
            "email"  => "guest_".time()."@petertimbs.co.nz",
        ];
        wp_send_json($response, $status);
        wp_die();
    }
}
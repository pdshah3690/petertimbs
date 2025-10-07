<?php
defined( 'ABSPATH' ) || exit;

class PeterTimbs_Settings_Admin {

    private $name;

    public function __construct() {
        $this->name = "peter_timbs";
        $this->petertimbs_settings_handler();
    }

    public function petertimbs_settings_handler() {
        add_action ( 'admin_menu', [$this, 'register_petertimbs_settings_page'] );
        add_action ( 'admin_init', [$this, 'petertimbs_admin_settings_save'] );
        add_filter( 'woocommerce_available_payment_gateways', [$this, 'check_stripe_gateway'] );
        add_action( 'wp_ajax_send_notification', [$this, 'send_notification']);
        add_action( 'wp_ajax_delete_notification', [$this, 'delete_notification']);
        add_action( 'wp_ajax_view_notification', [$this, 'view_notification']);
    }



    public function register_petertimbs_settings_page() {

        add_menu_page(

            __( 'Peter Timbs Settings', 'Peter Timbs Settings' ),

            'Peter Timbs Settings',

            'manage_options',

            $this->name,

            array($this, 'display_splashsms_settings_page'),

            null,

            '90'

        );
        add_submenu_page(
        $this->name,
        __( 'Peter Timbs Notification', 'Peter Timbs Notification' ),
        'Peter Timbs Notification',
        'manage_options',
        'peter_timbs_notification',
        [$this,'display_notification_page']
      );
        add_submenu_page(
        $this->name,
        __( 'Notification List', 'Notification List' ),
        'Notification List',
        'manage_options',
        'peter_timbs_notification_lst',
        [$this,'display_notification_list']
      );

    }



    /**

    *   Render Administration Settings Page

    *   @since 0.0.1

    */

    public function display_splashsms_settings_page() {

        include_once(dirname(__FILE__).'/../partials/petertimbs-admin-display.php');

    }

    // notification code start
    public function display_notification_page() {
        $customers = $this->_get_customer();
        $products = wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) );
        include_once(dirname(__FILE__).'/../partials/petertimbs-admin-notification.php');

    }
    public function display_notification_list() {
        $customers = $this->_get_customer();
        if($_GET['id']){
            include_once(dirname(__FILE__).'/../partials/petertimbs-admin-notification-view.php');            
        }else{
            include_once(dirname(__FILE__).'/../partials/petertimbs-admin-notification-list.php');
        }

    }



    private function _get_customer() {
        global $wpdb;
        $data = get_users( array(
                "meta_key" => "device_token",
                "meta_value" => '',
                "compare" => '!=',
            ) );
        $customers = [];
        foreach ($data as $d)
        {
            $first_name = get_user_meta( $d->data->ID, 'first_name', true );
            $last_name = get_user_meta( $d->data->ID, 'last_name', true );

            $customers[] = [
                'ID' => $d->data->ID,
                'email' => $d->data->user_email,
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
        }
        return $customers;
    }

    public function send_notification() {
        global $wpdb;
        $time_stamp = date("Y-m-d H:i:s");

        if(wp_doing_ajax()) {
            // $metadata = ['product_id' => $_POST['product_id'] ];
            
            if(!($_POST['schedule_at'])) {
                $date = date("Y-m-d H:i:s");
            } else {
                $date = $_POST['schedule_at'];
            }

            if($_POST['all_user'] == 'true') {
               $data = get_users();
                $all_userids = [];
                foreach ($data as $d) {
                    $device_token = get_user_meta($d->data->ID, "device_token", 1);
                    if(empty($device_token)) {
                        continue;
                    }
                    $query = $wpdb->insert($wpdb->prefix.'notification',
                        array(
                            'user_id'  => $d->data->ID,
                            'title'  => $_POST['title'],
                            'body'  => $_POST['body'],
                            'schedule_at' => $date,
                            'type' => $_POST['type'],
                            'product_id' => $_POST['product_id'],
                            'time_stamp' => $time_stamp,
                        )
                    );
                }
            } else if(!empty($_POST['zone'])) {
                $options = get_option($this->name);
                $zonePostCode = $options[$_POST['zone']]['post_code'];
                $zonePostCode = explode(",",$zonePostCode);
                $all_user = get_users();
                $userId = [];
                foreach($all_user as $user) {
                        $device_token = get_user_meta($user->ID, "device_token", 1);
                        if(empty($device_token)) {
                            continue;
                        }
//                     $customer = new WC_Customer( $user->ID );
//                     $last_order = $customer->get_last_order();
//                     if ( ! $last_order ) {
//                         continue;
//                     }
//                     $order_id = $last_order->get_id();
//                     $order = wc_get_order($order_id);
//                     $order_data = $order->get_data();
//                     $post_code = $order_data['billing']['postcode'];
					$post_code = get_user_meta($user->ID, "billing_postcode", 1);
                    if(in_array($post_code,$zonePostCode)) {
                        $userId[]   = $user->ID;
                    }
                }
                $userId = array_unique($userId);
                foreach($userId as $user_id) {
                    $query = $wpdb->insert($wpdb->prefix.'notification',
                        array(
                            'user_id'  => $user_id,
                            'title'  => $_POST['title'],
                            'body'  => $_POST['body'],
                            'schedule_at' => $date,
                            'type' => $_POST['type'],
                            'product_id' => $_POST['product_id'],
                            'time_stamp' => $time_stamp,
                        )
                    );
                }
            } else {
                foreach($_POST['userId'] as $user_id) {
                    $device_token = get_user_meta($user_id, "device_token", 1);
                    if(empty($device_token)) {
                        continue;
                    }
                    $query = $wpdb->insert($wpdb->prefix.'notification',
                        array(
                            'user_id'  => $user_id,
                            'title'  => $_POST['title'],
                            'body'  => $_POST['body'],
                            'schedule_at' => $date,
                            'type' => $_POST['type'],
                            'product_id' => $_POST['product_id'],
                            'time_stamp' => $time_stamp,
                        )
                    );
                }
            }

            $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE is_sent = 0 and type = 'instant' ";
            $data = $wpdb->get_results($sql, ARRAY_A);

            // Live api key
            $api_key = "AAAAczGtHOk:APA91bG66Vi0YsvbaVkDlRwJ0YjRSLp3tfFG3tk-GLl-7HY6ydXoLzZ_B2Ns1e3qxXmQjqBifBxpjaKaqD6BJpFWm9ud6SpPw8LkAdCOh5lDLhFvJlOXKE4ll3bVr6pe-VWQQwfJC6dD";

            // Testing api key
            // $api_key = "AAAAJb6AMMY:APA91bFmFPIued7MjI9Du01oqM0tWEDDNsSbJNyzp61SEn70XpRqpinD7_lbnNgyubPXBz-lF5VFW9tH_SrPgH9dVuROT-NUc6PEHVhLZrUOltV7LJN0dsA2eKrGHQW9w3wgXRHDCqTm";
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
                $wpdb->update(
                    $wpdb->prefix."notification",
                    ["is_sent" => 1],
                    ["id" => $d["id"]]
                );
            }

            $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE time_stamp = '$time_stamp'";
            $data = $wpdb->get_results($sql, ARRAY_A);
            $i = 1;
            $html = '';
            $html .="<table class='notification_list'>
                <thead>
                    <tr>
                        <td>#</td>
                        <td>User Name</td>
                        <td>Is Sent</td>
                    </tr>
                </thead>
                <tbody>";
            foreach($data as $d){
                $user_name = get_user_by( 'id', $d['user_id'] )->display_name;
                $user_email = get_user_by( 'id', $d['user_id'] )->user_email;
                        $html .="<tr>
                            <td>".$i."</td>
                            <td>&lt;".$user_name."&gt; ".$user_email."</td>
                            <td>".($d['is_sent'] == 0 ? 'Not Received' : 'Sent')."</td>
                        </tr>";

                $i++;
            }
            $html .="</tbody>
            </table>";
            $response =[
                "success" => true,
                "message" => "Notifications Send Successfully",
                "list" => $html,
                "response" => $response
            ];
            wp_send_json($response,200);
        }
    }

    public function view_notification()
    {
        global $wpdb;
        if(wp_doing_ajax()){
            $time_stamp = $_POST['time_stamp'];
            $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE time_stamp = '$time_stamp' ";
            $data = $wpdb->get_results($sql, ARRAY_A);

            $i = 1;
            $html = '';
            $html .="<table class='notification_list'>
                <thead>
                    <tr>
                        <td>#</td>
                        <td>User Name</td>
                        <td>Is Sent</td>
                        <td>Action</td>
                    </tr>
                </thead>
                <tbody>";
            foreach($data as $d){
                $user_name = get_user_by( 'id', $d['user_id'] )->display_name;
                $user_email = get_user_by( 'id', $d['user_id'] )->user_email;
                $id = $d['id'];
                        $html .="<tr>
                        <input type='hidden' name='' class='".$id."' value='".$time_stamp."'>
                            <td>".$i."</td>
                            <td>&lt;".$user_name."&gt; ".$user_email."</td>
                            <td>".($d['is_sent'] == 0 ? 'Not Received' : 'Sent')."</td>
                            <td><button type='button' class='delete_notification' data-id =".$d['id'].">Delete</button></td>
                        </tr>";

                $i++;
            }
            $html .="</tbody>
                <button type='button' class='all_delete_notification' data-id = ".$id.">All Delete</button>
            </table>";

            $response =[
                "success" => true,
                "list" => $html,
            ];
            wp_send_json($response,200);  
        }   
    }

    public function delete_notification()
    {   
        global $wpdb;
        if(wp_doing_ajax()){
            if($_POST['time_stamp']){
                $wpdb->delete(
                    $wpdb->prefix."notification",
                    ["time_stamp" => $_POST['time_stamp']]
                );
            }else{
                $wpdb->delete(
                    $wpdb->prefix."notification",
                    ["id" => $_POST['notification_id']]
                );
            }
            $response =[
                "success" => true,
                "message" => "Notifications Delete Successfully"
            ];
            wp_send_json($response,200);
        }
    }

    public function petertimbs_admin_settings_save() {

        register_setting( $this->name,

            $this->name,

            [$this, 'peter_timbs_options_validate']);



        add_settings_section(

            'peter_timbs_main',

            'Main Settings',

            array($this, 'peter_timbs_section_text'),

            'petertimbs-settings-page');



        add_settings_field('sunday_business_hours', 'Sunday Business Hours :', [$this, 'sunday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('monday_business_hours', 'Monday Business Hours :', [$this, 'monday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('tuesday_business_hours', 'Tuesday Business Hours :', [$this, 'tuesday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('wednesday_business_hours', 'Wednesday Business Hours :', [$this, 'wednesday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('thursday_business_hours', 'Thursday Business Hours :', [$this, 'thursday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('friday_business_hours', 'Friday Business Hours :', [$this, 'friday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('saturday_business_hours', 'Saturday Business Hours :', [$this, 'saturday_business_hours'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('enable_stripe_on_site', 'Enable stripe on website :', [$this, 'enable_stripe_on_site'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('enable_stripe_on_app', 'Enable stripe on mobile app :', [$this, 'enable_stripe_on_app'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_field('enable_delivery_on_site', 'Enable delivery on website :', [$this, 'enable_delivery_on_site'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('enable_delivery_on_app', 'Enable delivery on mobile app :', [$this, 'enable_delivery_on_app'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('preparation_buffer_time', 'Preparation Buffer Time (minutes) :', [$this, 'preparation_buffer_time'], 'petertimbs-settings-page', 'peter_timbs_main');



        add_settings_section('peter_timbs_main', '', array($this, 'peter_timbs_delivery_section_text'), 'petertimbs-settings-delivery');



        add_settings_field('delivery_distance', 'Delivery distance (Km) :', [$this, 'delivery_distance'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        // add_settings_field('delivery_cut_off_time', 'Delivery cut off time (24 Hours format) :', [$this, 'delivery_cut_off_time'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('delivery_cut_off_day', 'Delivery cut off day:', [$this, 'delivery_cut_off_day'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('minimum_delivery_amount', 'Min. Delivery Amount: ', [$this, 'minimum_delivery_amount'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('sunday_delivery_postcodes', 'Postcodes for Sunday ', [$this, 'sunday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('monday_delivery_postcodes', 'Postcodes for Monday ', [$this, 'monday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('tuesday_delivery_postcodes', 'Postcodes for Tuesday ', [$this, 'tuesday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('wednesday_delivery_postcodes', 'Postcodes for Wednesday ', [$this, 'wednesday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('thursday_delivery_postcodes', 'Postcodes for Thursday ', [$this, 'thursday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('friday_delivery_postcodes', 'Postcodes for Friday ', [$this, 'friday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('saturday_delivery_postcodes', 'Postcodes for Saturday ', [$this, 'saturday_delivery_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');





        add_settings_field('sunday_delivery_cut_off', 'Sunday Max Delivery Orders', [$this, 'sunday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('monday_delivery_cut_off', 'Monday Max Delivery Orders', [$this, 'monday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('tuesday_delivery_cut_off', 'Tuesday Max Delivery Orders', [$this, 'tuesday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('wednesday_delivery_cut_off', 'Wednesday Max Delivery Orders', [$this, 'wednesday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('thursday_delivery_cut_off', 'Thursday Max Delivery Orders', [$this, 'thursday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('friday_delivery_cut_off', 'Friday Max Delivery Orders', [$this, 'friday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('saturday_delivery_cut_off', 'Saturday Max Delivery Orders', [$this, 'saturday_delivery_cut_off'], 'petertimbs-settings-delivery', 'peter_timbs_main');



        add_settings_field('zone_one', 'Zone One Postcodes', [$this, 'zone_one'], 'petertimbs-settings-delivery', 'peter_timbs_main');
        add_settings_field('zone_two', 'Zone Two Postcodes', [$this, 'zone_two'], 'petertimbs-settings-delivery', 'peter_timbs_main');
        add_settings_field('zone_three', 'Zone Three Postcodes', [$this, 'zone_three'], 'petertimbs-settings-delivery', 'peter_timbs_main');
        add_settings_field('zone_four', 'Zone Four Postcodes', [$this, 'zone_four'], 'petertimbs-settings-delivery', 'peter_timbs_main');
        add_settings_field('north_island_postcodes', 'North Island Postcodes', [$this, 'north_island_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');
        add_settings_field('south_island_postcodes', 'South Island Postcodes', [$this, 'south_island_postcodes'], 'petertimbs-settings-delivery', 'peter_timbs_main');


    }



    /**

    *   Sanitize Settings fields

    *

    *   @since 0.0.1

    */

    public function peter_timbs_options_validate($input) {

        $newinput['sunday_business_hours']  = trim($input['sunday_business_hours']);

        $newinput['monday_business_hours']  = trim($input['monday_business_hours']);

        $newinput['tuesday_business_hours']     = trim($input['tuesday_business_hours']);

        $newinput['wednesday_business_hours']   = trim($input['wednesday_business_hours']);

        $newinput['thursday_business_hours']    = trim($input['thursday_business_hours']);

        $newinput['friday_business_hours']  = trim($input['friday_business_hours']);

        $newinput['saturday_business_hours']    = trim($input['saturday_business_hours']);

        $newinput['preparation_buffer_time']    = trim($input['preparation_buffer_time']);

        $newinput['enable_stripe_on_site']  = empty($input['enable_stripe_on_site']) ? 0 : 1;

        $newinput['enable_stripe_on_app']   = empty($input['enable_stripe_on_app']) ? 0 : 1;

        $newinput['enable_delivery_on_site']    = empty($input['enable_delivery_on_site']) ? 0 : 1;

        $newinput['enable_delivery_on_app']     = empty($input['enable_delivery_on_app']) ? 0 : 1;

        $newinput['delivery_distance']  = trim($input['delivery_distance']);

        $newinput['delivery_cut_off_time']  = trim($input['delivery_cut_off_time']);

        $newinput['delivery_cut_off_day']  = trim($input['delivery_cut_off_day']);

        $newinput['minimum_delivery_amount']  = trim($input['minimum_delivery_amount']);



        $newinput['sunday_delivery_postcodes']     = trim($input['sunday_delivery_postcodes']);

        $newinput['monday_delivery_postcodes']     = trim($input['monday_delivery_postcodes']);

        $newinput['tuesday_delivery_postcodes']     = trim($input['tuesday_delivery_postcodes']);

        $newinput['wednesday_delivery_postcodes']     = trim($input['wednesday_delivery_postcodes']);

        $newinput['thursday_delivery_postcodes']     = trim($input['thursday_delivery_postcodes']);

        $newinput['friday_delivery_postcodes']     = trim($input['friday_delivery_postcodes']);

        $newinput['saturday_delivery_postcodes']     = trim($input['saturday_delivery_postcodes']);



        $newinput['sunday_delivery_cut_off']     = trim($input['sunday_delivery_cut_off']);

        $newinput['monday_delivery_cut_off']     = trim($input['monday_delivery_cut_off']);

        $newinput['tuesday_delivery_cut_off']     = trim($input['tuesday_delivery_cut_off']);

        $newinput['wednesday_delivery_cut_off']     = trim($input['wednesday_delivery_cut_off']);

        $newinput['thursday_delivery_cut_off']     = trim($input['thursday_delivery_cut_off']);

        $newinput['friday_delivery_cut_off']     = trim($input['friday_delivery_cut_off']);

        $newinput['saturday_delivery_cut_off']     = trim($input['saturday_delivery_cut_off']);

        $newinput['zone_one']     = $input['zone_one'];
        $newinput['zone_two']     = $input['zone_two'];
        $newinput['zone_three']     = $input['zone_three'];
        $newinput['zone_four']     = $input['zone_four'];
        $newinput['north_island_postcodes']     = $input['north_island_postcodes'];
        $newinput['south_island_postcodes']     = $input['south_island_postcodes'];




        return $newinput;

    }



    /**

    *   Render Section Header Text

    *

    *   @since 0.0.1

    */

    public function peter_timbs_section_text() {

        echo '<h3>Peter Timbs Settings</h3>';

        echo '<p>Store business hours in 24 hours Format. e.g 10:15|19:30</p>';

    }



    public function peter_timbs_delivery_section_text() {

        echo '<h3>Peter Timbs Delivery Settings</h3>';

    }





    public function sunday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[sunday_business_hours]' size='40' type='text' value='{$options['sunday_business_hours']}' />";

    }



    public function monday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[monday_business_hours]' size='40' type='text' value='{$options['monday_business_hours']}' />";

    }



    public function tuesday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[tuesday_business_hours]' size='40' type='text' value='{$options['tuesday_business_hours']}' />";

    }



    public function wednesday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[wednesday_business_hours]' size='40' type='text' value='{$options['wednesday_business_hours']}' />";

    }



    public function thursday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[thursday_business_hours]' size='40' type='text' value='{$options['thursday_business_hours']}' />";

    }



    public function friday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[friday_business_hours]' size='40' type='text' value='{$options['friday_business_hours']}' />";

    }



    public function saturday_business_hours() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[saturday_business_hours]' size='40' type='text' value='{$options['saturday_business_hours']}' />";

    }



    public function preparation_buffer_time() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[preparation_buffer_time]' size='40' type='text' value='{$options['preparation_buffer_time']}' />";

    }



    public function enable_stripe_on_site() {

        $options = get_option($this->name);

        echo "<input id='enable_stripe_on_site' name='$this->name[enable_stripe_on_site]' type='checkbox' value='1' ".checked( 1, $options['enable_stripe_on_site'], false )."/>";

    }



    public function enable_stripe_on_app() {

        $options = get_option($this->name);

        echo "<input id='enable_stripe_on_app' name='$this->name[enable_stripe_on_app]' type='checkbox' value='1' ".checked( 1, $options['enable_stripe_on_app'], false )."/>";

    }



    public function enable_delivery_on_site() {

        $options = get_option($this->name);

        echo "<input id='enable_delivery_on_site' name='$this->name[enable_delivery_on_site]' type='checkbox' value='1' ".checked( 1, $options['enable_delivery_on_site'], false )."/>";

    }



    public function enable_delivery_on_app() {

        $options = get_option($this->name);

        echo "<input id='enable_delivery_on_app' name='$this->name[enable_delivery_on_app]' type='checkbox' value='1' ".checked( 1, $options['enable_delivery_on_app'], false )."/>";

    }



    public function check_stripe_gateway( $available_gateways ) {

        $options = get_option($this->name);

        if ( !empty( $available_gateways['stripe'] ) && !$options['enable_stripe_on_site'] ) {

            unset( $available_gateways['stripe'] );

        }

        return $available_gateways;

    }



    public function delivery_distance() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[delivery_distance]' type='text' value='{$options['delivery_distance']}' />";

    }



    public function delivery_cut_off_time() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[delivery_cut_off_time]' type='text' value='{$options['delivery_cut_off_time']}' />";

    }



    public function delivery_cut_off_day() {

        $options = get_option($this->name);

        $day = $options['delivery_cut_off_day'];

        echo "<label><input id='' name='$this->name[delivery_cut_off_day]' type='radio' value='0' ".checked( '0', $options['delivery_cut_off_day'], false )."/> Today </label>";

        echo "<label><input id='' name='$this->name[delivery_cut_off_day]' type='radio' value='1' ".checked( '1', $options['delivery_cut_off_day'], false )."/> Yesterday </label>";

        echo "<label><input id='' name='$this->name[delivery_cut_off_day]' type='radio' value='2' ".checked( '2', $options['delivery_cut_off_day'], false )."/> Day before yesterday </label>";

    }



    public function minimum_delivery_amount() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[minimum_delivery_amount]' type='text' value='{$options['minimum_delivery_amount']}' />";

    }



    public function sunday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[sunday_delivery_postcodes]' type='text' value='{$options['sunday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function monday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[monday_delivery_postcodes]' type='text' value='{$options['monday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function tuesday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[tuesday_delivery_postcodes]' type='text' value='{$options['tuesday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function wednesday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[wednesday_delivery_postcodes]' type='text' value='{$options['wednesday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function thursday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[thursday_delivery_postcodes]' type='text' value='{$options['thursday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function friday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[friday_delivery_postcodes]' type='text' value='{$options['friday_delivery_postcodes']}' style='width: 500px;' />";

    }



    public function saturday_delivery_postcodes() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[saturday_delivery_postcodes]' type='text' value='{$options['saturday_delivery_postcodes']}' style='width: 500px;' />";

    }





    public function sunday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[sunday_delivery_cut_off]' type='text' value='{$options['sunday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function monday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[monday_delivery_cut_off]' type='text' value='{$options['monday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function tuesday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[tuesday_delivery_cut_off]' type='text' value='{$options['tuesday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function wednesday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[wednesday_delivery_cut_off]' type='text' value='{$options['wednesday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function thursday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[thursday_delivery_cut_off]' type='text' value='{$options['thursday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function friday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[friday_delivery_cut_off]' type='text' value='{$options['friday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function saturday_delivery_cut_off() {

        $options = get_option($this->name);

        echo "<input id='' name='$this->name[saturday_delivery_cut_off]' type='text' value='{$options['saturday_delivery_cut_off']}' style='width: 500px;' />";

    }



    public function zone_one() {
        $options = get_option($this->name);
        // echo "<pre>";
        // print_r($options);
        // die;
        echo "<input id='' name='$this->name[zone_one][post_code]' type='text' value='{$options['zone_one']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[zone_one][delivery_fee]' type='text' value='{$options['zone_one']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[zone_one][min_cart_amount]' type='text' value='{$options['zone_one']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[zone_one][discount]' type='text' value='{$options['zone_one']['discount']}' style='width: 91px;' />";


        echo "<select name='$this->name[zone_one][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['zone_one']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['zone_one']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";
    }

    public function zone_two() {
        $options = get_option($this->name);
        echo "<input id='' name='$this->name[zone_two][post_code]' type='text' value='{$options['zone_two']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[zone_two][delivery_fee]' type='text' value='{$options['zone_two']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[zone_two][min_cart_amount]' type='text' value='{$options['zone_two']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[zone_two][discount]' type='text' value='{$options['zone_two']['discount']}' style='width: 91px;' />";

        echo "<select name='$this->name[zone_two][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['zone_two']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['zone_two']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";


    }

    public function zone_three() {
        $options = get_option($this->name);
        echo "<input id='' name='$this->name[zone_three][post_code]' type='text' value='{$options['zone_three']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[zone_three][delivery_fee]' type='text' value='{$options['zone_three']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 4px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[zone_three][min_cart_amount]' type='text' value='{$options['zone_three']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[zone_three][discount]' type='text' value='{$options['zone_three']['discount']}' style='width: 91px;' />";

        echo "<select name='$this->name[zone_three][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['zone_three']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['zone_three']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";


    }

    public function zone_four() {
        $options = get_option($this->name);
        echo "<input id='' name='$this->name[zone_four][post_code]' type='text' value='{$options['zone_four']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[zone_four][delivery_fee]' type='text' value='{$options['zone_four']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[zone_four][min_cart_amount]' type='text' value='{$options['zone_four']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[zone_four][discount]' type='text' value='{$options['zone_four']['discount']}' style='width: 91px;' />";

        echo "<select name='$this->name[zone_four][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['zone_four']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['zone_four']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";
    }
    
    public function north_island_postcodes() {
        $options = get_option($this->name);
        echo "<input id='' name='$this->name[north_island_postcodes][post_code]' type='text' value='{$options['north_island_postcodes']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[north_island_postcodes][delivery_fee]' type='text' value='{$options['north_island_postcodes']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[north_island_postcodes][min_cart_amount]' type='text' value='{$options['north_island_postcodes']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[north_island_postcodes][discount]' type='text' value='{$options['north_island_postcodes']['discount']}' style='width: 91px;' />";

        echo "<select name='$this->name[north_island_postcodes][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['north_island_postcodes']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['north_island_postcodes']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";
    }

    public function south_island_postcodes() {
        $options = get_option($this->name);
        echo "<input id='' name='$this->name[south_island_postcodes][post_code]' type='text' value='{$options['south_island_postcodes']['post_code']}' style='width: 238px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Delivery Fee</label>";
        echo "<input id='' name='$this->name[south_island_postcodes][delivery_fee]' type='text' value='{$options['south_island_postcodes']['delivery_fee']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Min. Amount</label>";
        echo "<input id='' name='$this->name[south_island_postcodes][min_cart_amount]' type='text' value='{$options['south_island_postcodes']['min_cart_amount']}' style='width: 92px;' />";

        echo "<label style='font-weight: 600;padding:0px 10px 0px 10px'>Discount</label>";
        echo "<input id='' name='$this->name[south_island_postcodes][discount]' type='text' value='{$options['south_island_postcodes']['discount']}' style='width: 91px;' />";

        echo "<select name='$this->name[south_island_postcodes][discount_type]' style='margin: 10px 10px;'>
                <option value='fixed_amount' ".($options['south_island_postcodes']['discount_type'] == 'fixed_amount' ? 'selected' : '').">Fixed Amount</options>
                <option value='percentage' ".($options['south_island_postcodes']['discount_type'] == 'percentage' ? 'selected' : '').">Percentage</options>
            </select>";
    }

}



$peter_timbs_settings = new PeterTimbs_Settings_Admin();
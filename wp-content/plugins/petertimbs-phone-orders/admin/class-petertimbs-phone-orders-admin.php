<?php
defined( 'ABSPATH' ) || exit;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://feathertechlabs.com
 * @since      1.0.0
 *
 * @package    Petertimbs Phone Orders
 * @subpackage Petertimbs/admin
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Petertimbs Phone Orders
 * @subpackage Petertimbs/admin
 * @author     FeatherTechlabs <PluginDev@FeatherTechlabs.com>
 */
Class Petertimbs_Phone_Orders_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mvpiac_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mvpiac_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( 'datetime', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css', '', 1);
        wp_enqueue_style( "select2", 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pt-phone-orders-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mvpiac_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mvpiac_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script('datetime', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', array('jquery'), '1.1', true);
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array('jquery'), '4', false);
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pt-phone-orders-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function add_menu_link() {
        add_submenu_page( 
            'woocommerce', 
            __( 'Phone Orders', 'woocommerce' ), 
            __( 'Phone Orders', 'woocommerce' ), 
            'manage_woocommerce', 
            'petertimbs-phone-orders', 
            [$this, 'pt_phone_orders_handler'] );
    }

    public function pt_phone_orders_handler() {
        $products = $this->_get_all_products();
        $customers = $this->_get_customer_emails();
        include_once(plugin_dir_path(dirname(__FILE__)).'admin/templates/create-order.php');
    }

    private function _get_all_products() {
        $data = wc_get_products([
            'limit' => -1,
            'status' => ['private', 'publish'],
            'stock_status' => 'instock',
        ]);
        $products = [];
        foreach($data as $p) {
            if($p->get_type() === "simple") {
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $p->get_id() ), 'woocommerce_gallery_thumbnail' );
                $image = empty($image) ? "" : $image[0];
                $products[] = [
                    'id' => $p->get_id(),
                    'variation_id' => 0,
                    'name' => $p->get_name(),
                    'price' => $p->get_price(),
                    'image' =>  $image,
                    'is_simple' => true,
                ];
            } else {
                $is_bb_product = get_post_meta($p->get_id(), 'is_butcherbox_product', true);
                if($is_bb_product) {
                    continue;
                }
                foreach ($p->get_children() as $key => $variation_id) {
                    $product = wc_get_product($variation_id);
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $variation_id ), 'woocommerce_gallery_thumbnail' );
                    $image = empty($image) ? "" : $image[0];
                    $products[] = [
                        'id' => $product->get_id(),
                        'parent_id' => $p->get_id(),
                        'variation_id' => $product->get_variation_id(),
                        'name' => $product->get_name(),
                        'price' => $product->get_price(),
                        'image' =>  $image,
                        'is_simple' => false,
                    ];
                }
            }
        }
        return $products;
    }

    private function _get_customer_emails() {
        global $wpdb;
        $sql = "SELECT max(post_id) as order_id, meta_value from {$wpdb->prefix}postmeta where meta_key = '_billing_email' group by meta_value order by 1 desc";
        $data = $wpdb->get_results($sql, ARRAY_A);
        $customers = [];
        foreach ($data as $d) {
            $last_order = $d["order_id"];
            $customers[] = [
                'email' => $d['meta_value'],
                'first_name' => get_post_meta($last_order, '_billing_first_name', true),
                'last_name' => get_post_meta($last_order, '_billing_last_name', true)
            ];
        }
        return $customers;
    }

    public function get_customer_last_order_details() {
        $email = $_POST['email'];
        if(wp_doing_ajax()) {
            global $wpdb;
            $sql = "select post_id from {$wpdb->prefix}postmeta WHERE meta_key = '_billing_email' and meta_value = '{$email}' order by post_id DESC LIMIT 0,1";
            $data = $wpdb->get_row($sql, ARRAY_A);
            $order_id = $data['post_id'];
            $response = [
                "success" => true,
                "data" => [
                    "_customer_user" => get_post_meta($order_id, "_customer_user", 1),
                    "_billing_first_name" => get_post_meta($order_id, "_billing_first_name", 1),
                    "_billing_last_name" => get_post_meta($order_id, "_billing_last_name", 1),
                    "_billing_address_1" => get_post_meta($order_id, "_billing_address_1", 1),
                    "_billing_address_2" => get_post_meta($order_id, "_billing_address_2", 1),
                    "_billing_city" => get_post_meta($order_id, "_billing_city", 1),
                    "_billing_state" => get_post_meta($order_id, "_billing_state", 1),
                    "_billing_postcode" => get_post_meta($order_id, "_billing_postcode", 1),
                    "_billing_phone" => get_post_meta($order_id, "_billing_phone", 1),
                ]
            ];
        }
        echo json_encode($response);
        wp_die();
    }

    public function get_earlier_pickup_time() {
        if(wp_doing_ajax()) {
            $cart = $_POST['cart'];
            $total_prep_time = 0;
            foreach ($cart as $item) {
                $post_id = empty($item['variation_id']) ? $item['product_id'] : $item['variation_id'];
                $preparation_time = get_post_meta($post_id, "preparation_time", true);
                $total_prep_time = $total_prep_time + ($preparation_time * $item['qty']);
                if(empty($preparation_time)) {
                    continue;
                }
            }
            $prep_time = _calculate_pickup_datetime($total_prep_time);
            
            $response = [
                "success" => true,
                "preparation_time" => $prep_time
            ];
            echo json_encode($response);
            wp_die();
        }
    }

    public function create_phone_order() {
        if(wp_doing_ajax()) {

            if(empty($_POST['order_id'])){

                $address = array(
                    'first_name' => $_POST['_billing_details']['_billing_first_name'],
                    'last_name'  => $_POST['_billing_details']['_billing_last_name'],
                    'email'      => $_POST['_billing_details']['_billing_email'],
                    'address_1'  => $_POST['_billing_details']['_billing_address_1'],
                    'address_2'  => $_POST['_billing_details']['_billing_address_2'],
                    'city'       => $_POST['_billing_details']['_billing_city'],
                    'state'      => $_POST['_billing_details']['_billing_state'],
                    'postcode'   => $_POST['_billing_details']['_billing_postcode'],
                    'phone'     => $_POST['_billing_details']['_billing_phone'],
                    'country'    => "NZ"
                );

                $order = wc_create_order();
                $args = [];
                foreach ($_POST['cart'] as $c) {
                    $id = empty($c['variation_id']) ? $c['product_id'] : $c['variation_id'];
                    $product = get_product( $id );
                    $price = ($c['item_type'] == 'Per Kg.') ?  ($c['price']*$c['weight']) : $c['price'];
                    $product->set_price($price);
                    $item_id = $order->add_product( $product, $c['qty'], [] );
                    $args[$item_id] = [
                        'item_type' => $c['item_type'],
                        'description' => $c['item_description'],
                        'weight' => $c['weight']
                    ];
                }
                $order->set_address( $address, 'billing' );
                $order->set_address( $address, 'shipping' );
                foreach($order->get_items() as $item) {
                    wc_add_order_item_meta( $item->get_id(), 'Item Type', $args[$item->get_id()]['item_type'] );
                    wc_add_order_item_meta( $item->get_id(), 'Weight', $args[$item->get_id()]['weight'] );
                    wc_add_order_item_meta( $item->get_id(), 'Description', $args[$item->get_id()]['description'] );
                }
                update_post_meta($order->get_id(), '_order_preparation_time', date("Y-m-d H:i:s", strtotime($_POST['_billing_details']['preparation_time'])));
                if($_POST['select_store'] == '0'){
                    $store_address = "edgeware"; 
                }else{
                    $store_address = $_POST['select_store'];
                }
                update_post_meta($order->get_id(), '_store_collection', $store_address);
                update_post_meta($order->get_id(), '_phone_order', "true");

                if($_POST['fee'] > 0) {
                    $calculate_tax_for = array(
                        'country' => "NZ", 
                        'state' => $_POST['_billing_details']['_billing_state'],
                        'postcode' => $_POST['_billing_details']['_billing_postcode'],
                        'city' => $_POST['_billing_details']['_billing_city'],
                    );
                    $item_fee = new WC_Order_Item_Fee();
                    $item_fee->set_name( "Fee" );
                    $item_fee->set_amount( $_POST['fee'] );
                    $item_fee->set_tax_class( '' );
                    $item_fee->set_tax_status( 'taxable' );
                    $item_fee->set_total( $_POST['fee'] );

                    // Calculating Fee taxes
                    $item_fee->calculate_taxes( $calculate_tax_for );                
                }

                // Add Fee item to the order
                $order->add_item( $item_fee );
                if(!empty($_POST['private_note'])) {
                    $order->add_order_note($_POST['private_note']);
                }
                if(!empty($_POST['customer_note'])) {
                    $order->set_customer_note($_POST['customer_note']);
                }

                $total = $order->calculate_totals();
                $this_order = new WC_Order($order->get_id());
                $this_order->set_total($total);
                $this_order->set_status('processing');
                $this_order->save();
                $return = [
                    "success" => true,
                    "order_id" => $order->get_id()
                ];

            }else{
                $order_id = $_POST['order_id'];

                // $address = array(
                //     'first_name' => $_POST['_billing_details']['_billing_first_name'],
                //     'last_name'  => $_POST['_billing_details']['_billing_last_name'],
                //     'email'      => $_POST['_billing_details']['_billing_email'],
                //     'address_1'  => $_POST['_billing_details']['_billing_address_1'],
                //     'address_2'  => $_POST['_billing_details']['_billing_address_2'],
                //     'city'       => $_POST['_billing_details']['_billing_city'],
                //     'state'      => $_POST['_billing_details']['_billing_state'],
                //     'postcode'   => $_POST['_billing_details']['_billing_postcode'],
                //     'phone'     => $_POST['_billing_details']['_billing_phone'],
                //     'country'    => "NZ"
                // );

                $order = wc_update_order(array ( 'order_id' => $order_id ));
                $args = [];
                foreach($order->get_items() as $item) {
                    wc_delete_order_item( $item->get_id() );
                }
                foreach ($_POST['cart'] as $c) {
                    $id = empty($c['variation_id']) ? $c['product_id'] : $c['variation_id'];
                    $product = get_product( $id );
                    $price = ($c['item_type'] == 'Per Kg.') ?  ($c['price']*$c['weight']) : $c['price'];
                    $product->set_price($price);
                    $item_id = $order->add_product( $product, $c['qty'], [] );
                    $args[$item_id] = [
                        'item_type' => $c['item_type'],
                        'description' => $c['item_description'],
                        'weight' => $c['weight']
                    ];
                }

                // $order->set_address( $address, 'billing' );
                // $order->set_address( $address, 'shipping' );
                
                foreach($order->get_items() as $item) {
                    wc_add_order_item_meta( $item->get_id(), 'Item Type', $args[$item->get_id()]['item_type'] );
                    wc_add_order_item_meta( $item->get_id(), 'Weight', $args[$item->get_id()]['weight'] );
                    wc_add_order_item_meta( $item->get_id(), 'Description', $args[$item->get_id()]['description'] );
                }

                update_post_meta($order->get_id(), '_order_preparation_time', date("Y-m-d H:i:s", strtotime($_POST['_billing_details']['preparation_time'])));


                if($_POST['select_store'] == '0'){
                    $store_address = "edgeware"; 
                }else{
                    $store_address = $_POST['select_store'];
                }
                update_post_meta($order->get_id(), '_store_collection', $store_address);

                if($_POST['fee'] > 0) {

                    $calculate_tax_for = array(
                        'country' => "NZ", 
                        'state' => $_POST['_billing_details']['_billing_state'],
                        'postcode' => $_POST['_billing_details']['_billing_postcode'],
                        'city' => $_POST['_billing_details']['_billing_city'],
                    );
                    $fee_items  = $order->get_fees();
                    if(empty($fee_items)){
                        $item_fee = new WC_Order_Item_Fee();
                        $item_fee->set_name( "Fee" );
                        $item_fee->set_amount( $_POST['fee'] );
                        $item_fee->set_tax_class( '' );
                        $item_fee->set_tax_status( 'taxable' );
                        $item_fee->set_total( $_POST['fee'] );

                        // Calculating Fee taxes
                        $item_fee->calculate_taxes( $calculate_tax_for );  
                        $this_order = wc_get_order( $order->get_id() );
                        $this_order->add_item( $item_fee ); 
                        $this_order->calculate_totals();
                        
                    }else{
                        foreach( $fee_items as $item_id => $item ) {
                            if( "Fee" === $item->get_name() ) {
                                wc_update_order_item_meta( $item_id, "_fee_amount", $_POST['fee'] );
                                wc_update_order_item_meta( $item_id, "_line_total", $_POST['fee'] );
                            }
                        }
                    }
                }
                
                $order = wc_get_order( $order->get_id() );
                $order->calculate_totals();
                // $order->save();

                $return = [
                    "success" => true,
                    "edit" => true,
                    "order_id" => $order_id." Update Successfully.",
                    // "item" => $fee_items,
                ];
            }

            echo json_encode($return);
            wp_die();
        }
    }
}
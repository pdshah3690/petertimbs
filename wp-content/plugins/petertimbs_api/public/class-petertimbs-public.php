<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Petertimbs
 * @subpackage Petertimbs/public
 * @author     FeatherTechlabs <dev@FeatherTechlabs.com>
 */

class Petertimbs_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function auth_handler() {

        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-auth.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $auth_obj = new Petertimbs_Auth;

        register_rest_route('api/v1', '/verify-email', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "verify_email"],
        ]);

        register_rest_route('api/v1', '/send-otp', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "send_otp"],
        ]);

        register_rest_route('api/v1', '/auth', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "login"],
        ]);

        register_rest_route('api/v1', '/singup', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "singup"],
        ]);

        register_rest_route('api/v1', '/verify-otp', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "verify_otp"],
        ]);

        register_rest_route('api/v1', '/forgot-password', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "forgot_password"],
        ]);

        register_rest_route('api/v1', '/profile', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$auth_obj, "get_profile"],
        ]);

        register_rest_route('api/v1', '/get-address', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$auth_obj, "get_address"],
        ]);

        register_rest_route('api/v1', '/profile', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "update_profile"],
        ]);

        register_rest_route('api/v1', '/update-phone', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "update_phone"],
        ]);

        register_rest_route('api/v1', '/notification/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$auth_obj, "notification_index"],
        ]);

        register_rest_route('api/v1', '/notification/seen', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "notification_seen"],
        ]);

        register_rest_route('api/v1', '/delete-account', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "delete_account"],
        ]);

        register_rest_route('api/v1', '/guest-login', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$auth_obj, "guest_login"],
        ]);
    }

    public function general_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-general.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $general_obj = new Petertimbs_General;

        register_rest_route('api/v1', '/home', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$general_obj, "home"],
        ]);

        register_rest_route('api/v1', '/home/search', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$general_obj, "search"],
        ]);

        register_rest_route('api/v1', '/cart/view', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$general_obj, "view_cart"],
        ]);
    }

    public function product_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-product.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $product_obj = new Petertimbs_Product;

        register_rest_route('api/v1', '/products/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_obj, "index"],
        ]);

        register_rest_route('api/v1', '/products/filters', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_obj, "filters"],
        ]);

        register_rest_route('api/v1', '/products/(?P<id>\d+)/view', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_obj, "view"],
        ]);
    }

    public function recipe_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-recipe.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $recipe_obj = new Petertimbs_Recipe;

        register_rest_route('api/v1', '/recipes/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$recipe_obj, "index"],
        ]);

        register_rest_route('api/v1', '/recipes/filters', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$recipe_obj, "filters"],
        ]);

        register_rest_route('api/v1', '/recipes/(?P<id>\d+)/view', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$recipe_obj, "view"],
        ]);
    }

    public function order_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-order.php');
        $order_obj = new Petertimbs_Order;

        register_rest_route('api/v1', '/orders/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$order_obj, "index"],
        ]);

        register_rest_route('api/v1', '/orders/create', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$order_obj, "create"],
        ]);

        register_rest_route('api/v1', '/orders/source', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$order_obj, "_get_stripe_source"],
        ]);

        register_rest_route('api/v1', '/orders/(?P<id>\d+)/view', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$order_obj, "view"],
        ]);

        register_rest_route('api/v1', '/orders/(?P<id>\d+)/repeat', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$order_obj, "repeat"],
        ]);
    }

    public function card_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-card.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $card_obj = new Petertimbs_Card;

        register_rest_route('api/v1', '/cards/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$card_obj, "index"],
        ]);

        register_rest_route('api/v1', '/cards/create', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$card_obj, "create"],
        ]);

        register_rest_route('api/v1', '/cards/delete', [
            "permission_callback" => "__return_true",
            "methods" => "POST",
            "callback" => [$card_obj, "delete"],
        ]);
    }

    public function product_meals_handler() {
        require_once(dirname( __FILE__ ).'/classes/class-petertimbs-product-meals.php');
        require_once(dirname( __FILE__ ).'/classes/jwt/JWT.php');
        $product_meals_obj = new Petertimbs_Product_Meals;

        register_rest_route('api/v1', '/meals/index', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_meals_obj, "index"],
        ]);

        register_rest_route('api/v1', '/meals/filters', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_meals_obj, "filters"],
        ]);

        register_rest_route('api/v1', '/meals/(?P<id>\d+)/view', [
            "permission_callback" => "__return_true",
            "methods" => "GET",
            "callback" => [$product_meals_obj, "view"],
        ]);
    }
}

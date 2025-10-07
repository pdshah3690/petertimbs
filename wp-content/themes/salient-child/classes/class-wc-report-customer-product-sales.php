<?php
/**
 * Class WC_Report_Customer_List file.
 *
 * @package WooCommerce\Reports
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WC_Report_Customer_List.
 *
 * @package     WooCommerce/Admin/Reports
 * @version     2.1.0
 */
class WC_Report_Customer_Product_Sales {

    private $query;
    public function __construct() {
        $this->_sql();
    }

    private function _sql() {
        global $wpdb;
        $range = $this->_get_request_date_range();
        $order_type = empty($_GET['order_type']) ? 'all' : $_GET['order_type'];
        $store = empty($_GET['select_store']) ? 'both' : $_GET['select_store'];

        $this->query = "SELECT pm.meta_value as preparation_time, p.post_status, p.ID as order_id, woi.order_item_name, woim.meta_key, woim.meta_value, woim.order_item_id FROM {$wpdb->posts} as p ";

        if($order_type == "pick_up") {
            $this->query .= " INNER JOIN {$wpdb->postmeta} as pm
                                ON p.ID = pm.post_id AND pm.meta_key = '_order_preparation_time' ";
        }
        if($order_type == "delivery") {
            $this->query .= " INNER JOIN {$wpdb->postmeta} as pm
                                ON p.ID = pm.post_id AND pm.meta_key = '_order_delivery_date' ";
        }
        if($order_type == "all") {
            $this->query .= " INNER JOIN {$wpdb->postmeta} as pm
                                ON p.ID = pm.post_id AND (pm.meta_key = '_order_preparation_time' OR pm.meta_key = '_order_delivery_date') ";
        }
        if($store == "edgeware") {
            $this->query .= " INNER JOIN {$wpdb->postmeta} as pom
                                ON p.ID = pom.post_id AND (pom.meta_key = '_store_collection' AND pom.meta_value = 'edgeware') ";
        }
        if($store == "bishopdale") {
            $this->query .= " INNER JOIN {$wpdb->postmeta} as pom
                                ON p.ID = pom.post_id AND (pom.meta_key = '_store_collection' AND pom.meta_value = 'bishopdale') ";
        }
        $this->query .= " INNER JOIN {$wpdb->prefix}woocommerce_order_items as woi
                            ON p.ID = woi.order_id
                        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woim
                            ON woi.order_item_id = woim.order_item_id AND (woim.meta_key = '_product_id' OR woim.meta_key = '_variation_id' OR woim.meta_key = '_qty')
                        WHERE p.post_status = 'wc-processing' AND p.post_type = 'shop_order' ";
        if(!empty($range[0])) {
            $this->query .= " AND str_to_date(pm.meta_value, '%Y-%m-%d %H:%i:%s') >= '{$range[0]}' AND str_to_date(pm.meta_value, '%Y-%m-%d %H:%i:%s') <= '{$range[1]}' ";
        }
        $this->query .= "order by order_id DESC LIMIT 0, 10000";
    }

    private function _get_request_date_range() {
        date_default_timezone_set('Pacific/Auckland');
        $start_date = $end_date = "";
        if($_GET['range'] === "year") {
            $start_date = date("Y-01-01");
            $end_date = date("Y-12-31");
        }
        if($_GET['range'] === "last_month") {
            $start_date = date("Y-m-d", strtotime("first day of previous month"));
            $end_date = date("Y-m-d", strtotime("last day of previous month"));
        }
        if($_GET['range'] === "month") {
            $start_date = date("Y-m-d", strtotime("first day of this month"));
            $end_date = date("Y-m-d", strtotime("last day of this month"));
        }
        if($_GET['range'] === "7day") {
            $start_date = date("Y-m-d", strtotime("-6days"));
            $end_date = date("Y-m-d");
        }
        if($_GET['range'] === "lastday") {
            $start_date = date("Y-m-d 00:00:00", strtotime("yesterday"));
            $end_date = date("Y-m-d 23:59:59", strtotime("yesterday"));
        }
        if($_GET['range'] === "today") {
            $start_date = date("Y-m-d 00:00:00");
            $end_date = date("Y-m-d 23:59:59");
        }
        if($_GET['range'] === "tomorrow") {
            $start_date = date("Y-m-d 00:00:00", strtotime("tomorrow"));
            $end_date = date("Y-m-d 23:59:59", strtotime("tomorrow"));
        }
        if($_GET['range'] === "custom") {
            $start_date = date("Y-m-d 00:00:00", strtotime($_GET['start_date']));
            $end_date = date("Y-m-d 23:59:59", strtotime($_GET['end_date']));
        }
        return [$start_date, $end_date];
    }

    public function output_report() {
        global $wpdb;
        $orders = $wpdb->get_results($this->query, ARRAY_A);
        $orders = $this->_format_order_details($orders);
        $this->_render_custom_report_table($orders);
    }

    private function _format_order_details($orders) {
        $return = $formatArray = [];
        foreach ($orders as $o) {
            $formatArray[$o['order_id']][$o['order_item_id']]['order_id'] = $o['order_id'];
            $formatArray[$o['order_id']][$o['order_item_id']]['post_status'] = $o['post_status'];
            $formatArray[$o['order_id']][$o['order_item_id']]['preparation_time'] = $o['preparation_time'];
            $formatArray[$o['order_id']][$o['order_item_id']][$o['meta_key']] = $o['meta_value'];
            $formatArray[$o['order_id']][$o['order_item_id']]['product_name'] = $o['order_item_name'];
        }

        foreach ($formatArray as $key => $value) {
            $user_id = get_post_meta($key, "_customer_user", true);
            if (empty($user_id)) {
                $user_name = get_post_meta($key, "_billing_first_name", true)." ".get_post_meta($key, "_billing_last_name", true);
            } else {
                $user = get_user_by("ID", $user_id);
                $user_name = $user->display_name;
            }
            foreach ($value as $key => $v) {
                $description = wc_get_order_item_meta($key, "Description", true);
                $prep_time = get_post_meta($v['_product_id'], "preparation_time", true);
                $weight = get_post_meta($v['_product_id'], "_weight", true);
                $sku = get_post_meta($v['_product_id'], "_sku", true);
                if(!empty($v['_variation_id'])) {
                    $prep_time = get_post_meta($v['_variation_id'], "preparation_time", true);
                    $weight = get_post_meta($v['_variation_id'], "_weight", true);
                    $sku = get_post_meta($v['_variation_id'], "_sku", true);
                }
                
                $weight = $weight * $v['_qty'];
                $prep_time = $this->format_prep_time($prep_time, $v['_qty']);
                
                $type = get_post_meta($v['order_id'], "_order_type", 1);
                $type = empty($type) ? 'Pick Up' : ucfirst(str_replace("_", " ", $type));

                $store = get_post_meta($v['order_id'], "_store_collection", 1);
                $store = empty($store) ? 'Edgeware' : ucfirst($store) ;
                
                $get_order = new WC_Order($v['order_id']);
                $order_date = $get_order->order_date;
                $order_total = $get_order->get_total();
                
                $is_order = get_post_meta($v['order_id'], "_is_order", 1);

                $return[] = [
                    "order_id"      => $v['order_id'],
                    "status"        => $v['post_status'],
                    "type"          => $type,
                    "preparation_time" => $v['preparation_time'],
                    "sku"           => $sku,
                    "customer_name" => $user_name,
                    "product_name"  => $v['product_name'],
                    "qty"           => $v['_qty'],
                    "time"          => $prep_time,
                    "weight"        => $weight,
                    "store"        => $store,
                    "order_date"    => $order_date,
                    "order_total"   => $order_total,
                    "description"   => $description,
                    "is_order"      => $is_order,
                ];
            }
        }
        return $return;
    }

    private function _render_custom_report_table($data) {
        require_once(get_stylesheet_directory().'/partials/admin/customer-product-sales-report.php');
    }

    private function format_prep_time($time, $qty) {
        $time = $time * $qty;
        $time_html = $time." Min";
        if($time > 60) {
            $hours = floor($time / 60);
            $min = $time - ($hours * 60);
            $time_html = $hours." Hr ".$min. " Min";
        }
        return $time_html;
    }
}

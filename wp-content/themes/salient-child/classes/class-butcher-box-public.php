<?php 
defined( 'ABSPATH' ) || exit;

Class Butcher_Box_Public {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'butcherbox_public_script']);
        add_action("woocommerce_single_butcherbox_product_summary", "woocommerce_template_single_title", 5);
        add_action("woocommerce_single_butcherbox_product_summary", "woocommerce_template_single_price", 10);
        add_action("woocommerce_single_butcherbox_product_summary", "woocommerce_template_single_excerpt", 20);
        add_action("woocommerce_single_butcherbox_product_summary", [$this, "get_butcherbox_product_variations"], 30);
        add_action("woocommerce_single_bb_product_items", [$this, "get_single_bb_product_items"]);

        add_action('woocommerce_new_order_item', [$this, 'save_bb_items_meta'], 10, 2);
        add_action('woocommerce_after_checkout_validation', [$this, 'butcher_box_items_qty_handler'], 100, 2);

        add_filter('woocommerce_display_item_meta', [$this, 'display_bb_items'], 10, 3);
        $this->butcherbox_ajax_handler();
    }

    private function butcherbox_ajax_handler() {
        add_action('wp_ajax_add_products_to_butcherbox', [$this, 'add_products_to_butcherbox']);
        add_action('wp_ajax_nopriv_add_products_to_butcherbox', [$this, 'add_products_to_butcherbox']);
        add_action('wp_ajax_get_bb_added_products', [$this, 'get_bb_added_products']);
        add_action('wp_ajax_nopriv_get_bb_added_products', [$this, 'get_bb_added_products']);

        add_action('wp_ajax_get_product_variations', [$this, 'get_product_variations']);
        add_action('wp_ajax_nopriv_get_product_variations', [$this, 'get_product_variations']);
    }

    public function get_butcherbox_product_variations() {
        global $post;
        $butcherbox_products = $this->_get_butcherbox_available_products();
        include_once(STYLESHEETPATH.'/partials/single-butcherbox-products-list.php');
    }

    private function _get_butcherbox_available_products() {
        global $wpdb;
        $ids = get_hidden_product_ids();
        $ids = implode(", ", $ids);
        $sql = "SELECT p.ID
                    FROM {$wpdb->prefix}posts as p
                    INNER JOIN {$wpdb->prefix}postmeta as pm
                        ON p.ID = pm.post_id AND (pm.meta_key = 'is_butcherbox_product' AND pm.meta_value = 1)
                    WHERE p.post_type = 'product' AND p.post_status = 'publish' AND p.ID NOT IN ({$ids})
                UNION 
                SELECT p2.ID
                    FROM {$wpdb->prefix}posts as p
                    INNER JOIN {$wpdb->prefix}postmeta as pm
                        ON p.ID = pm.post_id AND (pm.meta_key = 'is_butcherbox_product' AND pm.meta_value = 1)
                    INNER JOIN {$wpdb->prefix}posts as p2
                        ON p2.ID = p.post_parent AND p2.post_status = 'publish'
                    WHERE p.post_type = 'product_variation' AND p.post_status = 'publish'  AND p2.ID NOT IN ({$ids}) GROUP BY p2.ID";

        $data = $wpdb->get_results($sql, ARRAY_A);
        $butcherbox_products = [];
        foreach ($data as $d) {
            $product_id = empty($d['post_parent']) ? $d['ID'] : $d['post_parent'];
            $product = wc_get_product($d['ID']);
            $butcherbox_products[$product_id] = [
                "product_id" => $product_id,
                "name"  => $product->get_title(),
                "is_variable" => $product->get_type() === 'variable' ? 1 : 0
            ];
        }
        return $butcherbox_products;
    }

    public function add_products_to_butcherbox() {
        $cart_item = ["bb_items" => serialize($_POST['cart_item'])];
        $cart = WC()->cart->get_cart();
        if(!empty($cart)) {
            foreach ($cart as $item) {
                if($item['product_id'] == $_POST['product_id']) {
                    WC()->cart->remove_cart_item($item['key']);
                }
            }
        }
        if(!empty($_POST['cart_item'])) {
            WC()->cart->add_to_cart($_POST['product_id'], 1, $_POST['variation_id'], [], $cart_item );
        }
        WC_AJAX::get_refreshed_fragments();
        wp_die();
    }

    public function butcherbox_public_script() {
        wp_enqueue_script('bb-public', get_stylesheet_directory_uri()."/js/butcher-box-public.js", array("select2"), null, true);
    }

    public function get_bb_added_products() {
        $html = "";
        foreach ($_POST['cart_item'] as $item) {
            if(empty($item['product_id']) || empty($item['is_render'])) {
                continue;
            }
            $url = get_permalink($item['product_id']);
            $product = wc_get_product($item['product_id']);
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'woocommerce_gallery_thumbnail' );

            $v = new WC_Product_Variation($item['variation_id']);
            $variation = $v->get_attributes();
            if(!empty($variation)) {
                $url = get_permalink($item['variation_id']);
            }
            $variation = implode(array_values($variation), ", ");
            $variation = empty($variation) ? "" : " - ".$variation;

            $html .= '<div id="bb_product_'.$product->get_id()."_".$item['variation_id'].'" class="box-item-container bb_item" data-variation_id="'.$item['variation_id'].'" data-product_id="'.$product->get_id().'">
                <div class="details-container">
                    <div class="remove-div details-div">
                        <a href="javascript:void(0);" class="remove-bb-item remove" aria-label="Remove this item" data-product_id="'.$product->get_id().'" data-variation_id="'.$item['variation_id'].'">×</a>
                    </div>
                    <div class="img-div details-div">
                        <a href="javascript:void(0);">
                            <img width="300" height="300" src="'.$image[0].'" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                        </a>
                    </div>
                    <div class="qty-div details-div cart">
                        <div class="quantity buttons_added">
                            <input type="button" value="-" class="minus" data-product_id="'.$product->get_id().'" data-variation_id="'.$item['variation_id'].'">
                            <input type="number" id="quantity" class="bb_qty_text input-text qty text" step="1" min="0" max="" value="'.$item['qty'].'" title="Qty" size="4" inputmode="numeric">
                            <input type="button" value="+" class="plus" data-product_id="'.$product->get_id().'" data-variation_id="'.$item['variation_id'].'">
                        </div>
                    </div>
                </div>
                <div class="product-title-div">
                    <a href="'.$url.'" target="_blank">'.$product->get_title(). " ". $variation.'</a>
                </div>
            </div>';
        }
        die($html);
    }

    public function get_single_bb_product_items()
    {
        wc_get_template_part( 'bb', 'product-items-grid' );
    }

    public function get_product_variations()
    {
        global $wpdb;

        $ids = get_hidden_product_ids();
        $ids = implode(", ", $ids);

        $sql = "SELECT p.ID FROM {$wpdb->prefix}postmeta as pm
                    INNER JOIN {$wpdb->prefix}posts as p
                        ON pm.post_id = p.ID AND (pm.meta_key = 'is_butcherbox_product' AND pm.meta_value = 1)
                    WHERE p.post_parent = {$_POST['product_id']} AND p.post_status = 'publish' AND p.post_type = 'product_variation' AND p.ID NOT IN ($ids) LIMIT 0,30";

        $data = $wpdb->get_results($sql, ARRAY_A);

        $return = [];
        for ($i=0; $i < count($data); $i++) {
            $v = new WC_Product_Variation($data[$i]['ID']);
            $variation = $v->get_attributes();
            $attributes = array_keys($variation);
            $variations = array_values($variation);

            $return['attr_0'] = ucfirst($attributes[0]);
            $return['variations'][$variations[0]][] = [
                "variation_id" => $data[$i]['ID'],
                "attr_1" => ucfirst($attributes[1]),
                "variation" => $variations[1]
            ];
        }
        echo json_encode($return);
        die();
    }

    public function save_bb_items_meta($item_id, $cart_item) {
        if(!empty($cart_item['bb_items'])) {
            woocommerce_new_order_item($item_id, 'bb_items', $cart_item['bb_items']);
        }
    }

    public function butcher_box_items_qty_handler($fields, $errors) {
        $cart = WC()->cart->get_cart();
        foreach ($cart as $key => $item) {
            $category = get_the_terms($item['product_id'], "product_cat");
            if(!empty($category) && $category[0]->slug == "butcher-box") {
                $variation_id = $item['variation_id'];
                $v = new WC_Product_Variation($variation_id);
                $variation = $v->get_attributes();
                $box_size = array_values($variation)[0];
                $bb_items = unserialize($item['bb_items']);
                $this->check_box_complete($bb_items, $box_size, $errors);
            }
        }
    }

    private function check_box_complete($bb_items, $box_size, $errors) {
        $box_qty = 0;
        foreach ($bb_items as $p) {
            $box_qty = $box_qty + $p['qty'];
        }
        if($box_qty < $box_size) {
            $errors->add( 'validation', 'Your buthcer box is incomplete!' );
        }
    }

    public function display_bb_items($html, $item, $args) {
        foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
            if($meta->key === "bb_items") {
                $html = "<div>";
                $html .= "<div><span><strong>Box Items :</strong></span></div>";
                $bb_items = unserialize($meta->value);
                foreach ($bb_items as $i) {
                    $variation = "";
                    if(!empty($i['variation_id'])) {
                        $v = new WC_Product_Variation($i['variation_id']);
                        $variation = $v->get_attributes();
                        $variation = implode(array_values($variation), ", ");
                        $variation = empty($variation) ? "" : " - ".$variation;
                    }

                    $html .= "<div>";
                    $product = wc_get_product($i['product_id']);
                    $html .= "<span class='first'>".$product->get_title().$variation." - </span>";
                    $html .= "<span class='second'>".$i['qty']."</span>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            }
        }
        return $html;
    }
}

$butcher_box_public = new Butcher_Box_Public();

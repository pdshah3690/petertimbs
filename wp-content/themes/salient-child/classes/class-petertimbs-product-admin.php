<?php 
defined( 'ABSPATH' ) || exit;

Class Petertimbs_Product_Admin {
    
    public function __construct() {
        $this->add_prepare_time_tab();
    }

    public function add_prepare_time_tab() {
        add_filter( 'woocommerce_product_data_tabs', [$this, 'preparetime_woocommerce_data_tab'], 10);
        add_action( 'woocommerce_product_data_panels', [$this, 'preparetime_woocommerce_panel']);
        add_action( 'woocommerce_process_product_meta', [$this, 'product_save_meta'] );

        add_action( 'admin_enqueue_scripts', [$this, 'preparetime_admin_scripts']);

        add_action( 'wp_enqueue_scripts', [$this, 'enabling_date_picker'] );

        add_action( 'wp_ajax_get_product_prepare_time', [$this, 'get_product_prepare_time']);
        add_action( 'wp_ajax_get_site_product', [$this, 'get_site_product']);
        add_action( 'wp_ajax_get_app_product', [$this, 'get_app_product']);
        add_action( 'wp_ajax_get_product_prepare_time', [$this, 'get_product_prepare_time']);
        add_action( 'wp_ajax_get_shipping_type', [$this, 'get_shipping_type']);
        add_action( 'wp_ajax_get_date_product', [$this, 'get_date_product']);

        add_action( 'wp_ajax_get_product_free_delivery', [$this, 'get_product_free_delivery']);
        add_action( 'wp_ajax_get_butchers_kitchen_img', [$this, 'get_butchers_kitchen_img']);
    }

    public function preparetime_admin_scripts() {

        wp_enqueue_script( 'prepare-time-admin', get_stylesheet_directory_uri()."/js/prepare-time-admin.js", array(), null, true );
    }

    public function preparetime_woocommerce_data_tab($tabs) {
        $tabs['prepare-time-data-tab'] = [
            'label'         => 'Preparation Time',
            'target'        => 'prepare-time-data-tab',
            'class'         => ['prepare-time-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 90,
        ];
        $tabs['site-product-data-tab'] = [
            'label'         => 'Hide On Site',
            'target'        => 'site-product-data-tab',
            'class'         => ['site-product-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 90,
        ];
        $tabs['app-product-data-tab'] = [
            'label'         => 'Hide On App',
            'target'        => 'app-product-data-tab',
            'class'         => ['app-product-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 90,
        ];
        $tabs['shipping-type-data-tab'] = [
            'label'         => 'Shipping Type',
            'target'        => 'shipping-type-data-tab',
            'class'         => ['shipping-type-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 90,
        ];
        $tabs['date-product-data-tab'] = [
            'label'         => 'Schedule Date',
            'target'        => 'date-product-data-tab',
            'class'         => ['date-product-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 90,
        ];
        $tabs['product-free-delivery-tab'] = [
            'label'    => 'Product Free Delivery',
            'target'   => 'product-free-delivery-tab',
            'class'    => ['date-product-free-delivery-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority' => 90,
        ];
        $tabs['butchers-kitchen-img-tab'] = [
            'label'    => 'Butchers Kitchen Image',
            'target'   => 'butchers-kitchen-img-tab',
            'class'    => ['date-butchers-kitchen-img-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority' => 90,
        ];
        return $tabs;
    }

    public function preparetime_woocommerce_panel() {
        global $post;
        $product = wc_get_product($post->ID);
        echo "<div id='prepare-time-data-tab' class='panel woocommerce_options_panel'>
                    <div class='prepare-time-container'></div>
                </div>
                <div id='site-product-data-tab' class='panel woocommerce_options_panel'>
                    <div class='site-product-container'></div>
                </div>
                <div id='app-product-data-tab' class='panel woocommerce_options_panel'>
                    <div class='app-product-container'></div>
                </div>
                <div id='shipping-type-data-tab' class='panel woocommerce_options_panel'>
                    <div class='shipping-type-container'></div>
                </div>
                <div id='date-product-data-tab' class='panel woocommerce_options_panel'>
                    <div class='date-product-container'></div>
                </div>
                <div id='product-free-delivery-tab' class='panel woocommerce_options_panel'>
                    <div class='product-free-delivery-container'></div>
                </div>
                <div id='butchers-kitchen-img-tab' class='panel woocommerce_options_panel'>
                    <div class='butchers-kitchen-img-container'></div>
                </div>";
    }

    public function product_save_meta($product_id) {
        $product = wc_get_product($product_id);
        $butchers_kitchen_img = empty($_POST['butchers_kitchen_img']) ? "" : $_POST['butchers_kitchen_img'];
        update_option("butchers_kitchen_img", $butchers_kitchen_img);
        if($product->get_type() === "variable") {
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product_id
            );
            $posts = get_posts( $args );
            update_post_meta( $product_id, "date_product_from", $_POST['date_product_from']);
            update_post_meta( $product_id, "date_product_to", $_POST['date_product_to']);
            update_post_meta($product_id, "_hidden_on_app", 1);
            update_post_meta($product_id, "_hidden_on_site", 1);


            foreach($posts as $k => $p) {
                $bool = empty($_POST['_hidden_on_site'][$p->ID]) ? 0 : 1;
                if(!$bool) {
                    update_post_meta($product_id, "_hidden_on_site", 0);
                }
                update_post_meta($p->ID, "_hidden_on_site", $bool);
                
                $bool = empty($_POST['_hidden_on_app'][$p->ID]) ? 0 : 1;
                if(!$bool) {
                    update_post_meta($product_id, "_hidden_on_app", 0);
                }
                update_post_meta($p->ID, "_hidden_on_app", $bool);

                $type = empty($_POST['_shipping_type'][$p->ID]) ? "both" : $_POST['_shipping_type'][$p->ID];
                update_post_meta($p->ID, "_shipping_type", $type);

                $free_delivery = empty($_POST['product_free_delivery'][$p->ID]) ? "" : $_POST['product_free_delivery'][$p->ID];
                update_post_meta($p->ID, "product_free_delivery", $free_delivery);
            }
            foreach($_POST['preparation_time'] as $k => $p) {
                update_post_meta($k, "preparation_time", $p);
            }
        } else {
            update_post_meta($product_id, "preparation_time", $_POST['preparation_time']);
            $bool = empty($_POST['_hidden_on_site']) ? 0 : 1;
            update_post_meta($product_id, "_hidden_on_site", $bool);
            $bool = empty($_POST['_hidden_on_app']) ? 0 : 1;
            update_post_meta($product_id, "_hidden_on_app", $bool);
            update_post_meta($product_id, "_shipping_type", $_POST['_shipping_type']);
            update_post_meta( $product_id, "date_product_from", $_POST['date_product_from']);
            update_post_meta( $product_id, "date_product_to", $_POST['date_product_to']);
            $free_delivery = empty($_POST['product_free_delivery']) ? "" : $_POST['product_free_delivery'];
            update_post_meta( $product_id, "product_free_delivery", $free_delivery);
        }
    }

    function enabling_date_picker() {
        if(!is_admin()) return;
        wp_enqueue_script( 'jquery-ui-datepicker' );
    }

    function ncydesign_datepicker_field($date_product_from, $date_product_to) {

        date_default_timezone_set('Pacific/Auckland');
        ?>
        <p>
            <span>Schedule Date</span>
        </p>
        <p>
            <span>From:</span><br>
            <input type="text" id="date_product_from" name="date_product_from" value="<?php echo $date_product_from; ?>">
        </p>
        <p>
            <span>To:</span><br>
            <input type="text" id="date_product_to" name="date_product_to" value="<?php echo $date_product_to; ?>">
        </p>
        <?php
    }

    public function get_product_prepare_time() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_preparetime_html($product);
            echo $html;
            die();
        }
    }

    public function get_site_product() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_site_product_html($product);
            echo $html;
            die();
        }
    }

    public function get_app_product() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_app_product_html($product);
            echo $html;
            die();
        }
    }

    public function get_shipping_type() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_shipping_type_html($product);
            echo $html;
            die();
        }
    }

    public function get_date_product() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_date_product_html($product);
            echo $html;
            die();
        }
    }

    public function get_product_free_delivery() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_product_free_delivery_html($product);
            echo $html;
            die();
        }
    }

    public function get_butchers_kitchen_img() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_butchers_kitchen_img_html($product);
            echo $html;
            die();
        }
    }

    private function _render_date_product_html($product)
    {
        $html = "";
        $date_product_from = get_post_meta($product->get_ID(), "date_product_from", true);
        $date_product_to = get_post_meta($product->get_ID(), "date_product_to", true);
        $html = "<div class='date-product-input'>
                    <p class='simple-product'>
                       ".$this->ncydesign_datepicker_field($date_product_from, $date_product_to)."
                    </p>
                </div>";
        return $html;            
    }

    private function _render_preparetime_html($product) {
        $html = "";
        if($_POST['type'] === "variable") {
            $variations = [];
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product->get_ID()
            );
            $posts = get_posts( $args );
            if(empty($posts)) {
                echo "Create variations first!";
                die();
            }
            $html .= "<p>Product Prepare Time in minutes</p>";
            foreach ($posts as $p) {
                $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
                $variation = implode(" | ", $variation);
                $preparation_time = get_post_meta($p->ID, "preparation_time", true);

                $html .= "<div class='prepare-time-input'>
                            <p class='variable-product'>
                                <span><strong>".$variation." : </strong></span>
                                    <input type='number' name='preparation_time[".$p->ID."]' value='".$preparation_time."'/>
                            </p>
                        </div>";
            }
        } else {
            $preparation_time = get_post_meta($product->get_ID(), "preparation_time", true);
            $html = "<div class='prepare-time-input'>
                        <p class='simple-product'>
                            <span><b>Prepare time in minutes : </b> </span> <input type='number' name='preparation_time' value='".$preparation_time."'/>
                        </p>
                    </div>";
        }
        return $html;
    }

    private function _render_site_product_html($product) {
        $html = "";
        if($_POST['type'] === "variable") {
            $variations = [];
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product->get_ID()
            );
            $posts = get_posts( $args );
            if(empty($posts)) {
                echo "Create variations first!";
                die();
            }
            $html .= "<p>Select to hide product on site</p>";
            foreach ($posts as $p) {
                $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
                $variation = implode(" | ", $variation);
                $hidden_on_site = get_post_meta($p->ID, "_hidden_on_site", true);
                $attr = !empty($hidden_on_site) ? 'checked="checked"' : '';

                $html .= "<div class='site-product-input'>
                            <p>
                                <label>
                                    <input type='checkbox' name='_hidden_on_site[".$p->ID."]' ".$attr."/> <span><strong>".$variation."</strong></span></label>
                            </p>
                        </div>";
            }
        } else {
            $hidden_on_site = get_post_meta($product->get_ID(), "_hidden_on_site", true);
            $attr = !empty($hidden_on_site) ? 'checked="checked"' : '';
            $html = "<div class='site-product-input'>
                        <p>
                            <label>
                                <input type='checkbox' name='_hidden_on_site' ".$attr."/> <span>Hide Product on Website</span></label>
                        </p>
                    </div>";
        }
        return $html;
    }

    private function _render_app_product_html($product) {
        $html = "";
        if($_POST['type'] === "variable") {
            $variations = [];
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product->get_ID()
            );
            $posts = get_posts( $args );
            if(empty($posts)) {
                echo "Create variations first!";
                die();
            }
            $html .= "<p>Select to hide product on app</p>";
            foreach ($posts as $p) {
                $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
                $variation = implode(" | ", $variation);
                $hidden_on_app = get_post_meta($p->ID, "_hidden_on_app", true);
                $attr = !empty($hidden_on_app) ? 'checked="checked"' : '';

                $html .= "<div class='app-product-input'>
                            <p>
                                <label>
                                    <input type='checkbox' name='_hidden_on_app[".$p->ID."]' ".$attr."/> <span><strong>".$variation."</strong></span></label>
                            </p>
                        </div>";
            }
        } else {
            $hidden_on_app = get_post_meta($product->get_ID(), "_hidden_on_app", true);
            $attr = !empty($hidden_on_app) ? 'checked="checked"' : '';
            $html = "<div class='app-product-input'>
                        <p>
                            <label>
                                <input type='checkbox' name='_hidden_on_app' ".$attr."/> <span>Hide Product on App</span></label>
                        </p>
                    </div>";
        }
        return $html;
    }

    private function _render_shipping_type_html($product) {
        $html = "";
        if($_POST['type'] === "variable") {
            $variations = [];
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product->get_ID()
            );
            $posts = get_posts( $args );
            if(empty($posts)) {
                echo "Create variations first!";
                die();
            }
            $html .= "<p>Select product shipping type: </p>";
            foreach ($posts as $p) {
                $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
                $variation = implode(" | ", $variation);
                $shipping_type = get_post_meta($p->ID, "_shipping_type", true);
                $pick_up = ($shipping_type == "pick_up") ? "selected='selected'" : "";
                $delivery = ($shipping_type == "delivery") ? "selected='selected'" : "";
                $both = ($shipping_type == "both") ? "selected='selected'" : "";

                $html .= "<div class='shipping-type-input'>
                            <p>
                                <label>
                                    <span><strong>".$variation."</strong></span>
                                    <select name='_shipping_type[".$p->ID."]'>
                                        <option value=''>Select Shipping type</option>
                                        <option value='pick_up' ".$pick_up.">Only Pick Up</option>
                                        <option value='delivery' ".$delivery.">Only Delivery</option>
                                        <option value='both' ".$both.">Both</option>
                                    </select>
                                </label>
                            </p>
                        </div>";
            }
        } else {
            $shipping_type = get_post_meta($product->get_ID(), "_shipping_type", true);
            $pick_up = ($shipping_type == "pick_up") ? "selected='selected'" : "";
            $delivery = ($shipping_type == "delivery") ? "selected='selected'" : "";
            $both = ($shipping_type == "both") ? "selected='selected'" : "";


            $html = "<div class='shipping-type-input'>
                        <p>
                            <label>Shipping type: 
                                <select name='_shipping_type'>
                                    <option value=''>Select Shipping type</option>
                                    <option value='pick_up' ".$pick_up.">Only Pick Up</option>
                                    <option value='delivery' ".$delivery.">Only Delivery</option>
                                    <option value='both' ".$both.">Both</option>
                                </select>
                            </label>
                        </p>
                    </div>";
        }
        return $html;
    }

    private function _render_product_free_delivery_html($product) {
        $html = "";

        if($_POST['type'] === "variable") {
            $variations = [];
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product->get_ID()
            );
            $posts = get_posts( $args );
            if(empty($posts)) {
                echo "Free Delivery!";
                die();
            }
            $html .= "<p>Select to product free delivery</p>";
            foreach ($posts as $p) {
                $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
                $variation = implode(" | ", $variation);
                $product_free_delivery = get_post_meta($p->ID, "product_free_delivery", true);
                $attr = !empty($product_free_delivery) ? 'checked="checked"' : '';
                
                $html .= "<div class='app-product-input'>
                            <p> 
                                <input type='checkbox' name='product_free_delivery[".$p->ID."]' ".$attr."/> <span><strong>".$variation."</strong></span>
                            </p>
                        </div>";
            }
        } else {
            $product_free_delivery = get_post_meta($product->get_ID(), "product_free_delivery", true);
            $attr = !empty($product_free_delivery) ? 'checked="checked"' : '';
            $html = "<div class='app-product-input'>
                        <p>
                            <span><strong>Free Delivery : </strong></span>
                            <input type='checkbox' name='product_free_delivery' ".$attr."/>
                        </p>
                    </div>";
        }
        return $html;
    }

    private function _render_butchers_kitchen_img_html($product) {
         $getImg = get_option( 'butchers_kitchen_img' );
         $logo_image_url = wp_get_attachment_image_url($getImg, "full");
         $html = "";
        if($getImg) {
            $html = '<div class="app-product-input butchers_kitchen_img_div ">
                        <img src="'.$logo_image_url.'" class="uploaded_image" />
                        <input type="hidden" name="butchers_kitchen_img" value="'.$getImg.'" class="featured_image_upload">
                        <input type="button" name="image_upload" value="Upload Image" class="button upload_image_button">
                    </div>';
        } else {
            $html = '<div class="app-product-input butchers_kitchen_img_div">
                        <img src="" class="uploaded_image"/>
                        <input type="hidden" name="butchers_kitchen_img" value="" class="featured_image_upload">
                        <input type="button" name="image_upload" value="Upload Image" class="button upload_image_button">
                    </div>';
        }
        return $html;
    }
}

$product_admin = new Petertimbs_Product_Admin();

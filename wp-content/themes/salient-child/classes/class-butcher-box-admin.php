<?php 
defined( 'ABSPATH' ) || exit;

Class Butcher_Box_Admin {

	public function __construct() {
		$this->add_butcherbox_tab();
	}

    /*
     * Butcher box init
    */
	public function add_butcherbox_tab() {
        add_filter('woocommerce_order_item_display_meta_value', [$this, 'display_bb_items_values'], 10, 2);
        add_filter('woocommerce_order_item_display_meta_key', [$this, 'display_bb_items_key'], 10, 2);
		// add_filter( 'woocommerce_product_data_tabs', [$this, 'butcherbox_woocommerce_data_tab'], 10);

        add_action( 'woocommerce_product_data_panels', [$this, 'butcherbox_woocommerce_panel']);
		add_action( 'woocommerce_process_product_meta', [$this, 'butcher_box_save_fields'] );
		add_action( 'admin_enqueue_scripts', [$this, 'butcher_admin_scripts']);
		add_action( 'wp_ajax_get_product_butcher_box', [$this, 'get_product_butcher_box']);
	}

    /*
     * Create new tab in woocommerce product
    */
	public function butcherbox_woocommerce_data_tab($tabs) {
        $tabs['butcher-box-data-tab'] = [
            'label'         => 'Butcher Box',
            'target'        => 'butcher-box-data-tab',
            'class'         => ['butcher-box-data-tab-label show_if_simple show_if_grouped show_if_variable'],
            'priority'      => 80,
        ];
        return $tabs;
	}

    /*
     * Render html of butcher box data tab panel
    */
	public function butcherbox_woocommerce_panel() {
        global $post;
        $product = wc_get_product($post->ID);
		echo 	"<div id='butcher-box-data-tab' class='panel woocommerce_options_panel'>
            		<div class='butcherbox-container'></div>
        		</div>";
	}

    /*
     * Render butcher-box html of product
    */
	private function _render_butcherbox_html($product) {
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
			$html .= "<p>Display on Butcher Box Page</p>";
            foreach ($posts as $p) {
	            $v = new WC_Product_Variation($p->ID);
                $variation = $v->get_attributes();
                $variation = array_values($variation);
    			$variation = implode(" | ", $variation);
				$is_butcherbox_product = get_post_meta($p->ID, "is_butcherbox_product", true);
				$attr = !empty($is_butcherbox_product) ? 'checked="checked"' : '';

				$html .= "<div class='butcher-box-input'>
							<p>
								<label>
									<input type='checkbox' name='is_butcherbox_product[".$p->ID."]' ".$attr."/> <span><strong>".$variation."</strong></span></label>
							</p>
						</div>";
            }
		} else {
			$is_butcherbox_product = get_post_meta($product->get_ID(), "is_butcherbox_product", true);
			$attr = !empty($is_butcherbox_product) ? 'checked="checked"' : '';
			$html = "<div class='butcher-box-input'>
						<p>
							<label>
								<input type='checkbox' name='is_butcherbox_product' ".$attr."/> <span>Display on Butcher Box Page</span></label>
						</p>
					</div>";
		}
		return $html;
	}

    /*
     * save butcher-box data of product on add / edit.
    */
	public function butcher_box_save_fields($product_id) {
        $product = wc_get_product($product_id);
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
        	foreach($posts as $k => $p) {
        		if(empty($_POST['is_butcherbox_product'][$p->ID])) {
        			update_post_meta($p->ID, "is_butcherbox_product", 0);
        		} else {
	        		update_post_meta($p->ID, "is_butcherbox_product", 1);
        		}
        	}
        } else {
        	if(empty($_POST['is_butcherbox_product'])) {
    			update_post_meta($product_id, "is_butcherbox_product", 0);
    		} else {
        		update_post_meta($product_id, "is_butcherbox_product", 1);
    		}
        }
	}

    /*
     * Add script in admin panel
    */
	public function butcher_admin_scripts() {
        wp_enqueue_style( 'butcher-box-admin', get_stylesheet_directory_uri() . '/css/butcher-box-admin.css', '', "1.0" );

        wp_enqueue_script( 'butcher-box-admin', get_stylesheet_directory_uri()."/js/butcher-box-admin.js", array(), null, true );
	}

    /*
     * Get butcher-box data of product with ajax
    */
	public function get_product_butcher_box() {
        if(wp_doing_ajax()) {
            $product = wc_get_product($_POST['product_id']);
            $html = $this->_render_butcherbox_html($product);
            echo $html;
    		die();
        }
	}

    public function display_bb_items_values($formatted_meta, $meta) {
        $html = "";
        if($meta->key === "bb_items") {
            $html = "<div>";
            $bb_items = unserialize($formatted_meta);
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

        return empty($html) ? $formatted_meta : $html;
    }

    public function display_bb_items_key($key, $meta) {
        return ($meta->key === "bb_items") ? "Box Items" : $key;
    }
}

$butcher_box = new Butcher_Box_Admin();
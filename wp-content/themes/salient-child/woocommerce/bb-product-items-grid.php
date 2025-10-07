<?php 
    defined( 'ABSPATH' ) || exit;
    global $product;
    $cart = WC()->cart->get_cart();
    $bb_items = [];
    foreach ($cart as $item) {
        if($item['product_id'] == $product->get_id()) {
            $bb_items = unserialize($item['bb_items']);
        }
    }
    $hide = (empty($bb_items)) ? "hide" : "";
?>
<div class="added-products <?php echo $hide; ?>">
    <div class="box-header">
        <p><strong>Products currently in your box</strong></p>
    </div>
    <div class="box-container">
        <div class="box-wrapper box-main-div">
            <?php foreach ($bb_items as $item) : ?>
            <?php
                if(empty($item['product_id'])) {
                    continue;
                }
                $url = get_permalink($item['product_id']);
                $bb_product = wc_get_product($item['product_id']);
                if($bb_product->get_status() !== 'publish') {
                    continue;
                }
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $bb_product->get_id() ), 'woocommerce_gallery_thumbnail' );

                $v = new WC_Product_Variation($item['variation_id']);
                $variation = $v->get_attributes();
                if(!empty($variation)) {
                    $url = get_permalink($item['variation_id']);
                }
                $variation = implode(array_values($variation), ", ");
                $variation = empty($variation) ? "" : " - ".$variation;
            ?>
            <div id="bb_product_<?php echo $bb_product->get_id()."_".$item['variation_id']; ?>" class="box-item-container bb_item" data-variation_id="<?php echo $item['variation_id']; ?>" data-product_id="<?php echo $item['product_id']; ?>">
                <div class="details-container">
                    <div class="remove-div details-div">                    
                        <a href="javascript:void(0);" class="remove-bb-item remove" aria-label="Remove this item" data-product_id="<?php echo $item['product_id']; ?>" data-product_sku="" data-variation_id="<?php echo $item['variation_id']; ?>">×</a>
                    </div>
                    <div class="img-div details-div">
                        <a href="javascript:void(0);">
                            <img width="300" height="300" src="<?php echo $image[0]; ?>" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                        </a>
                    </div>
                    <div class="qty-div details-div cart">
                        <div class="quantity buttons_added">
                            <input type="button" value="-" class="minus" data-product_id="<?php echo $bb_product->get_id(); ?>" data-variation_id="<?php echo $item['variation_id']; ?>">
                            <input type="number" id="quantity" class="bb_qty_text input-text qty text" step="1" min="0" max="" value="<?php echo $item['qty']; ?>" title="Qty" size="4" inputmode="numeric" readonly>
                            <input type="button" value="+" class="plus" data-product_id="<?php echo $bb_product->get_id(); ?>" data-variation_id="<?php echo $item['variation_id']; ?>">
                        </div>
                    </div>                    
                </div>
                <div class="product-title-div">
                    <a href="<?php echo $url; ?>" target="_blank"><?php echo $bb_product->get_title(). " ". $variation; ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
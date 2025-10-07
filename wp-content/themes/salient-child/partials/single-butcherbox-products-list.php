<?php   
    $product = wc_get_product($post->ID);
    $args = array(
        'post_type'     => 'product_variation',
        'post_status'   => array( 'private', 'publish' ),
        'numberposts'   => -1,
        'orderby'       => 'menu_order',
        'order'         => 'asc',
        'post_parent'   => $post->ID
    );
    $posts = get_posts( $args );
    $variation_key = $variations = [];
    foreach ($posts as $p) {
        $variations[] = new WC_Product_Variation($p->ID);
    }
    foreach ($variations as $v) {
        $variation_key[$v->get_ID()] = $v->get_attributes();
        $variation_key[$v->get_ID()]['price'] = $v->get_price();
    }
    $bb_items = [];
    $cart = WC()->cart->get_cart();
    $box_variation = "";

    foreach ($cart as $item) {
        if($item['product_id'] == $product->get_id()) {
            $box_variation = $item['variation_id'];
            $bb_items = unserialize($item['bb_items']);
        }
    }
    $hide = (empty($bb_items)) ? "hide" : "";
?>
<form class="variations_form cart">
    <table class="butcher-box-table" cellspacing="0">
        <tbody>
            <tr>
                <td class="label"><label for="size">How many items do you want in your butchers box?</label></td>
                <td class="value">
                    <select id="buthcer-box-variation">
                        <option value="">Choose how many items you want</option>
                        <?php foreach ($variation_key as $key => $v) : ?>
                            <?php $attr = ($key == $box_variation) ? 'selected="selected"' : ''; ?>
                            <option data-variation_id="<?php echo $key; ?>" data-price="<?php echo $v['price']; ?>" value="<?php echo array_values($v)[0]; ?>" class="attached enabled" <?php echo $attr; ?>><?php echo array_values($v)[0]; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="select-product <?php echo $hide; ?>">
                <td class="label"><label>Select items to include in your box</label></td>
                <td class="value">
                    <select id="butcherbox_product">
                        <option value="">Choose a product</option>
                        <?php foreach ($butcherbox_products as $p) : ?>
                            <option data-is_variable="<?php echo $p['is_variable']; ?>" value="<?php echo $p['product_id']; ?>" class="attached enabled"><?php echo $p['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="product-variations-tr-1 hide">
                <td class="value">
                    <select id="bb-product-variation-1"></select>
                </td>
            </tr>
            <tr class="product-variations-tr-2 hide">
                <td class="value">
                    <select id="bb-product-variation-2"></select>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="bb_variation_wrap single_variation_wrap <?php echo $hide; ?>">
        <div class="woocommerce-variation single_variation" style="display: block;">
            <div class="woocommerce-variation-description"></div>
            <div class="woocommerce-variation-price">
                <span class="price">
                    <span class="woocommerce-Price-amount amount">
                        <span class="woocommerce-Price-currencySymbol">$</span><span class="butcherbox-price">15.00</span><span class="left-qty"></span>
                    </span>
                </span>
            </div>
            <div class="woocommerce-variation-availability"></div>
        </div>
        <div class="woocommerce-variation-add-to-cart variations_button woocommerce-variation-add-to-cart-enabled">
            <div class="quantity buttons_added">
                <input type="button" value="-" class="minus">
                <input type="number" id="quantity_bb" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" inputmode="numeric">
                <input type="button" value="+" class="plus">
            </div>
            <button type="button" class="single_add_to_cart_button button alt">Add to Butcher Box</button>
            <input type="hidden" name="add-to-cart" value="<?php echo $post->ID; ?>">
            <input type="hidden" name="product_id" id="product_id" value="<?php echo $post->ID; ?>">
            <input type="hidden" name="variation_id" class="variation_id" value="">
        </div>
    </div>
</form>

<?php
/**
* Single Recipe Content
*
* @version 10.5
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $nectar_options;
global $woocommerce_loop;
$woocommerce_loop['columns'] = 3;

$nectar_post_format            = get_post_format();
$hide_featrued_image           = ( ! empty( $nectar_options['blog_hide_featured_image'] ) ) ? $nectar_options['blog_hide_featured_image'] : '0';

$single_post_header_inherit_fi = ( ! empty( $nectar_options['blog_post_header_inherit_featured_image'] ) ) ? $nectar_options['blog_post_header_inherit_featured_image'] : '0';
$recipe = get_post();
$recipe_details = get_post_meta($recipe->ID);
$instruction_arr = unserialize($recipe_details['recipe_instructions'][0]);
$recipe = new WPURP_Recipe($recipe);
$instructions = [];
foreach ($instruction_arr as $i) {
    $instructions[$i['group']][] = $i;
}
$id = get_the_ID();
$related_products = get_field("related_product");
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="inner-wrap">
        <div class="post-content single-recipe-content" data-hide-featured-media="<?php echo esc_attr( $hide_featrued_image ); ?>">
            <div class="content-inner">
                <div class="text-justify">
                <?php 
                    $content = preg_replace("/\[wpurp-searchable-recipe\][^\[]*\[\/wpurp-searchable-recipe\]/", "", $recipe->post_content());
                    echo do_shortcode($content);
                 ?>
                </div>
                <div style="clear: both;"></div>
                <div class="" data-tab-style="fullwidth">
                    <div class="recipe-tabs-container">
                        <?php
                            $is_related_products = $is_notes = $is_instruction = $is_desc = false;
                            if(!empty($recipe->description())) {
                                $is_desc = true;
                            }
                            if(!empty($instructions)) {
                                $is_instruction = true;
                            }
                            if(!empty($recipe_details['recipe_notes'])) {
                                $is_notes = true;
                            }
                            if(!empty($related_products)) {
                                $is_related_products = true;
                            }
                        ?>
                        <ul id="recipe-tabs" class="tabs">
                            <?php if($is_desc) : ?>
                            <li class="tab description_tab active">
                                <a href="javascript:void(0);" data-id="#tab-description">Description</a>
                            </li>
                            <?php endif; ?>
                            <?php if($is_instruction) : ?>
                            <li class="tab additional_information_tab <?php echo ($is_desc) ? '' : 'active'; ?>">
                                <a href="javascript:void(0);" data-id="#tab-instructions">Method</a>
                            </li>
                            <?php endif; ?>
                            <?php if($is_notes) : ?>
                            <li class="tab additional_information_tab <?php echo ($is_desc || $is_instruction) ? '' : 'active'; ?>">
                                <a href="javascript:void(0);" data-id="#tab-notes">Notes</a>
                            </li>
                            <?php endif; ?>
                            <?php if($is_related_products) : ?>
                            <li class="tab additional_information_tab <?php echo ($is_desc || $is_instruction || $is_notes) ? '' : 'active'; ?>">
                                <a href="javascript:void(0);" data-id="#tab-related_products">Related Products</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div style="clear: both;"></div>
                <?php if($is_desc) : ?>
                <div id="tab-description" class="panel entry-content">
                    <div class="text-justify"><?php echo $recipe->description(); ?></div>
                </div>
                <?php endif; ?>
                <?php if($is_instruction) : ?>
                <div id="tab-instructions" class="panel entry-content <?php echo ($is_desc) ? 'hide' : ''; ?>">
                    <div class="instructions-container">
                        <?php foreach ($instructions as $grp) : ?>
                            <h5 class="group-title"><?php echo $grp[0]['group']; ?></h5>
                            <?php  $i=1;  ?>
                            <?php foreach ($grp as $ins) : ?>
                                <p class="<?php if($grp[0]['group']!="") { echo preg_replace('/\s+/', '', $grp[0]['group']."_"); } ?>description_<?php echo $i; ?>"><?php echo $ins['description']; ?></p>
                                <?php  $i++; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($is_notes) : ?>
                <div id="tab-notes" class="panel entry-content <?php echo ($is_desc || $is_instruction) ? 'hide' : ''; ?>">
                    <div class="single-recipe-notes text-justify">
                        <div class="recipe-notes-container"><?php echo $recipe_details['recipe_notes'][0]; ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($is_related_products) : ?>
                <div id="tab-related_products" class="panel entry-content woocommerce <?php echo ($is_desc || $is_instruction || $is_notes) ? 'hide' : ''; ?>">
                    <?php
                        do_action('woocommerce_before_shop_loop');
                        woocommerce_product_loop_start();
                        foreach($related_products as $related_product) {
                            $post_object = get_post($related_product);
                            setup_postdata($GLOBALS['post'] =& $post_object);
                            wc_get_template_part('content', 'product');
                        }
                        wp_reset_postdata();
                        woocommerce_product_loop_end();
                        do_action('woocommerce_after_shop_loop');
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div><!--/post-content-->
    </div><!--/inner-wrap-->
</article>
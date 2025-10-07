<?php 
    $bg = wp_get_attachment_url( get_post_thumbnail_id() );
    $id = get_the_ID();
    $ratings = get_post_meta($id, "recipe_rating", true);

    $allergens = get_the_terms($id, "allergen");
    $allergen_icons = [];
    foreach ($allergens as $allergen) {
        $allergen_icon = get_field("icon_light", "allergen_".$allergen->term_id);
        $allergen_icons[] = [
            "link" => $allergen_icon['url'],
            "name" => $allergen->name,
        ];
    }

    $meat = get_the_terms($id, "meat");

    $recipe_cat = get_the_terms($id, "recipe_cat");
?>
<div id="page-header-wrap" data-animate-in-effect="none" data-midnight="light" class="single-recipe-header" style="height: 450px;">
    <div id="page-header-bg" class="not-loaded bottom-shadow hentry bg-overlay" data-post-hs="default_minimal" data-padding-amt="normal" data-animate-in-effect="none" data-midnight="light" data-text-effect="" data-bg-pos="center" data-alignment="left" data-alignment-v="middle" data-parallax="0" data-height="450" style="background-color: #000; height:450px;">
        <div class="page-header-bg-image-wrap" id="nectar-page-header-p-wrap" data-parallax-speed="medium">
            <div class="page-header-bg-image" style="background-image: url(<?php echo $bg; ?>);"></div>
        </div>
        <div class="container"><img class="hidden-social-img" src="<?php echo $bg; ?>" alt="<?php echo the_title(); ?>">
            <div class="row">
                <div class="col span_6 section-title blog-title" data-remove-post-date="0" data-remove-post-author="1" data-remove-post-comment-number="1">
                    <div class="inner-wrap">
                        <?php if(!empty($recipe_cat)) : ?>
                        <a class="general" href="<?php echo get_category_link($recipe_cat[0]); ?>" style="transform: rotateX(0deg) translate(0px, 0px); opacity: 1;"><?php echo $recipe_cat[0]->name; ?></a>
                        <?php endif; ?>
                        <h1 class="entry-title" style="transform: rotateX(0deg) translate(0px, 0px); opacity: 1;"><?php echo the_title(); ?></h1>
                        <div id="single-below-header" data-hide-on-mobile="false" style="transform: rotateX(0deg) translate(0px, 0px); opacity: 1; margin-top: 10px;">
                            <span class="wpurp-recipe-stars">
                                <?php do_action('get_recipe_ratings_html', $id); ?>
                            </span>
                            <?php if(!empty($allergen_icons)) : ?>
                            <span class="meta-author vcard author">
                                <?php foreach ($allergen_icons as $a) : ?>
                                    <span class="cat-icon">
                                        <img src="<?php echo $a['link']; ?>">
                                        <div class="cat-name"><?php echo $a['name']; ?></div>
                                    </span>
                                <?php endforeach; ?>
                            </span>
                            <?php endif; ?>
                        </div><!--/single-below-header-->
                        <?php if(!empty($meat)) : ?>
                        <a class="general" href="<?php echo get_category_link($meat[0]); ?>" style="transform: rotateX(0deg) translate(0px, 0px); opacity: 1;"><?php echo $meat[0]->name; ?></a>
                        <?php endif; ?>
                    </div>
                </div><!--/section-title-->
            </div><!--/row-->
        </div>
    </div>
</div>
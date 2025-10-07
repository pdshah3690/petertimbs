<div id="recipe-container" class="row" style="transform: none;">
    <div class="post-area col span_9 masonry auto_meta_overlaid_spaced " data-ams="4px" data-remove-post-date="" data-remove-post-author="" data-remove-post-comment-number="" data-remove-post-nectar-love="">
        <div class="posts-container" data-load-animation="none">
    <?php
        $count = 0;
        $is_single = true;
        $html = "";
        // _dd($recipes->max_num_pages, false);
        foreach ($recipes->posts as $r) {
            $difficulty = get_the_terms($r->ID, "difficulty");
            $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;

            $servings = get_post_meta($r->ID, "recipe_servings", true);
            $cook_time = get_post_meta($r->ID, "recipe_cook_time", true)." mins";

    ?>
            <article id="post-<?php echo $r->ID; ?>" class=" masonry-blog-item post-<?php echo $r->ID; ?> post type-post status-publish format-standard has-post-thumbnail category-general">  
                <div class="inner-wrap">
                    <div class="post-content">
                        <div class="content-inner">
                            <a class="entire-meta-link" href="<?php echo get_permalink($r); ?>"></a>
                            <span class="post-featured-img" style="background-image: url('<?php echo wp_get_attachment_url(get_post_thumbnail_id($r->ID)) ?>);"></span>
                            <div class="article-content-wrap">
                                <div class="post-header">
                                    <h3 class="title">
                                        <a href="<?php echo get_permalink($r); ?>"><?php echo $r->post_title; ?></a></h3>
                                </div>
                                <?php 
                                    $ratings = do_action('get_recipe_ratings_html', $r->ID);
                                    echo $ratings;
                                ?>
                                <div class="recipe-meta-details">
                                    <span><img class="recipe-detail-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/img/serving-size-light.png"><?php echo $servings; ?></span>
                                    <span><img class="recipe-detail-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/img/cook-time-light.png"><?php echo $cook_time; ?></span>
                                </div>
                                <div class="recipe-meta-details">
                                    <span><img class="recipe-detail-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/img/difficulty-light.png"><?php echo $difficulty; ?></span>
                                </div>
                            </div><!--article-content-wrap-->
                        </div><!--/content-inner-->
                    </div><!--/post-content-->
                </div><!--/inner-wrap-->
            </article>
    <?php } ?>
        </div>
        <div class="pagination">
            <ul class="page-numbers">
                <?php if($page > 1) : ?>
                    <li>
                        <a class="page-numbers" href="/recipes/page/<?php echo $page-1; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for($i=1; $i <= $recipes->max_num_pages; $i++) : ?>
                    <?php if($page == $i) : ?>
                        <li>
                            <span class="page-numbers current"><?php echo $i; ?></span>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="page-numbers" href="/recipes/page/<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if($page != $recipes->max_num_pages) : ?>
                    <li>
                        <a class="page-numbers" href="/recipes/page/<?php echo $page+1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php do_action('get_recipe_sidebar'); ?>
</div>
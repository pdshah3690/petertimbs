<?php 
    $id = get_the_ID();
    $recipe_details = get_post_meta($id);
    $difficulty = get_the_terms($id, "difficulty");
    $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;

    $servings = get_post_meta($id, "recipe_servings", true)." ". get_post_meta($id, "recipe_servings_type", true);
    $cook_time = get_post_meta($id, "recipe_cook_time", true)." ". get_post_meta($id, "recipe_cook_time_text", true);
    $no_of_ingredients = get_post_meta($id, "no_of_ingredients", true);
    $recipe = new WPURP_Recipe($id);
    $ingredients = get_post_meta($id, "recipe_ingredients", true);
?>
<div class="recipe-details-sidebar">
	<h4>KEY INFORMATION</h4>
	<span class="wpurp-icon">
		<svg class="nc-icon glyph" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" viewBox="0 0 24 24">
			<g>
				<path fill="%highlight_color%" d="M10,0C9.4,0,9,0.4,9,1v4H7V1c0-0.6-0.4-1-1-1S5,0.4,5,1v4H3V1c0-0.6-0.4-1-1-1S1,0.4,1,1v8c0,1.7,1.3,3,3,3
		v10c0,1.1,0.9,2,2,2s2-0.9,2-2V12c1.7,0,3-1.3,3-3V1C11,0.4,10.6,0,10,0z"></path>
				<path data-color="color-2" fill="%highlight_color%" d="M19,0c-3.3,0-6,2.7-6,6v9c0,0.6,0.4,1,1,1h2v6c0,1.1,0.9,2,2,2s2-0.9,2-2V1
		C20,0.4,19.6,0,19,0z"></path>
			</g>
		</svg>
		Servings
	</span>
	<span class="recipe-meta-details"><?php echo $servings; ?></span>
</div>
<div class="recipe-details-sidebar">
	<span class="wpurp-icon">
		<svg class="nc-icon glyph" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" viewBox="0 0 24 24">
			<g>
				<path data-color="color-2" fill="%highlight_color%" d="M9,9c0.6,0,1-0.4,1-1V4c0-0.6-0.4-1-1-1S8,3.4,8,4v4C8,8.6,8.4,9,9,9z"></path>
				<path data-color="color-2" fill="%highlight_color%" d="M4,12c0.6,0,1-0.4,1-1V7c0-0.6-0.4-1-1-1S3,6.4,3,7v4C3,11.6,3.4,12,4,12z"></path>
				<path data-color="color-2" fill="%highlight_color%" d="M14,12c0.6,0,1-0.4,1-1V7c0-0.6-0.4-1-1-1s-1,0.4-1,1v4C13,11.6,13.4,12,14,12z"></path>
				<path fill="%highlight_color%" d="M23,14h-5H1c-0.6,0-1,0.4-1,1v3c0,1.7,1.3,3,3,3h13c1.7,0,3-1.3,3-3v-1h4c0.6,0,1-0.4,1-1v-1
					C24,14.4,23.6,14,23,14z"></path>
			</g>
		</svg>
	</span>Cook Time
	<span class="recipe-meta-details"><?php echo $cook_time; ?></span>
</div>
<div class="recipe-details-sidebar">
    <span class="wpurp-icon">
        <svg class="nc-icon glyph" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="14px" height="14px" viewBox="0 0 24 24">
            <g>
                <path data-color="color-2" fill="%highlight_color%" d="M4.3,16.6l-2.2,2.2c-0.6,0.6-0.9,1.3-0.9,2.1c0,0.8,0.3,1.6,0.9,2.1s1.3,0.9,2.1,0.9
c0.8,0,1.6-0.3,2.1-0.9l2.2-2.2L4.3,16.6z"></path>
                <path fill="%highlight_color%" d="M22.6,5.4l-3.5-3.5c-1.1-1.1-2.6-1.8-4.2-1.8s-3.1,0.6-4.2,1.8l-8.4,8.4c-0.4,0.4-0.4,1,0,1.4l7.1,7.1
C9.5,18.9,9.7,19,10,19c0,0,0,0,0,0c0.3,0,0.5-0.1,0.7-0.3L22.6,6.8C23,6.4,23,5.8,22.6,5.4z M9.2,14.6l-1.4-1.4l6.4-6.4l1.4,1.4
L9.2,14.6z"></path>
            </g>
        </svg>
        Prep Time
    </span>
    <span class="recipe-meta-details"><?php echo $recipe->prep_time()." ".$recipe->prep_time_text(); ?></span>
</div>
<?php if(!empty($difficulty)) : ?>
<div class="recipe-details-sidebar">
	<span><img src="<?php echo get_stylesheet_directory_uri();?>/img/difficulty.png" style="width: 15px; margin: 0;padding: 0;">&nbsp;&nbsp;Difficulty</span>
	<span class="recipe-meta-details"><?php echo $difficulty; ?></span>
</div>
<?php endif; ?>
<div class="recipe-details-sidebar">
	<span class="wpurp-icon">
		<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="concierge-bell" class="svg-inline--fa fa-concierge-bell fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14px" height="14px"><path fill="currentColor" d="M288 130.54V112h16c8.84 0 16-7.16 16-16V80c0-8.84-7.16-16-16-16h-96c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h16v18.54C115.49 146.11 32 239.18 32 352h448c0-112.82-83.49-205.89-192-221.46zM496 384H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h480c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16z"></path></svg>
		No. Of Ingredients
	</span>
	<span class="recipe-meta-details"><?php echo $no_of_ingredients; ?> Ing.</span>
</div>
<?php if(!empty($ingredients)) : ?>
<div class="widget woocommerce widget_product_categories recipe-details-sidebar">
	<h4>INGREDIENT LIST</h4>
	<ul>
		<?php foreach ($ingredients as $i) : ?>
		<li class="cat-item cat-item-44">
			<div class="ingredient-title">
				<span><strong><?php echo $i['ingredient']; ?></strong></span>
			</div>
			<div class="recipe-ingredient-unit-container">
				<span class="count"><span class="post_count"> <?php echo $i['amount_normalized']." ".$i['unit']; ?> </span></span>
			</div>
			<?php if(!empty($i['notes'])) : ?>
				<div class="recipe-ingredient-note"><?php echo $i['notes']; ?></div>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
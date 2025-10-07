<div id="sidebar" data-nectar-ss="true" class="col span_3 col_last" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
    <div id="recipe-meat-tag" class="widget widget_product_tag_cloud"  style="clear: both;">
        <h4>FILTER BY MEAT TYPE</h4>
        <div class="tagcloud">
            <?php foreach ($meats as $m) : ?>
             <a href="javascript:void(0);" class="tag-cloud-link tag-link-52 tag-link-position-1" style="font-size: 8pt;" aria-label="whole (1 product)" data-slug="<?php echo $m->slug; ?>" data-term_id="<?php echo $m->term_id; ?>"><?php echo $m->name; ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="recipe-allergen-tag" class="widget widget_allergen">
    	<h4>FILTER BY DIETARY NEEDS</h4>
<?php 
	$html = "<div class='allergen-icon-main'>";
    $i = 0;
    foreach ($allergens as $a) {
        $clear = $i == 3 ? 'clear:both;' : '';
    	$icon = get_field("icon", "allergen_".$a->term_id);
    	$html .= "<div class='recipe-icon-wrapper' style='".$clear."'>";
        $html .=    "<a href='javascript:void(0);' class='tag-cloud-link' data-term_id='".$a->term_id."'>";
        $html .=        "<img src='".$icon['url']."' class='recipe-icon'/>";
        $html .=        "<span>".$a->name."</span>";
        $html .=    "</a>";
    	$html .= "</div>";
        $i++;
    }
    $html .= "</div>";
    echo $html;
?>
   	</div>
	<div id="recipe-master-ingredient-tag" class="widget widget_product_tag_cloud"  style="clear: both;">
		<h4>NUMBER OF INGREDIENTS</h4>
		<select class="select2" id="ingredient-no-filter">
            <option value="0">Filter by Number of Ingredients</option>
            <option value="1-5">1-5</option>
            <option value="6-10">6-10</option>
            <option value="11-15">11-15</option>
            <option value="16-20">16-20</option>
            <option value="21-25">21-25</option>
            <option value="26-">26+</option>
        </select>
	</div>
    <div id="recipe-master-ingredient-tag" class="widget widget_product_tag_cloud"  style="clear: both;">
        <h4>COOKING TIME</h4>
        <select class="select2" id="cook-time-filter">
            <option value="0">Filter by Cooking Time</option>
            <option value="10-45">10-45 Min.</option>
            <option value="45-60">45Min. - 1 Hour</option>
            <option value="60-180">1-3 Hours</option>
            <option value="240-600">4-10 Hours</option>
        </select>
    </div>
    <div id="recipe-master-ingredient-tag" class="widget widget_product_tag_cloud"  style="clear: both;">
        <h4>SERVING SIZE</h4>
        <select class="select2" id="serving-size-filter">
            <option value="0">Filter by Serving Size</option>
            <option value="1-4">1-4</option>
            <option value="5-8">5-8</option>
            <option value="9-12">9-12</option>
            <option value="13-20">13-20</option>
        </select>
    </div>

    <div id="recipe-rating-filter" class="widget widget_product_tag_cloud"  style="clear: both;">
        <h4>FILTER BY RATINGS</h4>
        <input type="text" class="rating-filter kv-fa rating-loading" value="0" data-size="sm" title="">
    </div>

    <div id="recipe-difficulty-tag" class="widget widget_product_tag_cloud"  style="clear: both;">
        <h4>FILTER BY DIFFICULTY</h4>
        <div class="tagcloud">
            <?php foreach ($difficulties as $d) : ?>
             <a href="javascript:void(0);" class="tag-cloud-link tag-link-52 tag-link-position-1" style="font-size: 8pt;" aria-label="whole (1 product)" data-slug="<?php echo $d->slug; ?>" data-term_id="<?php echo $d->term_id; ?>"><?php echo $d->name; ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="yith-woo-ajax-reset-navigation-1" class="widget yith-woocommerce-ajax-product-filter yith-woo-ajax-reset-navigation yith-woo-ajax-navigation woocommerce widget_layered_nav no-widget-title" style="margin-top: 40px;">
        <div class="yith-wcan">
            <a class="yith-wcan-reset-navigation button" href="http://www.petertimbs.jadecreative.co.nz/recipes/">Reset All Filters</a>
        </div>
    </div>
</div>
(function( $ ) {
    'use strict';
    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    var _render_butcher_box_settings = function(type = 'variable'){
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_product_butcher_box',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.butcherbox-container').html(html);
            }
        });
    }
    $(document.body).on('woocommerce_variations_saved woocommerce_variations_removed', function(){
        _render_butcher_box_settings();
    });
    $(document).on("change", "#product-type", function(){
        var type = $(this).val();
        _render_butcher_box_settings(type);
    });
    $( ".to" ).datepicker({
      maxDate: "+60d"
    });
 })(jQuery);
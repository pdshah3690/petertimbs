(function($){

    "use strict";

    class RankMathCustomFields {
        /**
         * Class constructor
         */
        constructor() {
            this.init()
            this.hooks()
            this.events()
        }
    
        /**
         * Init the custom fields
         */
        init() {
            this.fields = this.getFields()
            this.getContent = this.getContent.bind( this )
        }
    
        /**
         * Hook into Rank Math App eco-system
         */
        hooks() {
            wp.hooks.addFilter( 'rank_math_content', 'rank-math', this.getContent, 11 )
        }
    
        /**
         * Capture events from custom fields to refresh Rank Math analysis
         */
        events() {
            jQuery( this.fields ).each( function( key, value ) {
                jQuery( value ).on(
                    'keyup change',
                    function() {
                        rankMathEditor.refresh( 'content' )
                    }
                )
            } )
        }
    
        /**
         * Get custom fields ids.
         *
         * @return {Array} Array of fields.
         */
        getFields() {
            const fields = []
   
            fields.push( '#_nectar_portfolio_extra_content' );
            
            return fields
        }
    
        /**
         * Gather custom fields data for analysis
         *
         * @param {string} content Content to replce fields in.
         *
         * @return {string} Replaced content.
         */
        getContent = function( content ) {
            
            jQuery( this.fields ).each( function( key, value ) {
                content += jQuery( value ).val()
            } )

            content = this.extractTextFromShortcodes(content);
            return content
        }
        
        extractTextFromShortcodes = function( content ) {
            const stack = [];
            const extractedTexts = [];
            const regex = /\[([a-zA-Z_]+)([^\]]*)\]|\[\/([a-zA-Z_]+)\]|([^[]+)/g;
            const textAttrRegex = /(?:text|text_content|quote)\s*=\s*"([^"]*)"/g;

            // ensure page is using WPBakery and not classic editor.
            const wpBakeryShortcodeRegex = /\[vc_row\b/;
            if (!wpBakeryShortcodeRegex.test(content)) {
                return content;
            }
        
            let match;
            while ((match = regex.exec(content)) !== null) {
                if (match[1]) {
                    // Opening shortcode tag
                    const attrs = match[2];
                    let attrMatch;
                    // Extract text from attributes that begin with "text"
                    while ((attrMatch = textAttrRegex.exec(attrs)) !== null) {
                        if ( attrMatch[1].trim() !== 'true' ) {
                            extractedTexts.push(attrMatch[1].trim());
                        }
                    }
                    stack.push({ tag: match[1], text: '' });
                } else if (match[3]) {
                    // Closing shortcode tag
                    let nestedContent = '';
                    while (stack.length > 0) {
                        const top = stack.pop();
                        if (top.tag === match[3]) {
                            if (top.text.trim()) {
                                extractedTexts.push(top.text.trim());
                            }
                            break;
                        } else {
                            nestedContent = top.text + nestedContent;
                        }
                    }
                    if (stack.length > 0) {
                        stack[stack.length - 1].text += nestedContent;
                    }
                } else if (match[4]) {
                    // Text content
                    if (stack.length > 0) {
                        stack[stack.length - 1].text += match[4];
                    }
                }
            }
        
            return extractedTexts.join(' ');
        }
    }
    

    $(document).ready(function(){
        setTimeout( function() {
            if( typeof rankMathEditor !== 'undefined' && typeof rankMath !== 'undefined' ) {
                new RankMathCustomFields();
            }
        }, 500);

        if( window.salient_portfolio_admin_l10n.activate_unload ) {
            
            var submittingForm = false;

            var form = document.getElementById('post');
            form.addEventListener('submit', function(){
                submittingForm = true;
            });

            window.onbeforeunload = function(){

                if( form && !submittingForm &&
                    $('#vc_navbar-undo').length > 0 &&
                    $('#vc_navbar-undo[disabled]').length == 0 ) {
                    return window.salient_portfolio_admin_l10n.save_alert;
                }
            };
        }

    });


})(jQuery);
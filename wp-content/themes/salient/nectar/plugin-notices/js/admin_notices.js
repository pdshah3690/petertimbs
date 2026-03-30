/**
 * Salient plugin notice
 *
 * @package Salient
 * @author ThemeNectar
 */
 /* global jQuery */
 /* global notice_params */

(function($) {

	"use strict";

	jQuery( document ).ready( function() {

		jQuery( document ).on( 'click', '.nectar-dismiss-cpt-notice .notice-dismiss', function() {

			var data = {
				action: 'nectar_dismiss_plugin_notice',
				nonce: (typeof notice_params !== 'undefined' && notice_params.nonce) ? notice_params.nonce : ''
			};

			jQuery.post( notice_params.ajaxurl, data, function() {
			});

		});

	});

})(jQuery);
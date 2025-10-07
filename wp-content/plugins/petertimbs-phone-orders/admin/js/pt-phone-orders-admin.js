(function($) {
	'use strict';

	$(document).ready(function (e) {
		$(".preparation_time").datetimepicker({
            formatDate : 'Y-m-d H:i',
            minDate : new Date()
        });
		$(".all-products").select2();
		$(".all-products").on("select2:select", function() {
			var id = $('.all-products').val();
			if(id > 0) {
				var name = $('.all-products').find(":selected").text();
				var variation_id = $('.all-products').find(":selected").data("variation_id");
				var price = $('.all-products').find(":selected").data("price");
				var image = $('.all-products').find(":selected").data("image");

				var tr_id = (variation_id == 0) ? "product_"+id : "product_"+variation_id;

				var tr = "<tr id='"+tr_id+"' class='order_item_tr' data-id='"+id+"' data-variation_id='"+variation_id+"'>";
				tr = tr + "<td><span class='remove_item'>x</span></td>";
				tr = tr + "<td><img src='"+image+"' width='50' height='30' /><br><strong>"+name+"</strong></td>";
				tr = tr + "<td><select class='item_type'><option value='0'>Select Type</option><option value='Each'>Each</option><option value='Per Kg.'>Per Kg.</option></select></td>";
				tr = tr + "<td><input type='number' min='1' class='weight hide' value='1' /></td>";
				tr = tr + "<td><textarea class='item_description'></textarea></td>";
				tr = tr + "<td><input type='number' min='1' class='qty' value='1' /></td>";
				tr = tr + "<td><input type='number' min='1' class='price hide' value='"+price+"'/></td>";
				tr = tr + "<td><span class='price_td'>"+price+"</span></td>";
				tr = tr + "</tr>";
				$("#sub_total_tr").before(tr);
				_calculate_pickup_time();
				_update_total();
			}
		});
		$(document).on("click", ".remove_item", function() {
			$(this).parent().parent().remove();
			_calculate_pickup_time();
			_update_total();
		});
		$(document).on("change", ".weight", function() {
			var tr = $(this).parent().parent();
			var weight = $(this).val();
			var price = $(".price", tr).val();
			var qty = $(".qty", tr).val();
			var type = $(".item_type", tr).val();
			var row_price = qty*price;
			if(type == 'Per Kg.') {
				row_price = (row_price*weight);
			}
			$(".price_td", tr).text("$"+row_price);
			_update_total();
			_calculate_pickup_time();
		});

		$(document).on("change", ".qty", function() {
			var tr = $(this).parent().parent();
			var qty = $(this).val();
			var price = $(".price", tr).val();
			var weight = $(".weight", tr).val();
			var type = $(".item_type", tr).val();
			var row_price = qty*price;
			if(type == 'Per Kg.') {
				row_price = (row_price*weight);
			}
			$(".price_td", tr).text("$"+row_price);
			_update_total();
			_calculate_pickup_time();
		});
		$(document).on("change", ".price", function() {
			var tr = $(this).parent().parent();
			var price = $(this).val();
			var qty = $(".qty", tr).val();
			var weight = $(".weight", tr).val();
			var type = $(".item_type", tr).val();
			var row_price = qty*price;
			if(type == 'Per Kg.') {
				row_price = (row_price*weight);
			}
			$(".price_td", tr).text("$"+row_price);
			_update_total();
		});

		$(document).on("change", ".add_fee", function() {
			_update_total();
		});
		$(".customer_email").select2();
		$(".customer_email").on("select2:select", function() {
			var email = $(this).val();
			if(email == 0) {
				$(".billing_email").removeClass("hide");
				$("._billing_email_txt").val("");
			} else {
				$(".billing_email").addClass("hide");
				$.ajax({
					url : ajaxurl,
					data : {
						action : "get_customer_last_order_details",
						email : email
					},
					type : "post",
					dataType : "JSON",
					success : function(response) {
						if(response.success) {
							$.each(response["data"], function(k, v) {
								$("."+k).val(v);
							});
						}
					}
				});
			}
		});
		var _update_total = function() {
			var grand_total = 0;
			$(".order_item_tr").each(function() {
				var tr = $(this);
				var qty = $(".qty", tr).val();
				var price = $(".price", tr).val();
				var weight = $(".weight", tr).val();
				var type = $(".item_type", tr).val();
				var row_price = qty*price;
				if(type == 'Per Kg.') {
					row_price = row_price*weight;
				}
				grand_total = Number(grand_total) + Number(row_price);
			});
			$(".sub_total").text("$"+grand_total);
			var fee = $(".add_fee").val();
			grand_total = Number(grand_total) + Number(fee);
			$(".total").text("$"+grand_total);
		}

		var _calculate_pickup_time = function() {
			var cart = [];
			$(".preparation_time").val("");
			$(".order_item_tr").each(function(e) {
				var tr = $(this);
				var qty = $(".qty", tr).val();
				var product_id = tr.data("id");
				var variation_id = tr.data("variation_id");
				cart.push({"qty" : qty, "product_id" : product_id, "variation_id" : variation_id});
			});
			if(cart.length > 0) {
				$.ajax({
					url : ajaxurl,
					data : {
						action : "get_earlier_pickup_time",
						cart : cart
					},
					type : "post",
					dataType : "JSON",
					success : function (response) {
						if(response.success) {
							$(".preparation_time").val(response.preparation_time);
						}
					}
				});
			}
		}

		$(document).on("change", ".item_type", function(e) {
			var type = $(this).val();
			var tr = $(this).parent().parent();
			var qty = $(".qty", tr).val();
			var price = $(".price", tr).val();
			var weight = $(".weight", tr).val();
			var row_price = 0;
			if(type == 0) {
				$(".price", tr).addClass("hide");
				$(".weight", tr).addClass("hide");
			} else {
				$(".price", tr).removeClass("hide");
				if(type == 'Each') {
					row_price = (qty*price);
					$(".weight", tr).addClass("hide");
				}
				if(type == 'Per Kg.') {
					row_price = (qty*price*weight);
					$(".weight", tr).removeClass("hide");
				}
			}
			$(".price_td", tr).text("$"+row_price);
		});

		$(".create_order").on("click", function() {
			var error = false;
			var email = $(".customer_email").val();
			if( email == 0) {
				 email = $("._billing_email_txt").val();
				if(email == "") {
					$("._billing_email_txt").parent().addClass("has-error");
					error = true;
				} else {
					$("._billing_email_txt").parent().removeClass("has-error");
				}
			}
			var _billing_details = {
				_billing_email : email,
				_billing_first_name : $("._billing_first_name").val(),
				_billing_last_name : $("._billing_last_name").val(),
				_billing_address_1 : $("._billing_address_1").val(),
				_billing_address_2 : $("._billing_address_2").val(),
				_billing_city : $("._billing_city").val(),
				_billing_state : $("._billing_state").val(),
				_billing_postcode : $("._billing_postcode").val(),
				_billing_phone : $("._billing_phone").val(),
				preparation_time : $(".preparation_time").val(),
			}
			$.each(_billing_details, function(k, v) {
				if((k == "_billing_first_name" || k == "_billing_last_name") && v == "") {
					$("."+k).parent().addClass("has-error");
					error = true;
				} else {
					$("."+k).parent().removeClass("has-error");
				}
			});
			var cart = [];
			$(".order_item_tr").each(function(e) {
				var tr = $(this);
				var qty = $(".qty", tr).val();
				var product_id = tr.data("id");
				var variation_id = tr.data("variation_id");
				var item_type = $(".item_type", tr).val();
				var item_description = $(".item_description", tr).val();
				var price = $(".price", tr).val();
				var weight = $(".weight", tr).val();
				cart.push({
					"qty" : qty,
					"product_id" : product_id,
					"variation_id" : variation_id,
					"price" : price,
					"weight" : weight,
					"item_type" : item_type,
					"item_description" : item_description
				});
			});

			if(cart.length == 0) {
				alert("Add items to create order");
			}
			if(error) {
				return false;
			}
			var _self = $(this);
			_self.attr("disabled", "disabled");
			$.ajax({
				url : ajaxurl,
				data : {
					action : "create_phone_order",
					cart : cart,
					_billing_details : _billing_details,
					fee : $(".add_fee").val(),
					customer_note : $(".customer_note").val(),
					private_note : $(".private_note").val(),
					order_id : $('.order_id').val(),
					select_store : $('.select_store').val(),
				},
				type : "post",
				dataType : "JSON",
				success : function(response) {
					_self.removeAttr("disabled");
					if(response.success) {
						if(response.edit){
							alert("Order created with id #"+response.order_id);
							var url = window.location.href;
							window.location.replace(url.split('&')[0]);
						}else{
							alert("Order created with id #"+response.order_id);
							$(".order_item_tr").remove();
							$(".add_fee").val("");
							_update_total();
							window.location.reload();
						}
					}
				}
			});
		});
	});
})(jQuery);
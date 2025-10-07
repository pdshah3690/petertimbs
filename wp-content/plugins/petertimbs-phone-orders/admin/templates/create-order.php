<?php 
	$order_id = empty($_GET['order_id']) ? '' : $_GET['order_id'];
	if(!empty($order_id)){
		$order = wc_get_order( $order_id );
		$order_data = $order->get_data();

		$billing_email = $order_data['billing']['email'];
		$billing_phone = $order_data['billing']['phone'];
		$billing_first_name = $order_data['billing']['first_name'];
		$billing_last_name = $order_data['billing']['last_name'];
		$billing_address_1 = $order_data['billing']['address_1'];
		$billing_address_2 = $order_data['billing']['address_2'];
		$billing_city = $order_data['billing']['city'];
		$billing_state = $order_data['billing']['state'];
		$billing_postcode = $order_data['billing']['postcode'];

		$store = get_post_meta($order_id, '_store_collection', 1);
		$preparation_time = get_post_meta($order_id, '_order_preparation_time', 1);

		$sub_total = "$".$order->get_subtotal();
		$grand_total = "$".$order->get_total();

		$customer_note = $order->get_customer_note();

		foreach( $order->get_items('fee') as $item_id => $item_fee ){
		    // The fee name
		    $fee_name = $item_fee->get_name();
		    // The fee total amount
		    $fee_total = $item_fee->get_total();
		}

	}

?>
<div class="create-order-main">
	<div class="create-order-container">
		<div class="create-order-content">
			<input type="hidden" name="order_id" class="order_id" value="<?php echo $order_id ?>">
			<div class="product-table-wrapper">
				<select class="all-products">
					<option value="0">Select Product</option>
					<?php foreach ($products as $p) : ?>
						<option value="<?php echo $p['is_simple'] ? $p['id'] : $p['parent_id']; ?>" 
							data-price="<?php echo $p['price']; ?>" 
							data-image="<?php echo $p['image']; ?>"
							data-variation_id="<?php echo $p['is_simple'] ? 0 : $p['variation_id']; ?>"
							><?php echo $p['name']; ?></option>
					<?php endforeach; ?>
				</select>
				<select class="select_store">
					<option value="0">Select Store</option>
						<option value="edgeware" <?php echo $store == 'edgeware' ? 'selected' : '' ; ?> >Edgeware</option>
						<option value="bishopdale" <?php echo $store == 'bishopdale' ? 'selected' : '' ; ?> >Bishopdale</option>
				</select>
				<div style="height: 20px;"></div>
				<table>
					<thead>
						<tr>
							<th>#</th>
							<th>Product Name</th>
							<th>Type</th>
							<th>Weight</th>
							<th>Description</th>
							<th>Qty</th>
							<th>Price</th>
							<th>Total</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($order_id)){
							foreach($order->get_items() as $item_id => $item){
								 $product = $item->get_product();
								$item_type = wc_get_order_item_meta($item->get_id(), 'Item Type', 1);
								$weight = wc_get_order_item_meta($item->get_id(), 'Weight', 1);
								$description = wc_get_order_item_meta($item->get_id(), 'Description', 1);
								?>
									<tr id="product_<?php echo $item->get_product_id(); ?>" class="order_item_tr" data-id="<?php echo $item->get_product_id(); ?>" data-variation_id="<?php echo empty($item->get_variation_id()) ? 0 : $item->get_variation_id(); ?>">
										<td><span class="remove_item">x</span></td>
										<td><img src="<?php echo get_the_post_thumbnail_url($item->get_product_id()); ?>" width="50" height="30"><br><strong><?php echo $item->get_name(); ?></strong></td>
										<td>
											<select class="item_type">
												<option value="0">Select Type</option>
												<option value="Each" <?php echo $item_type == 'Each' ? 'selected' : '' ; ?>>Each</option>
												<option value="Per Kg." <?php echo $item_type == 'Per Kg.' ? 'selected' : '' ; ?>>Per Kg.</option>
											</select>
										</td>
										<td><input type="number" min="1" class="weight <?php echo $item_type == 'Per Kg.' ? '' : 'hide'; ?> " value="<?php echo empty($weight) ? 1 : $weight ; ?>"></td>
										<td><textarea class="item_description"><?php echo $description; ?></textarea></td>
										<td><input type="number" min="1" class="qty" value="<?php echo empty($item->get_quantity()) ? 1 : $item->get_quantity() ; ?>"></td>
										<td><input type="number" min="1" class="price <?php echo empty($item_type) ? 'hide' : ''; ?>" value="<?php echo $product->get_price(); ?>"></td>
										<td><span class="price_td">$<?php echo $item->get_total(); ?></span></td>
									</tr>
								<?php
							}
						} ?>
						<tr class="order_sub_tr" id="sub_total_tr">
							<td colspan="7">Sub Total:</td>
							<td><span class="sub_total" ><?php echo $sub_total; ?></span></td>
						</tr>
						<tr class="order_sub_tr" id="fee_tr">
							<td colspan="7">Add Fee:</td>
							<td><input type="number" class="add_fee" value="<?php echo !empty($fee_total) ? $fee_total : 0 ?>" min="0"></td>
						</tr>
						<tr class="order_sub_tr" id="total_tr">
							<td colspan="7">Grand Total:</td>
							<td><span class="total"><?php echo $grand_total; ?></span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="customer-billing-details">
				<select class="customer_email">
					<option value="0">New Customer</option>
					<?php foreach($customers as $c) : ?>
						<option value="<?php echo $c['email']; ?>" <?php echo $c['email'] == $billing_email ? 'selected' : ''; ?> >&lt;<?php echo $c['first_name']." ".$c['last_name']; ?>&gt; <?php echo $c['email']; ?></option>
					<?php endforeach; ?>
				</select>
				<div class="_billing_address">
					<div class="input-row-wide billing_email">
						<label>Email:</label>
						<input type="email" class="_billing_email_txt" value="<?php echo $billing_email; ?>">
					</div>
					<div class="input-row-wide">
						<label>Phone Number:</label>
						<input type="text" class="_billing_phone" value="<?php echo $billing_phone; ?>">
					</div>
					<div class="input-row-first">
						<label>First Name:</label>
						<input type="text" class="_billing_first_name" value="<?php echo $billing_first_name; ?>">
					</div>
					<div class="input-row-last">
						<label>Last Name:</label>
						<input type="text" class="_billing_last_name" value="<?php echo $billing_last_name; ?>">
					</div>
					<div class="input-row-first">
						<label>Address Line 1:</label>
						<input type="text" class="_billing_address_1" value="<?php echo $billing_address_1; ?>">
					</div>
					<div class="input-row-last">
						<label>Address Line 2:</label>
						<input type="text" class="_billing_address_2" value="<?php echo $billing_address_2; ?>">
					</div>
					<div class="input-row-first">
						<label>City:</label>
						<input type="text" class="_billing_city" value="<?php echo $billing_city; ?>">
					</div>
					<div class="input-row-last">
						<label>State:</label>
						<input type="text" class="_billing_state" value="<?php echo $billing_state; ?>">
					</div>
					<div class="input-row-wide">
						<label>Postcode:</label>
						<input type="text" class="_billing_postcode" value="<?php echo $billing_postcode; ?>">
					</div>
					<div class="input-row-wide">
						<label>Pickup Date Time:</label>
						<input type="text" class="preparation_time" value="<?php echo $preparation_time; ?>">
					</div>
					<div>
						<button type="button" class="create_order"><?php echo !empty($order_id) ? 'Update' : 'Create' ; ?> Order</button>
					</div>
					<input type="hidden" class="_customer_user">
				</div>
				<div style="clear: both;"></div>
			</div>
			<div class="notes">
				<div class="input-row-wide" style="margin-top: 27px;">
					<label>Customer Note:</label>
					<textarea class="customer_note"><?php echo $customer_note; ?></textarea>
				</div>
				<div class="input-row-wide">
					<label>Private Note:</label>
					<textarea class="private_note"></textarea>
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
	</div>
</div>
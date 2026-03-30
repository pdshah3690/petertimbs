<?php
	$range = empty($_GET['range']) ? '' : $_GET['range'];
	$start_date = empty($_GET['start_date']) ? '' : $_GET['start_date'];
	$end_date = empty($_GET['end_date']) ? '' : $_GET['end_date'];
	$type = empty($_GET['order_type']) ? '' : $_GET['order_type'];
	$store = empty($_GET['select_store']) ? '' : $_GET['select_store'];

	$param = empty($type) ? '' : '&order_type='.$type;
	$storeParam = empty($store) ? '' : '&select_store='.$store;
?>
<div class="customer-report-main woocommerce-reports-wide">
	<div class="customer-report-wrapper postbox">
		<div class="customer-report-header">
			<div class="stats_range">
				<ul class="">
					<li>
						<form id="order_type_form" method="GET">
							<select id="order_type" name="order_type">
								<option value="">Select Order Type</option>
								<option value="pick_up" <?php echo $type == 'pick_up' ? 'selected="selected"' : ''; ?>>Store Pick Up</option>
								<option value="delivery" <?php echo $type == 'delivery' ? 'selected="selected"' : ''; ?>>Delivery</option>
								<option value="all" <?php echo $type == 'all' ? 'selected="selected"' : ''; ?>>All</option>
							</select>
							<select id="select_store" name="select_store">
								<option value="">Select Store</option>
								<option value="edgeware" <?php  echo $store == 'edgeware' ? 'selected="selected"' : ''; ?>>Edgeware</option>
								<option value="bishopdale" <?php echo $store == 'bishopdale' ? 'selected="selected"' : ''; ?>>Bishopdale</option>
								<option value="both" <?php echo $store == 'both' ? 'selected="selected"' : ''; ?>>Both</option>
							</select>
							<input type="hidden" name="page" value="wc-reports">
							<input type="hidden" name="range" value="<?php echo $range; ?>">
							<?php if($range == 'custom') :  ?>
								<input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
								<input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
							<?php endif; ?>
						</form>
					</li>
					<li class="<?php echo ($range === 'year') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=year<?php echo $param.$storeParam; ?>">Year</a></li>
					<li class="<?php echo ($range === 'last_month') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=last_month<?php echo $param.$storeParam; ?>">Last month</a></li>
					<li class="<?php echo ($range === 'month') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=month<?php echo $param.$storeParam; ?>">This month</a></li>
					<li class="<?php echo ($range === '7day') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=7day<?php echo $param.$storeParam; ?>">Last 7 days</a></li>
					<li class="<?php echo ($range === 'lastday') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=lastday<?php echo $param.$storeParam; ?>">Yesterday</a></li>
					<li class="<?php echo ($range === 'today') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=today<?php echo $param.$storeParam; ?>">Today</a></li>
					<li class="<?php echo ($range === 'tomorrow') ? 'active' : ''; ?>">
						<a href="/wp-admin/admin.php?page=wc-reports&amp;range=tomorrow<?php echo $param.$storeParam; ?>">Tomorrow</a></li>
					<li class="custom <?php echo ($range === 'custom') ? 'active' : ''; ?>">
						Custom:
						<form method="GET">
							<div>
								<input type="hidden" name="order_type" value="<?php echo $type; ?>">
								<input type="hidden" name="page" value="wc-reports">
								<input type="hidden" name="range" value="custom">
								<input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo $start_date; ?>" name="start_date" class="range_datepicker from"><span>–</span>
								<input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo $end_date; ?>" name="end_date" class="range_datepicker from">
								<button type="submit" class="button" value="Go">Go</button>
							</div>
						</form>
					</li>
				</ul>
			</div>
		</div>
		<div class="customer-report-content">
			<div class="customer-report-content-wrapper">
				<table class="table" id="customer_product_sale_table">
					<thead>
						<tr>
							<th>Order #</th>
							<th>Type</th>
							<th>Pick up / Delivery date time</th>
							<th>Product</th>
							<th>Description</th>
							<th>Total</th>
							<th>SKU</th>
							<th>Time (In Minutes.)</th>
							<th>Customer</th>
							<th>Qty.</th>
							<th>U.O.M</th>
							<th>Status</th>
							<!-- <th>Is Order</th> -->
							<th>Store</th>
							<th>Order create date</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data as $d) : ?>
							<tr>
								<td><a href="<?php echo get_edit_post_link($d['order_id']); ?>"><?php echo $d['order_id']; ?></a></td>
								<td><?php echo $d['type']; ?></td>
								<td><?php echo date("d/m/Y H:i", strtotime($d['preparation_time'])); ?></td>
								<td><?php echo $d['product_name']; ?></td>
								<td><?php echo $d['description']; ?></td>
								<td><?php echo "$".$d['order_total']; ?></td>
								<td><?php echo $d['sku']; ?></td>
								<td><?php echo $d['time']; ?></td>
								<td><?php echo $d['customer_name']; ?></td>
								<td><?php echo $d['qty']; ?></td>
								<td><?php echo empty($d['weight']) ? "-" : $d['weight']." Kg."; ?> </td>
								<td><?php echo ucfirst(str_replace("wc-", "", $d['status'])); ?> </td>
								<!-- <td><?php // echo $d['is_order']; ?> </td> -->
								<td><?php echo $d['store']; ?> </td>
								<td><?php echo date("d/m/Y H:i", strtotime($d['order_date'])); ?> </td>
								<td><a href="<?php echo get_admin_url(); ?>admin.php?page=petertimbs-phone-orders&order_id=<?php echo $d['order_id']; ?>">Edit</a>  </td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

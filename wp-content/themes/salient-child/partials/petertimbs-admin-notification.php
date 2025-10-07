<div class="notification-main">
	<h2>Send Notification</h2>
	<div id="notification-response-message" class="notice notice-success" style="display: none; margin: 0; padding: 10px 12px;"></div>
	<table class="form-table">
		<tbody>
			<tr id="select_check">
				<th scope="row">Select All Users :</th>
				<td>
					<input type="checkbox" name="all_user" id="all_user">
				</td>	
			</tr>
			<tr id="selected_all_zones">
				<th scope="row">Select Zones :</th>
				<td>
					<select class="" name="selected_zones" id="selected_zones" data-placeholder="Select Zones.." style="width:32.8%;">
						 <option></option>
						<option value="zone_one">Zone One</option>
						<option value="zone_two">Zone Two</option>
						<option value="zone_three">Zone Three</option>
						<option value="zone_four">Zone Four</option>
					</select>
				</td>	
			</tr>
			<tr id="selected_all_user">
				<th scope="row">Select Users :</th>
				<td>
					<select class="" name="selected_user" id="selected_user" data-placeholder="Select Users.." multiple>
						<?php foreach($customers as $c) : ?>
							<option value="<?php echo $c['ID']; ?>">&lt;<?php echo $c['first_name']." ".$c['last_name']; ?>&gt; <?php echo $c['email']; ?></option>
						<?php endforeach; ?>
					</select>
				</td>	
			</tr>
			<tr>
				<th scope="row">Title :</th>
				<td>
					<input type="text" name="title" id="title" style="width: 308.234px;padding: 1%;">
				</td>	
			</tr>
			<tr>
				<th scope="row">Body :</th>
				<td>
					<textarea name="body" id="body" style="width: 308.234px;padding: 1%;"></textarea>
				</td>	
			</tr>
			<tr id="">
				<th scope="row">Link a product :</th>
				<td>
					<select class="" name="selected_product" id="selected_product" data-placeholder="Link a product.." multiple style="width:32.8%;">
						<?php foreach ( $products as $product ) : ?>
							<option value="<?php echo $product->get_id(); ?>">&lt;<?php echo $product->get_id(); ?>&gt;<?php echo $product->get_title(); ?></option>
						<?php endforeach; ?>
					</select>
				</td>	
			</tr>
			<tr>
				<th scope="row">Notification Send Type :</th>
				<td>
					<input type="radio" name="notification_type" class="notification_type" value="instant">Instant
					<input type="radio" name="notification_type" class="notification_type" value="schedule">Schedule
				</td>	
			</tr>
            <tr id="type_schedule" style="display:none;">
				<th scope="row">Schedule at :</th>
				<td>
					<input type="text" name="schedule_at" id="schedule_at" />
				</td>	
			</tr>
		</tbody>	
	</table>
	
	<p class="submit">
		<input type="button" name="send_notification" id="send_notification" class="button button-primary" value="Send Notification">
	</p>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
  	<div class="modal-header">
        <h4 class="modal-title">Send Notification List</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
    	<div id="send_notification_list">
	    </div>
    </div>
  </div>
</div>

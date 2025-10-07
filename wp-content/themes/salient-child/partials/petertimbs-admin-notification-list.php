<?php
	global $wpdb;
	$sql = "SELECT * FROM {$wpdb->prefix}notification  Group BY time_stamp Order By time_stamp DESC";
	$notification_list = $wpdb->get_results($sql, ARRAY_A);
?>
<div class="notification-main">
	<h1>Notification List</h1>
	<table id="notification" class="notification-table">
		<thead>
			<tr>
				<td>#</td>
				<td>Title</td>
				<td>Body</td>
				<td>Scheduled/Sent</td>
				<td>Status</td>
				<td>Action</td>
			</tr>
		</thead>
		<tbody>

			<?php $i = 1; foreach ($notification_list as $notification) {
				?>
				<tr>
					<input type="hidden" name="" class="<?php echo $notification['id']; ?>" value="<?php echo $notification['time_stamp']; ?>">
					<td><?php echo $i; ?></td>
					<td><?php echo $notification['title']; ?></td>
					<td><?php echo $notification['body']; ?></td>
					<td><?php echo $notification['schedule_at']; ?></td>
					<td><?php echo $notification['type'] == 'instant' ? 'Sent' : "Scheduled"; ?></td>
					<td>
						<a href="<?php echo admin_url()."admin.php?page=peter_timbs_notification_lst&id=".$notification['time_stamp']; ?>" class="button">View</a>
						<!-- <button type="button" class="view_notification" data-id ="<?php echo $notification['time_stamp']; ?>">View</button> -->
						<!-- <button type="button" class="delete_notification" data-id ="<?php echo $notification['id']; ?>">Delete</button></td> -->
				</tr>
				<?php
				$i++;
			} ?>
		</tbody>
	</table>
</div>
<!-- The Modal -->
<div id="delete_modal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
  	<div class="modal-header">
        <h4 class="modal-title">Send Notification List</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
    	<div id="view_notification_list">
	    </div>
    </div>
  </div>
</div>
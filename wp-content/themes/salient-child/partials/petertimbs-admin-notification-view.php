<?php
	global $wpdb;
	$time_stamp = $_GET['id'];
  $sql = "SELECT * FROM {$wpdb->prefix}notification WHERE time_stamp = '$time_stamp' ORDER BY id desc ";
  $notification_list = $wpdb->get_results($sql, ARRAY_A);
?>
<div class="notification-main">
	<h1>Notification View</h1>
	<table id="notification" class="notification-table">
		<thead>
			<tr>
				<td>#</td>
				<td>User Name</td>
				<td>Status</td>
				<td>Action</td>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1; foreach ($notification_list as $notification) {
				$id = $notification['id'];
				$user_name = get_user_by( 'id', $notification['user_id'] )->display_name;
        $user_email = get_user_by( 'id', $notification['user_id'] )->user_email;
				?>
				<tr>
					<input type="hidden" name="" class="<?php echo $notification['id']; ?>" value="<?php echo $notification['time_stamp']; ?>">
					<td><?php echo $i; ?></td>
					<td>&lt;<?php echo $user_name; ?>&gt; <?php echo $user_email; ?></td>
					<td><?php echo $notification['is_sent'] == 1 ? 'Customer has received' : "Customer hasn't received due to being logged out"; ?></td>
					<td>
						<button type='button' class='delete_notification' data-id = "<?php echo $notification['id']; ?>">Delete</button>
				</tr>
				<?php
				$i++;
			} ?>
		</tbody>
			<button type='button' class='all_delete_notification button' data-id = "<?php echo $id; ?>">All Delete</button>
			<a href="<?php echo admin_url()."admin.php?page=peter_timbs_notification_lst"; ?>" class="button">Back</a>
	</table>
</div>
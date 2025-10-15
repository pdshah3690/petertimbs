<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://feathertechlabs.com
 * @since      1.0.0
 *
 * @package    Splashsms
 * @subpackage Splashsms/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div id="" class="petertimbs-settings-container">
	<form method="POST" action="options.php" enctype="multipart/form-data">
		<?php
			settings_fields($this->name."_gss");
			do_settings_sections('pt-gss-settings');
			submit_button();
		?>
	</form>
</div>

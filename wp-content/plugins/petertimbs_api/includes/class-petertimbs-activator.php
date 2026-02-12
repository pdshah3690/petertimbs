<?php

/**
 * Fired during plugin activation
 *
 * @link       https://feathertechlabs.com
 * @since      1.0.0
 *
 * @package    Petertimbs
 * @subpackage Petertimbs/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Petertimbs
 * @subpackage Petertimbs/includes
 * @author     FeatherTechlabs <PluginDev@FeatherTechlabs.com>
 */
class Petertimbs_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
        global $table_prefix,$wpdb;
        $tblname = 'notification';
        $wp_track_table = $table_prefix . "$tblname";

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phone_otp` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `phone` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
		  `otp` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
      if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table){
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}notification` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) COLLATE utf8_unicode_ci NOT NULL,
          `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `body` TEXT NOT NULL,
          `schedule_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `is_sent` BOOLEAN NOT NULL DEFAULT 0,
          `type` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
          `is_seen` BOOLEAN NOT NULL DEFAULT 0,
          `product_id` varchar(255) NULL,
          `time_stamp` TIMESTAMP  NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        
        dbDelta($sql);
      }else{
        $sql = "ALTER TABLE `". $wp_track_table . "` ADD "   ;
        $sql .= " `product_id` varchar(255) NULL AFTER `is_seen`" ;
        $wpdb->query($sql);
      } 
        wp_schedule_event(strtotime('12:00 today'), 'hourly', 'send_scheduled_push_notifications');
	}
}


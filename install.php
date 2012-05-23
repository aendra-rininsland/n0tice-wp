<?php // HELLO!
		global $wpdb;
		$curation_table = $wpdb->prefix . 'n0tice_curations';
		$item_table = $wpdb->prefix . 'n0tice_items';
		$sql = "CREATE TABLE IF NOT EXISTS $curation_table (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`name` tinytext NOT NULL,
		`description` text,		
		`created` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		`modified` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
		UNIQUE KEY (id)
		);";
		
		$sql2 = "CREATE TABLE IF NOT EXISTS $item_table (
		`item_id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`headline` text NOT NULL,
		`url` text NOT NULL,
		`noticeboard` tinytext NOT NULL,
		`type` tinytext NOT NULL,
		`created` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
		`order` mediumint(9) NOT NULL,
		`curation_id` mediumint(9) NOT NULL,
		UNIQUE KEY (item_id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		dbDelta($sql2);

		update_option( "n0tice_db_version", self::$pluginVersion);
?>
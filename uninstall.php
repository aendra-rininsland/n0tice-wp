<?php
/* bye-bye. */

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) exit();

	global $wpdb;
	$curation_table = $wpdb->prefix . 'n0tice_curations';
	$item_table = $wpdb->prefix . 'n0tice_items';
	$sql = "DROP TABLE IF EXISTS $curation_table;"; 
	$sql2 = "DROP TABLE IF EXISTS $item_table;";
	$wpdb->query($sql);
	$wpdb->query($sql2);
?>
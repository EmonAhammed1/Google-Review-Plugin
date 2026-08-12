<?php
defined('ABSPATH') or die('No script kiddies please!');
foreach ($this->get_option_names() as $optName) {
delete_option($this->get_option_name($optName));
}
global $wpdb;
$tiReviewsTableName = $this->get_tablename('reviews');
$tiViewsTableName = $this->get_tablename('views');
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'schema.php';
foreach (array_keys($ti_db_schema) as $tableName) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $this->get_tablename($tableName)));
}
?>

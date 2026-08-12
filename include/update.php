<?php
defined('ABSPATH') or die('No script kiddies please!');
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
global $wpdb;
if (version_compare($this->getVersion(), $this->getVersion('update-version-check'))) {
$tableName = $this->get_tablename('reviews');
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$results = $wpdb->get_results($wpdb->prepare('SHOW COLUMNS FROM %i', $tableName), ARRAY_A);
$columns = array_column($results, 'Field');

if (!in_array('highlight', $columns)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('ALTER TABLE %i ADD highlight VARCHAR(11) NULL AFTER rating', $tableName));
}

if (!in_array('reply', $columns)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('ALTER TABLE %i ADD reply TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER date', $tableName));
}
if (in_array('replied', $columns)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('ALTER TABLE %i DROP replied', $tableName));
}
if (!in_array('reviewId', $columns)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('ALTER TABLE %i ADD reviewId TEXT NULL AFTER date', $tableName));
}

if (!in_array('hidden', $columns)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query($wpdb->prepare('ALTER TABLE %i ADD hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER id', $tableName));
}
$oldRateUs = get_option('trustindex-'. $this->getShortName() .'-rate-us');
if ($oldRateUs) {
if ($oldRateUs === 'hide') {
$this->setNotificationParam('rate-us', 'hidden', true);
}
else {
$this->setNotificationParam('rate-us', 'active', true);
$this->setNotificationParam('rate-us', 'timestamp', $oldRateUs);
}
}
$oldNotificationEmail = get_option('trustindex-'. $this->getShortName() .'-review-download-notification-email');
if ($oldNotificationEmail) {
$this->setNotificationParam('review-download-finished', 'email', $oldNotificationEmail);
}
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$results = $wpdb->get_results($wpdb->prepare('SELECT option_name FROM %i WHERE option_name LIKE %s', $wpdb->options, 'trustindex-'.$this->getShortName().'-%'), ARRAY_A);
$optionNamesInDb = array_column($results, 'option_name');
$usedOptionNames = [];
foreach ($this->get_option_names() as $optName) {
$usedOptionNames []= $this->get_option_name($optName);
}
foreach ($optionNamesInDb as $optName) {
if (!in_array($optName, $usedOptionNames)) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->delete($wpdb->options, ['option_name' => $optName]);
}
}
if (get_option($this->get_option_name('css-content'))) {
$cssCdnVersion = $this->getCdnVersion('widget-css');
if ($cssCdnVersion && version_compare($cssCdnVersion, $this->getVersion('widget-css'))) {
$this->noreg_save_css(true);
$this->updateVersion('widget-css', $cssCdnVersion);
}
}
if (get_option($this->get_option_name('review-content'))) {
$htmlCdnVersion = $this->getCdnVersion('widget-html');
if ($htmlCdnVersion && version_compare($htmlCdnVersion, $this->getVersion('widget-html'))) {
delete_option($this->get_option_name('review-content'));
$this->updateVersion('widget-html', $htmlCdnVersion);
}
}
if (!$this->is_table_exists('views')) {
$tiReviewsTableName = $this->get_tablename('reviews');
$tiViewsTableName = $this->get_tablename('views');
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'schema.php';
try {
dbDelta(trim($ti_db_schema['views']));
} catch (Exception $e) { }
}
$this->updateVersion('update-version-check', $this->getVersion());
}
?>

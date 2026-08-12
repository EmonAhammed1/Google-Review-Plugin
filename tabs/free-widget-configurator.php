<?php
defined('ABSPATH') or die('No script kiddies please!');
$ti_command = isset($_REQUEST['command']) ? sanitize_text_field(wp_unslash($_REQUEST['command'])) : null;
$ti_command_list = [
'save-page',
'delete-page',
'save-style',
'save-filter-stars',
'save-set',
'save-language',
'save-dateformat',
'save-nameformat',
'save-top-rated-type',
'save-top-rated-date',
'save-options',
'save-align',
'save-fomo-icon',
'save-fomo-color',
'save-fomo-margin',
'save-fomo-title',
'save-fomo-text',
'save-fomo-url',
'save-fomo-day',
'save-fomo-hide-count',
'save-review-text-mode',
'save-verified-by-trustindex',
'save-amp-notice-hide',

];
if (!in_array($ti_command, $ti_command_list)) {
$ti_command = null;
}
function trustindex_plugin_disconnect_page($settingsDelete = true)
{
global $pluginManagerInstance;
global $wpdb;
$pluginManagerInstance->delete_async_request();
delete_option($pluginManagerInstance->get_option_name('review-download-inprogress'));
delete_option($pluginManagerInstance->get_option_name('review-download-request-id'));
delete_option($pluginManagerInstance->get_option_name('review-manual-download'));
delete_option($pluginManagerInstance->get_option_name('page-details'));
delete_option($pluginManagerInstance->get_option_name('review-content'));
delete_option($pluginManagerInstance->get_option_name('css-content'));
if (is_file($pluginManagerInstance->getCssFile())) {
wp_delete_file($pluginManagerInstance->getCssFile());
}
if ($settingsDelete) {
delete_option($pluginManagerInstance->get_option_name('style-id'));
delete_option($pluginManagerInstance->get_option_name('scss-set'));
delete_option($pluginManagerInstance->get_option_name('filter'));
delete_option($pluginManagerInstance->get_option_name('lang'));
delete_option($pluginManagerInstance->get_option_name('dateformat'));
delete_option($pluginManagerInstance->get_option_name('nameformat'));
delete_option($pluginManagerInstance->get_option_name('no-rating-text'));
delete_option($pluginManagerInstance->get_option_name('verified-icon'));
delete_option($pluginManagerInstance->get_option_name('enable-animation'));
delete_option($pluginManagerInstance->get_option_name('show-arrows'));
delete_option($pluginManagerInstance->get_option_name('show-header-button'));
delete_option($pluginManagerInstance->get_option_name('reviews-load-more'));
delete_option($pluginManagerInstance->get_option_name('show-reviewers-photo'));
delete_option($pluginManagerInstance->get_option_name('widget-setted-up'));
delete_option($pluginManagerInstance->get_option_name('show-review-replies'));
delete_option($pluginManagerInstance->get_option_name('verified-by-trustindex'));
}
$wpdb->query($wpdb->prepare('TRUNCATE %i', $pluginManagerInstance->get_tablename('reviews')));
$pluginManagerInstance->setNotificationParam('not-using-no-connection', 'active', true);
$pluginManagerInstance->setNotificationParam('not-using-no-connection', 'timestamp', time() + 86400);
$pluginManagerInstance->setNotificationParam('not-using-no-widget', 'active', false);
}
function trustindex_plugin_change_step($step = 5)
{
global $pluginManagerInstance;
if ($step < 5) {
$optionsToDelete = [
'widget-setted-up',
'align',
'review-text-mode',
'verified-icon',
'enable-animation',
'no-rating-text',
'disable-font',
'show-reviewers-photo',
'show-logos',
'show-stars',
'footer-filter-text',
'top-rated-type',
'top-rated-date',
'show-arrows',
'show-header-button',
'reviews-load-more',
'dateformat',
'nameformat',
'show-review-replies',
'verified-by-trustindex',
'fomo-open',
'fomo-link',
'fomo-border',
'fomo-arrow',
'fomo-icon',
'fomo-color',
'fomo-icon-background',
'fomo-margin',
'fomo-title',
'fomo-text',
'fomo-url',
'fomo-day',
'fomo-hide-count',
];
foreach ($optionsToDelete as $name) {
delete_option($pluginManagerInstance->get_option_name($name));
}
$pluginManagerInstance->emptyViews();
}
if ($step < 4) {
delete_option($pluginManagerInstance->get_option_name('scss-set'));
}
if ($step < 3) {
delete_option($pluginManagerInstance->get_option_name('style-id'));
}
if ($step < 2) {
trustindex_plugin_disconnect_page();
}
}
if ($ti_command === 'save-page') {
check_admin_referer('ti-save-page');
// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
$pageDetails = isset($_POST['page_details']) ? json_decode(wp_unslash($_POST['page_details']), true) : null;
$reviewDownload = isset($_POST['review_download']) ? sanitize_text_field(wp_unslash($_POST['review_download'])) : 0;
if ($pageDetails['name']) {
$pageDetails['name'] = json_encode($pageDetails['name']);
}
$pluginManagerInstance->setNotificationParam('not-using-no-connection', 'active', false);
$pluginManagerInstance->setNotificationParam('not-using-no-widget', 'active', true);
$pluginManagerInstance->setNotificationParam('not-using-no-widget', 'timestamp', time() + (2 * 3600));
$tableName = $pluginManagerInstance->get_tablename('reviews');
$wpdb->query($wpdb->prepare('TRUNCATE %i', $tableName));
$reviews = null;
if (isset($pageDetails['reviews'])) {
$reviews = $pageDetails['reviews'];
unset($pageDetails['reviews']);
}
$requestId = null;
if (isset($pageDetails['request_id'])) {
$requestId = $pageDetails['request_id'];
unset($pageDetails['request_id']);
}
else if (isset($_REQUEST['review_request_id'])) {
$requestId = sanitize_text_field(wp_unslash($_REQUEST['review_request_id']));
}
if ($requestId) {
update_option($pluginManagerInstance->get_option_name('review-download-request-id'), $requestId, false);
}
$manualDownload = 0;
if (isset($pageDetails['manual_download'])) {
$manualDownload = (int)$pageDetails['manual_download'];
unset($pageDetails['manual_download']);
}
else if (isset($_REQUEST['manual_download'])) {
$manualDownload = (int)$_REQUEST['manual_download'];
}
delete_option($pluginManagerInstance->get_option_name('review-download-token'));
if ($reviewDownload) {
update_option($pluginManagerInstance->get_option_name('review-download-inprogress'), $reviewDownload, false);
update_option($pluginManagerInstance->get_option_name('review-manual-download'), $manualDownload, false);
update_option($pluginManagerInstance->get_option_name('review-download-is-connecting'), 1, false);
}
else {
delete_option($pluginManagerInstance->get_option_name('review-download-inprogress'));
delete_option($pluginManagerInstance->get_option_name('review-manual-download'));
delete_option($pluginManagerInstance->get_option_name('review-download-is-connecting'));
}
if (is_array($reviews)) {
foreach ($reviews as $row) {
$date = isset($row['created_at']) ? $row['created_at'] : (isset($row['date']) ? $row['date'] : '');
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
$wpdb->insert($tableName, [
'user' => $row['reviewer']['name'],
'user_photo' => $row['reviewer']['avatar_url'],
'text' => $row['text'],
'rating' => $row['rating'] ? $row['rating'] : 5,
'date' => substr($date, 0, 10),
'reviewId' => isset($row['id']) ? $row['id'] : null,
'reply' => isset($row['reply']) ? $row['reply'] : ""
]);
}
update_option($pluginManagerInstance->get_option_name('download-timestamp'), time() + (86400 * 10), false);
delete_option($pluginManagerInstance->get_option_name('review-download-inprogress'));
delete_option($pluginManagerInstance->get_option_name('review-manual-download'));
update_option($pluginManagerInstance->get_option_name('review-download-modal'), 0, false);
}
update_option($pluginManagerInstance->get_option_name('page-details'), $pageDetails, false);
$GLOBALS['wp_object_cache']->delete( $pluginManagerInstance->get_option_name('page-details'), 'options' );
$lang = strtolower(substr(get_locale(), 0, 2));
if (!isset($pluginManager::$widget_languages[ $lang ])) {
$lang = 'en';
}
update_option($pluginManagerInstance->get_option_name('lang'), $lang, false);
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
exit;
}
else if ($ti_command === 'delete-page') {
check_admin_referer('ti-delete-page');
trustindex_plugin_disconnect_page();
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
exit;
}
else if ($ti_command === 'save-style') {
check_admin_referer('ti-save-style');
$styleId = isset($_REQUEST['style_id']) ? (int)$_REQUEST['style_id'] : 4;
if (14 !== $styleId) {
update_option($pluginManagerInstance->get_option_name('style-id'), $styleId, false);
delete_option($pluginManagerInstance->get_option_name('review-content'));
trustindex_plugin_change_step(3);
if (in_array($pluginManager::$widget_templates['templates'][$styleId]['type'], ['floating', 'fomo'])) {
$pluginManagerInstance->noreg_save_css();
}
}
if (isset($_GET['style_id'])) {
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
}
exit;
}
else if ($ti_command === 'save-set') {
check_admin_referer('ti-save-set');
$setId = isset($_REQUEST['set_id']) ? sanitize_text_field(wp_unslash($_REQUEST['set_id'])) : 'light-background';
update_option($pluginManagerInstance->get_option_name('scss-set'), $setId, false);
trustindex_plugin_change_step(4);
$pluginManagerInstance->noreg_save_css(true);
if (isset($_GET['verified_by_trustindex'])) {
update_option($pluginManagerInstance->get_option_name('verified-by-trustindex'), 1, false);
}
if (isset($_GET['set_id'])) {
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
}
exit;
}
else if ($ti_command === 'save-filter-stars') {
check_admin_referer('ti-save-filter-stars');
$filter = $pluginManagerInstance->getWidgetOption('filter');
$filter['stars'] = isset($_POST['stars']) ? explode(',', sanitize_text_field(wp_unslash($_POST['stars']))) : $pluginManagerInstance->getWidgetOption('filter', false, true)['stars'];
update_option($pluginManagerInstance->get_option_name('filter'), $filter, false);
exit;
}
else if ($ti_command === 'save-language') {
check_admin_referer('ti-save-language');
$value = isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : 'en';
update_option($pluginManagerInstance->get_option_name('lang'), $value, false);
delete_option($pluginManagerInstance->get_option_name('review-content'));
if ($pluginManagerInstance->isRtlLanguage()) {
update_option($pluginManagerInstance->get_option_name('align'), 'right', false);
}
exit;
}
else if ($ti_command === 'save-dateformat') {
check_admin_referer('ti-save-dateformat');
$value = isset($_POST['dateformat']) ? sanitize_text_field(wp_unslash($_POST['dateformat'])) : '';
update_option($pluginManagerInstance->get_option_name('dateformat'), $value, false);
exit;
}
else if ($ti_command === 'save-nameformat') {
check_admin_referer('ti-save-nameformat');
$value = isset($_POST['nameformat']) ? sanitize_text_field(wp_unslash($_POST['nameformat'])) : '';
update_option($pluginManagerInstance->get_option_name('nameformat'), $value, false);
exit;
}
else if ($ti_command === 'save-top-rated-type') {
check_admin_referer('ti-save-top-rated-type');
$value = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
update_option($pluginManagerInstance->get_option_name('top-rated-type'), $value, false);
exit;
}
else if ($ti_command === 'save-top-rated-date') {
check_admin_referer('ti-save-top-rated-date');
$value = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
update_option($pluginManagerInstance->get_option_name('top-rated-date'), $value, false);
exit;
}
else if ($ti_command === 'save-options') {
$scssSet = get_option($pluginManagerInstance->get_option_name('scss-set'));
check_admin_referer('ti-save-options');
$r = 0;
if (isset($_POST['verified-icon'])) {
$r = sanitize_text_field(wp_unslash($_POST['verified-icon']));
}
update_option($pluginManagerInstance->get_option_name('verified-icon'), $r, false);
$r = 1;
if (isset($_POST['enable-animation'])) {
$r = sanitize_text_field(wp_unslash($_POST['enable-animation']));
}
update_option($pluginManagerInstance->get_option_name('enable-animation'), $r, false);
$r = 1;
if (isset($_POST['show-arrows'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-arrows']));
}
update_option($pluginManagerInstance->get_option_name('show-arrows'), $r, false);
$r = 1;
if (isset($_POST['show-header-button'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-header-button']));
}
update_option($pluginManagerInstance->get_option_name('show-header-button'), $r, false);
$r = 1;
if (isset($_POST['reviews-load-more'])) {
$r = sanitize_text_field(wp_unslash($_POST['reviews-load-more']));
}
update_option($pluginManagerInstance->get_option_name('reviews-load-more'), $r, false);
$r = 1;
if (isset($_POST['show-reviewers-photo'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-reviewers-photo']));
}
update_option($pluginManagerInstance->get_option_name('show-reviewers-photo'), $r, false);
$r = 0;
if (isset($_POST['no-rating-text'])) {
$r = sanitize_text_field(wp_unslash($_POST['no-rating-text']));
}
update_option($pluginManagerInstance->get_option_name('no-rating-text'), $r, false);
$r = 0;
if (isset($_POST['disable-font'])) {
$r = sanitize_text_field(wp_unslash($_POST['disable-font']));
}
update_option($pluginManagerInstance->get_option_name('disable-font'), $r, false);
$r = 1;
if (isset($_POST['show-logos'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-logos']));
}
update_option($pluginManagerInstance->get_option_name('show-logos'), $r, false);
$r = 1;
if (isset($_POST['show-stars'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-stars']));
}
update_option($pluginManagerInstance->get_option_name('show-stars'), $r, false);
$r = 0;
if (isset($_POST['footer-filter-text'])) {
$r = sanitize_text_field(wp_unslash($_POST['footer-filter-text']));
}
update_option($pluginManagerInstance->get_option_name('footer-filter-text'), $r, false);
$r = 0;
if (isset($_POST['show-review-replies'])) {
$r = sanitize_text_field(wp_unslash($_POST['show-review-replies']));
}
update_option($pluginManagerInstance->get_option_name('show-review-replies'), $r, false);
$filter = $pluginManagerInstance->getWidgetOption('filter');
$filter['only-ratings'] = isset($_POST['only-ratings']) ? (bool)$_POST['only-ratings'] : $pluginManagerInstance->getWidgetOption('filter', false, true)['only-ratings'];
update_option($pluginManagerInstance->get_option_name('filter'), $filter, false);
$r = 1;
if (isset($_POST['fomo-open'])) {
$r = sanitize_text_field(wp_unslash($_POST['fomo-open']));
}
update_option($pluginManagerInstance->get_option_name('fomo-open'), $r, false);
$r = 0;
if (isset($_POST['fomo-link'])) {
$r = sanitize_text_field(wp_unslash($_POST['fomo-link']));
}
update_option($pluginManagerInstance->get_option_name('fomo-link'), $r, false);
$r = 1;
if (isset($_POST['fomo-border'])) {
$r = sanitize_text_field(wp_unslash($_POST['fomo-border']));
}
update_option($pluginManagerInstance->get_option_name('fomo-border'), $r, false);
$r = 1;
if (isset($_POST['fomo-arrow'])) {
$r = sanitize_text_field(wp_unslash($_POST['fomo-arrow']));
}
update_option($pluginManagerInstance->get_option_name('fomo-arrow'), $r, false);
$r = 0;
if (isset($_POST['fomo-icon-background'])) {
$r = sanitize_text_field(wp_unslash($_POST['fomo-icon-background']));
}
update_option($pluginManagerInstance->get_option_name('fomo-icon-background'), $r, false);
exit;
}
else if ($ti_command === 'save-align') {
check_admin_referer('ti-save-align');
$value = isset($_POST['align']) ? sanitize_text_field(wp_unslash($_POST['align'])) : '';
update_option($pluginManagerInstance->get_option_name('align'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-icon') {
check_admin_referer('ti-save-fomo-icon');
$value = isset($_POST['fomo-icon']) ? sanitize_text_field(wp_unslash($_POST['fomo-icon'])) : '';
update_option($pluginManagerInstance->get_option_name('fomo-icon'), $value, false);
if ('hide' === $value) {
update_option($pluginManagerInstance->get_option_name('fomo-open'), 1, false);
}
exit;
}
else if ($ti_command === 'save-fomo-color') {
check_admin_referer('ti-save-fomo-color');
$value = isset($_POST['fomo-color']) ? sanitize_text_field(wp_unslash($_POST['fomo-color'])) : '';
update_option($pluginManagerInstance->get_option_name('fomo-color'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-margin') {
check_admin_referer('ti-save-fomo-margin');
$value = isset($_POST['fomo-margin']) ? sanitize_text_field(wp_unslash($_POST['fomo-margin'])) : '';
update_option($pluginManagerInstance->get_option_name('fomo-margin'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-title') {
check_admin_referer('ti-save-fomo-title');
$value = isset($_POST['fomo-title']) ? sanitize_text_field(wp_unslash($_POST['fomo-title'])) : '';
update_option($pluginManagerInstance->get_option_name('fomo-title'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-text') {
check_admin_referer('ti-save-fomo-text');
$value = isset($_POST['fomo-text']) ? sanitize_text_field(wp_unslash($_POST['fomo-text'])) : '';
update_option($pluginManagerInstance->get_option_name('fomo-text'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-url') {
check_admin_referer('ti-save-fomo-url');
$value = isset($_POST['fomo-url']) ? sanitize_text_field(wp_unslash($_POST['fomo-url'])) : '';
if ($value) {
update_option($pluginManagerInstance->get_option_name('fomo-url'), $value, false);
} else {
delete_option($pluginManagerInstance->get_option_name('fomo-url'));
}
exit;
}
else if ($ti_command === 'save-fomo-day') {
check_admin_referer('ti-save-fomo-day');
$value = isset($_POST['fomo-day']) ? (int)sanitize_text_field(wp_unslash($_POST['fomo-day'])) : 0;
update_option($pluginManagerInstance->get_option_name('fomo-day'), $value, false);
exit;
}
else if ($ti_command === 'save-fomo-hide-count') {
check_admin_referer('ti-save-fomo-hide-count');
$value = isset($_POST['fomo-hide-count']) ? (int)sanitize_text_field(wp_unslash($_POST['fomo-hide-count'])) : 0;
update_option($pluginManagerInstance->get_option_name('fomo-hide-count'), $value, false);
exit;
}
else if ($ti_command === 'save-review-text-mode') {
check_admin_referer('ti-save-review-text-mode');
$value = isset($_POST['review_text_mode']) ? sanitize_text_field(wp_unslash($_POST['review_text_mode'])) : '';
update_option($pluginManagerInstance->get_option_name('review-text-mode'), $value, false);
exit;
}
else if ($ti_command === 'save-verified-by-trustindex') {
check_admin_referer('ti-save-verified-by-trustindex');
$value = isset($_POST['verified-by-trustindex']) ? sanitize_text_field(wp_unslash($_POST['verified-by-trustindex'])) : '';
update_option($pluginManagerInstance->get_option_name('verified-by-trustindex'), $value, false);
exit;
}
else if ($ti_command === 'save-amp-notice-hide') {
update_option($pluginManagerInstance->get_option_name('amp-hidden-notification'), 1, false);
exit;
}
if (isset($_GET['recreate'])) {
check_admin_referer('ti-recreate');
$pluginManagerInstance->uninstall();
$pluginManagerInstance->activate();
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
exit;
}
if (isset($_GET['setup_widget'])) {
check_admin_referer('ti-setup-widget');
update_option($pluginManagerInstance->get_option_name('widget-setted-up'), 1, false);
header('Location: admin.php?page='.esc_attr($_page).'&tab=free-widget-configurator');
}
$reviews = $pluginManagerInstance->getReviews();
$isReviewDownloadInProgress = $pluginManagerInstance->is_review_download_in_progress();
$styleId = (int)$pluginManagerInstance->getWidgetOption('style-id', true);
$scssSet = $pluginManagerInstance->getWidgetOption('scss-set', true);
$widgetSettedUp = $pluginManagerInstance->getWidgetOption('widget-setted-up');
if (!$pluginManagerInstance->is_noreg_linked()) {
$styleId = null;
$scssSet = null;
$widgetSettedUp = null;
} else {
$pageDetails = $pluginManagerInstance->getPageDetails();
$isTopRatedBadge = $styleId ? $pluginManager::$widget_templates['templates'][$styleId]['is-top-rated-badge'] : false;
if ($isTopRatedBadge) {
$isTopRatedBadgeValid = isset($pageDetails['rating_score']) ? (float)$pageDetails['rating_score'] >= $pluginManager::$topRatedMinimumScore : false;
}
}
wp_enqueue_style('trustindex-widget-preview-css', 'https://cdn.trustindex.io/assets/ti-preview-box.css', [], true);
?>
<?php
$stepUrl = '?page='.esc_attr($_page).'&tab=free-widget-configurator&step=%step%';
$stepList = [
/* translators: %s: Google */
sprintf(__('Connect %s', 'wp-reviews-plugin-for-google'), 'Google'),
__('Select Layout', 'wp-reviews-plugin-for-google'),
__('Select Style', 'wp-reviews-plugin-for-google'),
__('Set up widget', 'wp-reviews-plugin-for-google'),
__('Insert code', 'wp-reviews-plugin-for-google')
];
$stepDone = 0;
$stepCurrent = isset($_GET['step']) ? (int)sanitize_text_field(wp_unslash($_GET['step'])) : 0;
if ($stepCurrent === 3 && in_array($pluginManager::$widget_templates['templates'][$styleId]['type'], ['floating', 'fomo'])) {
$stepCurrent = 4;
}
if ($widgetSettedUp) {
$stepDone = 4;
}
else if ($scssSet) {
$stepDone = 3;
}
else if ($styleId) {
$stepDone = 2;
}
else if ($pluginManagerInstance->is_noreg_linked()) {
$stepDone = 1;
}
if ($stepDone >= 4 && $isTopRatedBadge && !$isTopRatedBadgeValid) {
$stepDone = 3;
}
if (!$stepCurrent) {
$stepCurrent = $stepDone + 1;
} else if ($stepCurrent > ($stepDone + 1)) {
$stepCurrent = $stepDone + 1;
}
include(plugin_dir_path(__FILE__) . '../include/step-list.php');
?>
<div class="ti-container<?php if ($stepCurrent < 5): ?> ti-narrow-page<?php endif; ?><?php if (5 === $stepCurrent): ?> ti-insert-code-page<?php endif; ?>">
<?php if ($pluginManagerInstance->is_trustindex_connected()): ?>
<div class="ti-notice ti-notice-warning">
<p>
<?php
$advancedTab = '<a href="?page='.esc_attr($_page).'&tab=advanced#trustindex-admin">'.__('Advanced', 'wp-reviews-plugin-for-google').'</a>';
/* translators: %s: Advanced tab link */
echo wp_kses_post(sprintf(__("You have connected your Trustindex account, so you can find premium functionality under the %s tab. You no longer need this tab unless you choose the limited but forever free mode.", 'wp-reviews-plugin-for-google'), $advancedTab));
?>
</p>
</div>
<?php endif; ?>

<?php if ($pluginManager::is_amp_active() && !get_option($pluginManagerInstance->get_option_name('amp-hidden-notification'), 0)): ?>
<div class="ti-notice ti-notice-warning is-dismissible">
<p>
<?php echo esc_html(__('Free plugin features are unavailable with AMP plugin.', 'wp-reviews-plugin-for-google')); ?>
<?php if ($pluginManagerInstance->is_trustindex_connected()): ?>
 <a href="?page=<?php echo esc_attr($_page); ?>&tab=advanced">Trustindex admin</a>
<?php else: ?>
 <a href="https://www.trustindex.io/?a=sys&c=wp-amp" target="_blank"><?php echo esc_html(__('Try premium features (like AMP) for free', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</p>
<button type="button" class="notice-dismiss" data-command="save-amp-notice-hide"></button>
</div>
<?php endif; ?>
<?php if ($stepCurrent === 1): ?>
<h1 class="ti-header-title"><?php
/* translators: %s: Google */
echo esc_html(sprintf(__('Connect %s', 'wp-reviews-plugin-for-google'), 'Google'));
?></h1>
<?php if ($pluginManagerInstance->is_noreg_linked()): ?>
<div class="ti-source-box">
<?php if (isset($pageDetails['avatar_url']) && $pageDetails['avatar_url']): ?>
<img src="<?php echo esc_url($pageDetails['avatar_url']); ?>" />
<?php endif; ?>
<div class="ti-source-info">
<?php if (isset($pageDetails['name']) && $pageDetails['name']): ?>
<strong><?php echo esc_html($pageDetails['name']); ?></strong><br />
<?php endif; ?>
<?php if (isset($pageDetails['address']) && $pageDetails['address']): ?>
<?php echo esc_html($pageDetails['address']); ?><br />
<?php endif; ?>
<a href="<?php echo esc_url($pluginManagerInstance->getPageUrl()); ?>" target="_blank"><?php echo esc_url($pluginManagerInstance->getPageUrl()); ?></a>
</div>
<a href="<?php echo esc_url(wp_nonce_url('?page='. esc_attr($_page) .'&tab=free-widget-configurator&command=delete-page', 'ti-delete-page')); ?>" class="ti-btn ti-btn-primary ti-btn-loading-on-click"><?php echo esc_html(__('Disconnect', 'wp-reviews-plugin-for-google')); ?></a>
</div>
<?php else: ?>
<div class="ti-box">
<form method="post" action="" data-platform="google" id="ti-connect-platform-form">
<?php wp_nonce_field('ti-save-page'); ?>
<input type="hidden" name="command" value="save-page" />
<input type="hidden" name="page_details" required="required" id="ti-noreg-page-details" value="" />
<?php
$reviewDownloadToken = get_option($pluginManagerInstance->get_option_name('review-download-token'));
if (!$reviewDownloadToken) {
$reviewDownloadToken = wp_create_nonce('ti-noreg-connect-token');
update_option($pluginManagerInstance->get_option_name('review-download-token'), $reviewDownloadToken, false);
}
?>
<input type="hidden" id="ti-noreg-connect-token" name="ti-noreg-connect-token" value="<?php echo esc_attr($reviewDownloadToken); ?>" />
<input type="hidden" id="ti-noreg-webhook-url" value="<?php echo esc_url($pluginManagerInstance->getWebhookUrl()); ?>" />
<input type="hidden" id="ti-noreg-email" value="<?php echo esc_attr(get_option('admin_email')); ?>" />
<input type="hidden" id="ti-noreg-version" value="<?php echo esc_attr($pluginManagerInstance->getVersion()); ?>" />
<input type="hidden" id="ti-noreg-review-download" name="review_download" value="0" />
<input type="hidden" id="ti-noreg-review-request-id" name="review_request_id" value="" />
<input type="hidden" id="ti-noreg-manual-download" name="manual_download" value=0 />
<input type="hidden" id="ti-noreg-page-id" value="" />
<div class="ti-notice ti-notice-info ti-d-none" id="ti-connect-info">
<p><?php echo esc_html(__("A popup window should be appear! Please, go to there and continue the steps! (If there is no popup window, you can check the the browser's popup blocker)", 'wp-reviews-plugin-for-google')); ?></p>
</div>

<a href="#" class="ti-btn btn-connect-public"><?php echo esc_html(__('Connect', 'wp-reviews-plugin-for-google')); ?></a>


</form>
</div>
<?php endif; ?>
<h1 class="ti-header-title ti-mt-2"><?php
/* translators: %s: Google Reviews */
echo esc_html(sprintf(__('Check some %s widget layouts and styles', 'wp-reviews-plugin-for-google'), 'Google Reviews'));
?></h1>
<?php include(plugin_dir_path(__FILE__) . '../include/demo-widgets.php'); ?>
<?php elseif ($stepCurrent === 2): ?>
<h1 class="ti-header-title"><?php echo esc_html(__('Select Layout', 'wp-reviews-plugin-for-google')); ?></h1>
<?php if (!count($reviews) && !$isReviewDownloadInProgress): ?>
<div class="ti-notice ti-notice-warning" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %s: Google */
echo esc_html(sprintf(__('There are no reviews on your %s platform.', 'wp-reviews-plugin-for-google'), 'Google'));
?>
</p>
</div>
<?php endif; ?>
<div class="ti-box ti-box-filter">
<label><?php echo esc_html(__('Layout', 'wp-reviews-plugin-for-google')); ?>:</label>
<span class="ti-checkbox">
<input type="radio" name="layout-select" value="" data-ids="" checked>
<label><?php echo esc_html(__('All', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php foreach ($pluginManager::$widget_templates['categories'] as $category => $ids): ?>
<span class="ti-checkbox">
<input type="radio" name="layout-select" value="<?php echo esc_attr($category); ?>" data-ids="<?php echo esc_attr($ids); ?>">
<label><?php
switch ($category) {
case 'slider':
echo esc_html(__('Slider', 'wp-reviews-plugin-for-google'));
break;
case 'sidebar':
echo esc_html(__('Sidebar', 'wp-reviews-plugin-for-google'));
break;
case 'list':
echo esc_html(__('List', 'wp-reviews-plugin-for-google'));
break;
case 'grid':
echo esc_html(__('Grid', 'wp-reviews-plugin-for-google'));
break;
case 'Badge':
echo esc_html(__('Badge', 'wp-reviews-plugin-for-google'));
break;
case 'Button':
echo esc_html(__('Button', 'wp-reviews-plugin-for-google'));
break;
case 'floating':
echo esc_html(__('Floating', 'wp-reviews-plugin-for-google'));
break;
case 'popup':
echo esc_html(__('Popup', 'wp-reviews-plugin-for-google'));
break;
case 'top-rated-badge':
echo esc_html(__('Top Rated Badge', 'wp-reviews-plugin-for-google'));
break;
case 'fomo':
echo esc_html(__('Fomo', 'wp-reviews-plugin-for-google'));
break;
default:
echo esc_html($category);
break;
}
?></label>
</span>
<?php endforeach; ?>
</div>
<div class="ti-preview-boxes-container">
<?php foreach ($pluginManager::$widget_templates['templates'] as $id => $template): ?>
<?php
$className = 'ti-full-width';
if (in_array($template['type'], [ 'badge', 'button', 'floating', 'popup', 'sidebar', 'top-rated-badge', 'fomo' ])) {
$className = 'ti-half-width';
}
$set = 'light-background';
if (in_array($template['type'], [ 'badge', 'button' ])) {
$set = 'drop-shadow';
}
if ($template['is-top-rated-badge']) {
$set = 'light-minimal';
if (isset($template['params']['top-rated-badge-border']) && $template['params']['top-rated-badge-border']) {
$set = 'ligth-border';
}
}
$isTopRatedBadgeValid = isset($pageDetails['rating_score']) ? (float)$pageDetails['rating_score'] >= $pluginManager::$topRatedMinimumScore : false;
$fomoWidgetInvalid = in_array($id, [119, 120]) && (!isset($pageDetails['rating_numbers']) || !isset($pageDetails['rating_numbers_last']));

if (!isset($template['is-active']) || $template['is-active']):
?>
<div class="<?php echo esc_attr($className); ?>">
<div class="ti-box ti-preview-boxes" data-layout-id="<?php echo esc_attr($id); ?>" data-set-id="<?php echo esc_attr($set); ?>">
<div class="ti-box-inner">
<div class="ti-box-header ti-box-header-normal">
<?php echo esc_html(__('Layout', 'wp-reviews-plugin-for-google')); ?>:
<strong><?php echo esc_html($template['name']); ?></strong>
<?php if ($template['is-popular'] || 14 === $id): ?>
<span class="ti-badge ti-most-popular-badge ti-tooltip">
<?php echo esc_html(__('Most popular', 'wp-reviews-plugin-for-google')); ?> <span class="dashicons dashicons-info-outline"></span>
<span class="ti-tooltip-message"><?php echo
esc_html(__('Selected by most users!', 'wp-reviews-plugin-for-google')).' '.
esc_html(__('This widget layout helps build trust and effectively increases sales.', 'wp-reviews-plugin-for-google'));
?></span>
</span>
<?php endif; ?>
<?php if (14 === $id) :?>
<a href="#" class="ti-btn ti-btn-sm ti-pull-right" style="pointer-events: none;background: grey">
<?php echo esc_html(__('Paid package feature', 'wp-reviews-plugin-for-google')); ?>
</a>
<?php elseif ((!$template['is-top-rated-badge'] || $isTopRatedBadgeValid) && !$fomoWidgetInvalid): ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='. esc_attr($_page) .'&tab=free-widget-configurator&command=save-style&style_id='. esc_attr(urlencode($id)), 'ti-save-style')); ?>" class="ti-btn ti-btn-sm ti-btn-loading-on-click ti-pull-right"><?php echo esc_html(__('Select', 'wp-reviews-plugin-for-google')); ?></a>
<div class="clear"></div>
<?php endif; ?>
</div>
<div class="preview">
<?php if ($template['is-top-rated-badge'] && !$isTopRatedBadgeValid): ?>
<div class="ti-notice ti-notice-info" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %d: minimum score (4.5) */
echo esc_html(sprintf(__('Our exclusive "Top Rated" badge is awarded to service providers with a rating of %s and above.', 'wp-reviews-plugin-for-google'), $pluginManager::$topRatedMinimumScore));
?><br />
</p>
</div>
<?php endif; ?>
<?php if ($fomoWidgetInvalid): ?>
<div class="ti-notice ti-notice-info" style="margin: 0 0 15px 0">
<p>
<?php echo esc_html(__('Update your reviews to use this widget.', 'wp-reviews-plugin-for-google')); ?><br />
</p>
</div>
<?php endif; ?>
<?php echo wp_kses($pluginManagerInstance->renderWidgetAdmin(true, false, ['style-id' => esc_attr($id), 'set-id' => esc_attr($set)]), $pluginManager::$allowedAttributesForWidget); ?>
</div>
</div>
</div>
</div>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php elseif ($stepCurrent === 3): ?>
<h1 class="ti-header-title"><?php echo esc_html(__('Select Style', 'wp-reviews-plugin-for-google')); ?></h1>
<?php if (!count($reviews) && !$isReviewDownloadInProgress): ?>
<div class="ti-notice ti-notice-warning" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %s: Google */
echo esc_html(sprintf(__('There are no reviews on your %s platform.', 'wp-reviews-plugin-for-google'), 'Google'));
?>
</p>
</div>
<?php endif; ?>
<?php
$className = 'ti-full-width';
if (in_array($pluginManager::$widget_templates['templates'][ $styleId ]['type'], [ 'badge', 'button', 'floating', 'popup', 'sidebar', 'top-rated-badge' ])) {
$className = 'ti-half-width';
}
?>
<div class="ti-preview-boxes-container">
<?php
$isFirstLayout = true;
$isVerifiedByTrustindexAvailable = $pluginManagerInstance->isVerifiedByTrustindexAvailable();
foreach ($pluginManager::$widget_styles as $id => $style): ?>
<?php if (!isset($style['is-active']) || $style['is-active']): ?>
<div class="<?php echo esc_attr($className); ?>">
<div class="ti-box ti-preview-boxes" data-layout-id="<?php echo esc_attr($styleId); ?>" data-set-id="<?php echo esc_attr($id); ?>">
<div class="ti-box-inner">
<div class="ti-box-header ti-box-header-normal">
<?php echo esc_html(__('Style', 'wp-reviews-plugin-for-google')); ?>:
<strong><?php echo esc_html($style['name']); ?></strong>
<?php if ($isFirstLayout && !$isVerifiedByTrustindexAvailable): ?>
<span class="ti-badge ti-most-popular-badge ti-tooltip">
<?php echo esc_html(__('Most popular', 'wp-reviews-plugin-for-google')); ?> <span class="dashicons dashicons-info-outline"></span>
<span class="ti-tooltip-message"><?php echo
esc_html(__('Selected by most users!', 'wp-reviews-plugin-for-google')).' '.
esc_html(__('This widget style helps build trust and effectively increases sales.', 'wp-reviews-plugin-for-google'));
?></span>
</span>
<?php endif; ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='. esc_attr($_page) .'&tab=free-widget-configurator&command=save-set&set_id='. esc_attr(urlencode($id)), 'ti-save-set')); ?>" class="ti-btn ti-btn-sm ti-btn-loading-on-click ti-pull-right"><?php echo esc_html(__('Select', 'wp-reviews-plugin-for-google')); ?></a>
<div class="clear"></div>
</div>
<div class="preview">
<?php echo wp_kses($pluginManagerInstance->renderWidgetAdmin(true, false, ['style-id' => esc_attr($styleId), 'set-id' => esc_attr($id)]), $pluginManager::$allowedAttributesForWidget); ?>
</div>
</div>
</div>
</div>
<?php if ($id === 'light-background' && $isVerifiedByTrustindexAvailable): ?>
<div class="<?php echo esc_attr($className); ?>">
<div class="ti-box ti-preview-boxes" data-layout-id="<?php echo esc_attr($styleId); ?>" data-set-id="<?php echo esc_attr($id); ?>">
<div class="ti-box-inner">
<div class="ti-box-header ti-box-header-normal">
<?php echo esc_html(__('Style', 'wp-reviews-plugin-for-google')); ?>:
<strong>
<?php echo esc_html($style['name']); ?>
-
<?php echo esc_html(__('with Trustindex verified', 'wp-reviews-plugin-for-google')); ?>
</strong>
<?php if ($className === 'ti-half-width'): ?><br /><?php endif; ?>
<span class="ti-badge ti-most-popular-badge ti-tooltip">
<?php echo esc_html(__('Most popular', 'wp-reviews-plugin-for-google')); ?> <span class="dashicons dashicons-info-outline"></span>
<span class="ti-tooltip-message"><?php echo
esc_html(__('Selected by most users!', 'wp-reviews-plugin-for-google')).' '.
esc_html(__('This widget style helps build trust and effectively increases sales.', 'wp-reviews-plugin-for-google'));
?></span>
</span>
<a href="<?php echo esc_url(wp_nonce_url('?page='. esc_attr($_page) .'&tab=free-widget-configurator&command=save-set&set_id='. esc_attr(urlencode($id)), 'ti-save-set')); ?>&verified_by_trustindex" class="ti-btn ti-btn-sm ti-btn-loading-on-click ti-pull-right"><?php echo esc_html(__('Select', 'wp-reviews-plugin-for-google')); ?></a>
<div class="clear"></div>
</div>
<div class="preview">
<?php echo wp_kses($pluginManagerInstance->renderWidgetAdmin(true, false, ['style-id' => esc_attr($styleId), 'set-id' => esc_attr($id), 'verified-by-trustindex' => true]), $pluginManager::$allowedAttributesForWidget); ?>
</div>
</div>
<div class="ti-notice ti-notice-info ti-verified-badge-notice">
<p>
<span class="dashicons dashicons-star-empty"></span> <strong><?php echo esc_html(__('Congratulations!', 'wp-reviews-plugin-for-google')); ?></strong><br />
<?php
/* translators: 1: 5, 2: 4.5, 3: 12 */
echo esc_html(sprintf(__('Our system ranked you in the top %1$d%% of companies based on your reviews. Your total rating score above %2$s in the last %3$d month, and your reviews are genuine', 'wp-reviews-plugin-for-google'), 5, $pluginManager::$topRatedMinimumScore, 12));
?><br />
<?php echo wp_kses_post(__('This allows you to <strong>use in the widgets the Trustindex verified badge, the Universal Symbol of Trust.</strong> With the verified badge you can build more trust, and sell more!', 'wp-reviews-plugin-for-google')); ?>
</p>
</div>
</div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php
$isFirstLayout = false;
endforeach; ?>
</div>
<?php elseif ($stepCurrent === 4): ?>
<?php $widgetType = $pluginManager::$widget_templates['templates'][$styleId]['type']; ?>
<h1 class="ti-header-title"><?php echo esc_html(__('Set up widget', 'wp-reviews-plugin-for-google')); ?></h1>
<?php if (!count($reviews) && !$isReviewDownloadInProgress): ?>
<div class="ti-notice ti-notice-warning" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %s: Google */
echo esc_html(sprintf(__('There are no reviews on your %s platform.', 'wp-reviews-plugin-for-google'), 'Google'));
?>
</p>
</div>
<?php endif; ?>
<?php if ($isTopRatedBadge && !$isTopRatedBadgeValid): ?>
<div class="ti-notice ti-notice-error" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %s: min score (4.5) */
echo esc_html(sprintf(__('Our exclusive "Top Rated" badge is awarded to service providers with a rating of %s and above.', 'wp-reviews-plugin-for-google'), $pluginManager::$topRatedMinimumScore));
?><br />
<a href="?page=<?php echo esc_attr($_page); ?>&tab=free-widget-configurator&step=2" class="ti-btn ti-btn-sm ti-btn-loading-on-click" style="margin-top: 10px"><?php echo esc_html(__('Please select another widget', 'wp-reviews-plugin-for-google')); ?></a>
</p>
</div>
<?php endif; ?>
<div class="ti-preview-boxes-container">
<div class="ti-full-width">
<div class="ti-box ti-preview-boxes" data-layout-id="<?php echo esc_attr($styleId); ?>" data-set-id="<?php echo esc_attr($scssSet); ?>">
<div class="ti-box-inner">
<div class="ti-box-header">
<?php echo esc_html(__('Widget Preview', 'wp-reviews-plugin-for-google')); ?>
<?php if (!in_array($styleId, [17, 21, 52, 53, 112, 114])): ?>
<span class="ti-box-header-normal ti-pull-right">
<?php echo esc_html(__('Style', 'wp-reviews-plugin-for-google')); ?>:
<strong><?php echo esc_html($pluginManager::$widget_styles[ $scssSet ]['name']); ?></strong>
</span>
<?php endif; ?>
<span class="ti-box-header-normal ti-pull-right">
<?php echo esc_html(__('Layout', 'wp-reviews-plugin-for-google')); ?>:
<strong><?php echo esc_html($pluginManager::$widget_templates['templates'][ $styleId ]['name']); ?></strong>
</span>
</div>
<div class="preview ti-widget-editor-preview">
<?php echo wp_kses($pluginManagerInstance->renderWidgetAdmin(true), $pluginManager::$allowedAttributesForWidget); ?>
</div>
</div>
</div>
</div>
</div>
<?php $filter = $pluginManagerInstance->getWidgetOption('filter'); ?>
<div class="ti-preview-boxes-container">
<div class="ti-full-width">
<div class="ti-box">
<div class="ti-box-inner">
<div class="ti-box-header"><?php echo esc_html(__('Widget Settings', 'wp-reviews-plugin-for-google')); ?></div>
<div class="ti-left-block" id="ti-widget-selects">
<?php if ($pluginManagerInstance->isVerifiedByTrustindexAvailable()): ?>
<div class="ti-form-group">
<label>
<?php echo esc_html(__('Verified by Trustindex', 'wp-reviews-plugin-for-google')); ?>
<span class="ti-badge ti-badge-info"><?php echo esc_html(__('Recommended', 'wp-reviews-plugin-for-google')); ?></span>
</label>
<form method="post" action="">
<input type="hidden" name="command" value="save-verified-by-trustindex" />
<?php wp_nonce_field('ti-save-verified-by-trustindex'); ?>
<?php $verifiedByTrustindex = (int)$pluginManagerInstance->getWidgetOption('verified-by-trustindex'); ?>
<select class="ti-form-control" name="verified-by-trustindex">
<option value="0"<?php if (!$verifiedByTrustindex): ?> selected<?php endif; ?>><?php echo esc_html(__('Hide', 'wp-reviews-plugin-for-google')); ?></option>
<option value="1"<?php if ($verifiedByTrustindex === 1): ?> selected<?php endif; ?>><?php echo
/* translators: %d: 1 */
esc_html(sprintf(__('Style %d', 'wp-reviews-plugin-for-google'), 1));
?></option>
<option value="2"<?php if ($verifiedByTrustindex === 2): ?> selected<?php endif; ?>><?php
/* translators: %d: 2 */
echo esc_html(sprintf(__('Style %d', 'wp-reviews-plugin-for-google'), 2));
?></option>
</select>
</form>
</div>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews()): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Filter your ratings', 'wp-reviews-plugin-for-google')); ?></label>

<form method="post" action="">
<input type="hidden" name="command" value="save-filter-stars" />
<?php wp_nonce_field('ti-save-filter-stars'); ?>
<select class="ti-form-control" name="stars">
<option value="1,2,3,4,5"<?php if (count($filter['stars']) > 2): ?> selected<?php endif; ?>><?php echo esc_html(__('Show all', 'wp-reviews-plugin-for-google')); ?></option>
<option value="4,5"<?php if (count($filter['stars']) === 2): ?> selected<?php endif; ?>>&starf;&starf;&starf;&starf; - &starf;&starf;&starf;&starf;&starf;</option>
<option value="5"<?php if (count($filter['stars']) === 1): ?> selected<?php endif; ?>><?php echo esc_html(__('only', 'wp-reviews-plugin-for-google')); ?> &starf;&starf;&starf;&starf;&starf;</option>
</select>
</form>
</div>
<?php endif; ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Select language', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-language" />
<?php wp_nonce_field('ti-save-language'); ?>
<select class="ti-form-control" name="lang">
<?php foreach ($pluginManager::$widget_languages as $id => $name): ?>
<option value="<?php echo esc_attr($id); ?>" <?php echo $pluginManagerInstance->getWidgetOption('lang') == $id ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<?php if ($pluginManagerInstance->isLayoutHasReviews()): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Select date format', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-dateformat" />
<?php wp_nonce_field('ti-save-dateformat'); ?>
<select class="ti-form-control" name="dateformat">
<?php foreach ($pluginManager::$widget_dateformats as $format): ?>
<option value="<?php echo esc_attr($format); ?>" <?php echo $pluginManagerInstance->getWidgetOption('dateformat') == $format ? 'selected' : ''; ?>><?php
switch ($format) {
case 'modern':
$lang = substr(get_locale(), 0, 2);
if (!in_array($lang, array_keys($pluginManager::$widget_date_format_locales))) {
$lang = 'en';
}
$tmp = explode('|', $pluginManager::$widget_date_format_locales[$lang]);
echo esc_html(str_replace([ '%d', '%s' ], [ 2, $tmp[3] ], $tmp[0]));
break;
case 'hide':
echo esc_html(__('Hide', 'wp-reviews-plugin-for-google'));
break;
default:
echo esc_html(gmdate($format));
break;
}
?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Select name format', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-nameformat" />
<?php wp_nonce_field('ti-save-nameformat'); ?>
<select class="ti-form-control" name="nameformat">
<?php foreach ($pluginManager::$widget_nameformats as $format): ?>
<option value="<?php echo esc_attr($format['id']); ?>" <?php echo $pluginManagerInstance->getWidgetOption('nameformat') == $format['id'] ? 'selected' : ''; ?>>
<?php
if (1 === (int)$format['id']) {
echo esc_html(__('Do not format', 'wp-reviews-plugin-for-google'));
} else {
echo esc_html($pluginManagerInstance->renderNameFormat('Firstname Lastname', $format['id']));
}
?>
</option>
<?php endforeach; ?>
</select>
</form>
</div>
<?php if (!in_array($styleId, [17, 21, 52, 53, 112, 114])): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Align', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-align" />
<?php wp_nonce_field('ti-save-align'); ?>
<select class="ti-form-control" name="align">
<option value="left" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'left' ? 'selected' : ''; ?>><?php echo esc_html(__('left', 'wp-reviews-plugin-for-google')); ?></option>
<option value="center" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'center' ? 'selected' : ''; ?>><?php echo esc_html(__('center', 'wp-reviews-plugin-for-google')); ?></option>
<option value="right" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'right' ? 'selected' : ''; ?>><?php echo esc_html(__('right', 'wp-reviews-plugin-for-google')); ?></option>
<option value="justify" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'justify' ? 'selected' : ''; ?>><?php echo esc_html(__('justify', 'wp-reviews-plugin-for-google')); ?></option>
</select>
</form>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Review text', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-review-text-mode" />
<?php wp_nonce_field('ti-save-review-text-mode'); ?>
<select class="ti-form-control" name="review_text_mode">
<option value="scroll" <?php echo $pluginManagerInstance->getWidgetOption('review-text-mode') === 'scroll' ? 'selected' : ''; ?>><?php echo esc_html(__('Scroll', 'wp-reviews-plugin-for-google')); ?></option>
<option value="readmore" <?php echo $pluginManagerInstance->getWidgetOption('review-text-mode') === 'readmore' ? 'selected' : ''; ?>><?php echo esc_html(__('Read more', 'wp-reviews-plugin-for-google')); ?></option>
<option value="truncated" <?php echo $pluginManagerInstance->getWidgetOption('review-text-mode') === 'truncated' ? 'selected' : ''; ?>><?php echo esc_html(__('Truncated', 'wp-reviews-plugin-for-google')); ?></option>
</select>
</form>
</div>
<?php endif; ?>
<?php endif; ?>
<?php if ($isTopRatedBadge): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Select type', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-top-rated-type" />
<?php wp_nonce_field('ti-save-top-rated-type'); ?>
<select class="ti-form-control" name="type">
<?php foreach ($pluginManager::$widget_top_rated_titles as $type => $langs): ?>
<option value="<?php echo esc_attr($type); ?>" <?php echo $pluginManagerInstance->getWidgetOption('top-rated-type') == $type ? 'selected' : ''; ?>><?php
switch ($type) {
case 'Apartment':
echo esc_html(__('Apartment', 'wp-reviews-plugin-for-google'));
break;
case 'Bar':
echo esc_html(__('Bar', 'wp-reviews-plugin-for-google'));
break;
case 'Cafe':
echo esc_html(__('Cafe', 'wp-reviews-plugin-for-google'));
break;
case 'Clinic':
echo esc_html(__('Clinic', 'wp-reviews-plugin-for-google'));
break;
case 'Hotel':
echo esc_html(__('Hotel', 'wp-reviews-plugin-for-google'));
break;
case 'Provider':
echo esc_html(__('Provider', 'wp-reviews-plugin-for-google'));
break;
case 'Restaurant':
echo esc_html(__('Restaurant', 'wp-reviews-plugin-for-google'));
break;
case 'Service':
echo esc_html(__('Service', 'wp-reviews-plugin-for-google'));
break;
case 'Webshop':
echo esc_html(__('Webshop', 'wp-reviews-plugin-for-google'));
break;
default:
echo esc_html($type);
break;
}
?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<?php if (!$pluginManagerInstance->isFomoWidget()): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Select date format', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-top-rated-date" />
<?php wp_nonce_field('ti-save-top-rated-date'); ?>
<?php $topRatedDate = $pluginManagerInstance->getWidgetOption('top-rated-date'); ?>
<select class="ti-form-control" name="date">
<option value="hide"<?php if ($topRatedDate === 'hide'): ?> selected<?php endif; ?>><?php echo esc_html(__("Hide", 'wp-reviews-plugin-for-google')); ?></option>
<option value="last-year"<?php if ($topRatedDate === 'last-year'): ?> selected<?php endif; ?>><?php echo esc_html(__("Last year", 'wp-reviews-plugin-for-google')); ?></option>
<option value=""<?php if (!$topRatedDate): ?> selected<?php endif; ?>><?php echo esc_html(__("Current year", 'wp-reviews-plugin-for-google')); ?></option>
</select>
</form>
</div>
<?php endif; ?>
<?php endif; ?>
<?php if ($pluginManagerInstance->isFomoWidget()): ?>
<?php if ($pluginManagerInstance->isFomoCustomWidget()): ?>
<div class="ti-form-group" style="max-width: 400px">
<label><?php echo esc_html(__('Title', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-title" />
<?php wp_nonce_field('ti-save-fomo-title'); ?>
<input type="text" class="ti-form-control ti-save-input-on-change" value="<?php echo esc_attr(wp_kses($pluginManagerInstance->getWidgetOption('fomo-title'), ['u' => []])); ?>" name="fomo-title" />
<small class="ti-text-muted" style="padding-left: 5px"><?php echo esc_html(htmlentities(__('Enclose the text in <u></u> if you want to highlight it', 'wp-reviews-plugin-for-google'))); ?></small>
</form>
</div>
<div class="ti-form-group" style="max-width: 400px">
<label><?php echo esc_html(__('Subtitle', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-text" />
<?php wp_nonce_field('ti-save-fomo-text'); ?>
<input type="text" class="ti-form-control ti-save-input-on-change" value="<?php echo esc_attr(wp_kses($pluginManagerInstance->getWidgetOption('fomo-text'), ['u' => []])); ?>" name="fomo-text" />
<small class="ti-text-muted" style="padding-left: 5px"><?php echo esc_html(htmlentities(__('Enclose the text in <u></u> if you want to highlight it', 'wp-reviews-plugin-for-google'))); ?></small>
</form>
</div>
<?php endif; ?>
<?php if ($choices = $pluginManagerInstance->getFomoSubtitleTextChoices()): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Subtitle', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-text" />
<?php wp_nonce_field('ti-save-fomo-text'); ?>
<select class="ti-form-control" name="fomo-text">
<option value="hide" <?php echo $pluginManagerInstance->getWidgetOption('fomo-text') === '' ? 'selected' : ''; ?>><?php echo esc_html(__('Hide', 'wp-reviews-plugin-for-google')); ?></option>
<?php foreach ($choices as $choice => $choiceText): ?>
<option value="<?php echo esc_attr($choice); ?>" <?php echo $pluginManagerInstance->getWidgetOption('fomo-text') == $choice ? 'selected' : ''; ?>><?php echo esc_html($choiceText); ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<?php endif; ?>
<div class="ti-form-row">
<div class="ti-form-group">
<label><?php echo esc_html(__('Icon', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-icon" />
<?php wp_nonce_field('ti-save-fomo-icon'); ?>
<select class="ti-form-control" name="fomo-icon">
<option value="hide" <?php echo $pluginManagerInstance->getWidgetOption('fomo-icon') === 'hide' ? 'selected' : ''; ?>><?php echo esc_html(__('Hide', 'wp-reviews-plugin-for-google')); ?></option>
<?php foreach ($pluginManager::$widget_templates['templates'][$styleId]['params']['fomo-icon-choices'] as $icon): ?>
<option value="<?php echo esc_attr($icon); ?>" <?php echo $pluginManagerInstance->getWidgetOption('fomo-icon') == $icon ? 'selected' : ''; ?>><?php
switch ($icon) {
case 'profile-images':
echo esc_html(__('Profile images', 'wp-reviews-plugin-for-google'));
break;
case 'platform':
echo esc_html(__('Platform', 'wp-reviews-plugin-for-google'));
break;
default:
echo esc_html(ucfirst($icon));
break;
}
?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Color', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-color" />
<?php wp_nonce_field('ti-save-fomo-color'); ?>
<input type="text" class="ti-form-control ti-color-picker ti-save-input-on-change-color" value="<?php echo esc_attr($pluginManagerInstance->getWidgetOption('fomo-color')); ?>" name="fomo-color" readonly />
</form>
</div>
</div>
<div class="ti-form-row">
<div class="ti-form-group">
<label><?php echo esc_html(__('Align', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-align" />
<?php wp_nonce_field('ti-save-align'); ?>
<select class="ti-form-control" name="align">
<option value="left" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'left' ? 'selected' : ''; ?>><?php echo esc_html(__('left', 'wp-reviews-plugin-for-google')); ?></option>
<option value="right" <?php echo $pluginManagerInstance->getWidgetOption('align') === 'right' ? 'selected' : ''; ?>><?php echo esc_html(__('right', 'wp-reviews-plugin-for-google')); ?></option>
</select>
</form>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Margin', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-margin" />
<?php wp_nonce_field('ti-save-fomo-margin'); ?>
<input type="number" class="ti-form-control ti-save-input-on-change" min=0 step=1 value="<?php echo esc_attr($pluginManagerInstance->getWidgetOption('fomo-margin')); ?>" name="fomo-margin" />
</form>
</div>
</div>
<div class="ti-form-row">
<?php if (isset($pluginManager::$widget_templates['templates'][$styleId]['params']['fomo-days'])): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Number of days', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-day" />
<?php wp_nonce_field('ti-save-fomo-day'); ?>
<select class="ti-form-control" name="fomo-day">
<?php foreach ($pluginManager::$widget_templates['templates'][$styleId]['params']['fomo-days'] as $day): ?>
<?php
/* translators: %d: Number of days */
$name = sprintf(__('%d days', 'wp-reviews-plugin-for-google'), $day);
if ($day === 1) {
/* translators: %d: 24 */
$name = sprintf(__('%d hours', 'wp-reviews-plugin-for-google'), 24);
}
?>
<option value="<?php echo esc_attr($day); ?>" <?php echo $pluginManagerInstance->getWidgetOption('fomo-day') == $day ? 'selected' : ''; ?>><?php echo esc_html($name); ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<?php endif; ?>
<?php if ($pluginManagerInstance->isFomoHideCountAvailable()): ?>
<div class="ti-form-group">
<label><?php echo esc_html(__('Hide until count reaches', 'wp-reviews-plugin-for-google')); ?></label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-hide-count" />
<?php wp_nonce_field('ti-save-fomo-hide-count'); ?>
<input type="number" class="ti-form-control ti-save-input-on-change" min=0 step=1 value="<?php echo esc_attr($pluginManagerInstance->getWidgetOption('fomo-hide-count')); ?>" name="fomo-hide-count" />
</form>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
<div class="ti-right-block">
<form method="post" id="ti-widget-options">
<input type="hidden" name="command" value="save-options" />
<?php wp_nonce_field('ti-save-options'); ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews()): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="only-ratings" value="1"<?php if ($filter['only-ratings']): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Hide reviews without comments', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if (in_array($styleId, [ 4, 6, 7, 15, 16, 19, 31, 33, 36, 37, 38, 39, 44 ])): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="no-rating-text" value="1"<?php if ($pluginManagerInstance->getWidgetOption('no-rating-text')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Hide rating text', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews() && (!in_array($widgetType, ['floating']) || $styleId === 53)): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="footer-filter-text" value="1"<?php if ($pluginManagerInstance->getWidgetOption('footer-filter-text')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show minimum review filter condition', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews()): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="show-review-replies" value="1"<?php if ($pluginManagerInstance->getWidgetOption('show-review-replies')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show review reply', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if (in_array($styleId, [ 8, 10, 13 ])): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="show-header-button" value="1"<?php if ($pluginManagerInstance->getWidgetOption('show-header-button')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show write review button', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if (in_array($styleId, [ 8, 16, 18, 31, 33 ])): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="reviews-load-more" value="1"<?php if ($pluginManagerInstance->getWidgetOption('reviews-load-more')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show "Load more" button', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews() && !in_array($styleId, [53,54])): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="verified-icon" value="1"<?php if ($pluginManagerInstance->getWidgetOption('verified-icon')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show verified review icon', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if (in_array($widgetType, [ 'slider', 'sidebar' ]) && !in_array($styleId, [ 8, 9, 10, 18, 19, 37, 54 ])): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="show-arrows" value="1"<?php if ($pluginManagerInstance->getWidgetOption('show-arrows')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show navigation arrows', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews() && $styleId != 52): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="show-reviewers-photo" value="1"<?php if ($pluginManagerInstance->getWidgetOption('show-reviewers-photo')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__("Show reviewer's profile picture", 'wp-reviews-plugin-for-google')); ?></label>
</span>
<span class="ti-checkbox ti-checkbox-row ti-disabled">
<input type="checkbox" value="1" disabled />
<label class="ti-tooltip">
<?php echo esc_html(__("Show reviewer's profile picture locally, from a single image (less requests)", 'wp-reviews-plugin-for-google')); ?>
<span class="ti-tooltip-message"><?php echo esc_html(__('Paid package feature', 'wp-reviews-plugin-for-google')); ?></span>
</label>
</span>
<span class="ti-checkbox ti-checkbox-row ti-disabled">
<input type="checkbox" value="1" disabled />
<label class="ti-tooltip">
<?php echo esc_html(__('Show photos in reviews', 'wp-reviews-plugin-for-google')); ?>
<span class="ti-tooltip-message"><?php echo esc_html(__('Paid package feature', 'wp-reviews-plugin-for-google')); ?></span>
</label>
</span>
<?php endif; ?>
<?php if (!in_array($widgetType, [ 'floating', 'fomo' ]) && !$isTopRatedBadge && $scssSet !== 'drop-shadow' && $styleId != 54): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="enable-animation" value="1"<?php if ($pluginManagerInstance->getWidgetOption('enable-animation')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Enable mouseover animation', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if (!$pluginManagerInstance->isFomoWidget()): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="disable-font" value="1"<?php if ($pluginManagerInstance->getWidgetOption('disable-font')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__("Use site's font", 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php if ($pluginManagerInstance->isLayoutHasReviews()): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="show-logos" value="1"<?php if ($pluginManagerInstance->getWidgetOption('show-logos')): ?> checked<?php endif;?> />
<label><?php echo esc_html(__('Show platform logos', 'wp-reviews-plugin-for-google')); ?></label>
</span>

<?php endif; ?>
<?php if ($pluginManagerInstance->isFomoWidget()): ?>
<?php if ('hide' !== $pluginManagerInstance->getWidgetOption('fomo-icon')): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="fomo-open" value="1"<?php if ($pluginManagerInstance->getWidgetOption('fomo-open')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Default open', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="fomo-border" value="1"<?php if ($pluginManagerInstance->getWidgetOption('fomo-border')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show border', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php if ('hide' !== $pluginManagerInstance->getWidgetOption('fomo-icon') && 'platform-images' !== $pluginManagerInstance->getWidgetOption('fomo-icon')): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="fomo-icon-background" value="1"<?php if ($pluginManagerInstance->getWidgetOption('fomo-icon-background')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show icon background', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="fomo-link" value="1"<?php if ($pluginManagerInstance->getWidgetOption('fomo-link')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Enable link', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php if ($pluginManagerInstance->getWidgetOption('fomo-link')): ?>
<span class="ti-checkbox ti-checkbox-row">
<input type="checkbox" name="fomo-arrow" value="1"<?php if ($pluginManagerInstance->getWidgetOption('fomo-arrow')): ?> checked<?php endif; ?> />
<label><?php echo esc_html(__('Show arrow', 'wp-reviews-plugin-for-google')); ?></label>
</span>
<?php endif; ?>
<?php endif; ?>
</form>
<div class="clear"></div>
<?php if ($pluginManagerInstance->getWidgetOption('fomo-link') && $pluginManagerInstance->isFomoCustomWidget()): ?>
<div class="ti-form-group ti-mt-4">
<label>URL</label>
<form method="post" action="">
<input type="hidden" name="command" value="save-fomo-url" />
<?php wp_nonce_field('ti-save-fomo-url'); ?>
<input type="text" class="ti-form-control ti-save-input-on-change" value="<?php echo esc_attr($pluginManagerInstance->getWidgetOption('fomo-url')); ?>" name="fomo-url" />
</form>
</div>
<?php endif; ?>
</div>
<div class="clear"></div>
<?php if (!$isTopRatedBadge || $isTopRatedBadgeValid): ?>
<div class="ti-box-footer">
<a href="<?php echo esc_url(wp_nonce_url('?page='. esc_attr($_page) .'&tab=free-widget-configurator&setup_widget', 'ti-setup-widget')); ?>" class="ti-btn ti-btn-loading-on-click ti-pull-right"><?php echo esc_html(__('Save and get code', 'wp-reviews-plugin-for-google')); ?></a>
<div class="clear"></div>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php else: ?>
<h1 class="ti-header-title"><?php echo esc_html(__('Insert code', 'wp-reviews-plugin-for-google')); ?></h1>
<?php if (!count($reviews) && !$isReviewDownloadInProgress): ?>
<div class="ti-notice ti-notice-warning" style="margin: 0 0 15px 0">
<p>
<?php
/* translators: %s: Google */
echo esc_html(sprintf(__('There are no reviews on your %s platform.', 'wp-reviews-plugin-for-google'), 'Google'));
?>
</p>
</div>
<?php endif; ?>
<div class="ti-box">
<div class="ti-box-header"><?php echo esc_html(__('Insert this shortcode into your website', 'wp-reviews-plugin-for-google')); ?></div>
<?php include(plugin_dir_path(__FILE__) . '../include/shortcode-paste-box.php'); ?>
</div>
<div class="ti-box ti-sales-widget-box-container">
<div class="ti-box-header">
<?php echo
/* translators: 1: widget count, 2: percent */
esc_html(sprintf(__('%1$d Professional Review Widgets That Help Boost Sales by %2$d%%', 'wp-reviews-plugin-for-google'), 5, 9));
?>
<small>
<?php echo
/* translators: %d: widget count */
esc_html(sprintf(__('For best results, generate all %d widgets and place them in the recommended positions.', 'wp-reviews-plugin-for-google'), 5));
?>
</small>
<?php echo wp_kses_post($pluginManagerInstance->getProFeatureButton('wp-google-'.(!class_exists('Woocommerce') ? 6 : 7))); ?>
</div>
<?php
$tiSalesRows = [
[
'Button VIII.',
__('Homepage – Hero section', 'wp-reviews-plugin-for-google'),
__('Reduces bounce rate', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-button8.png'),
300,
],
[
'Slider I. <i>('.__('with header', 'wp-reviews-plugin-for-google').')</i>',
__('Homepage – Middle of the page', 'wp-reviews-plugin-for-google'),
__('Increases time on site, improves SEO, more sales', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-slider1-with-header.png'),
1200,
],
[
'Top Rated Badge VIII.',
__('Homepage – Footer', 'wp-reviews-plugin-for-google'),
__('More calls and emails', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-top-rated-badge8.png'),
300,
],
[
'Button III.',
__('Contact page - Under the contact form', 'wp-reviews-plugin-for-google'),
__('More enquires', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-button3.png'),
300,
],
[
__('Review Certificate', 'wp-reviews-plugin-for-google'),
__('Every page – Left corner of the page', 'wp-reviews-plugin-for-google'),
__('Builds trust', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-certificate.png'),
350,
],
];
if (class_exists('Woocommerce')) {
$tiSalesRows = [
[
'Button VIII.',
__('Homepage – Hero section', 'wp-reviews-plugin-for-google'),
__('Reduces bounce rate', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-button8.png'),
300,
],
[
'Slider I. <i>('.__('with header', 'wp-reviews-plugin-for-google').')</i>',
__('Homepage – Middle of the page', 'wp-reviews-plugin-for-google'),
__('Increases time on site, improves SEO, more sales', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-slider1-with-header.png'),
1200,
],
[
__('Review Certificate', 'wp-reviews-plugin-for-google'),
__('Every page – Left corner of the page', 'wp-reviews-plugin-for-google'),
__('Builds trust', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-certificate.png'),
350,
],
[
'Button III.',
__('Every product page – Near price or cart button', 'wp-reviews-plugin-for-google'),
__('Increases purchases', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-button3.png'),
300,
],
[
'Slider I. <i>('.__('with footer', 'wp-reviews-plugin-for-google').')</i>',
__('Every product page – Below the product details', 'wp-reviews-plugin-for-google'),
__('Builds purchase confidence before checkout', 'wp-reviews-plugin-for-google'),
$pluginManagerInstance->get_plugin_file_url('static/img/ti-widget-tooltip-slider1-with-footer.png'),
1200,
],
];
}
?>
<div class="ti-sales-widget-box">
<div class="ti-sales-widget-table">
<div class="ti-sales-widget-row">
<div><?php echo esc_html(__('Recommended widget', 'wp-reviews-plugin-for-google')); ?></div>
<div><?php echo esc_html(__('Where to place it', 'wp-reviews-plugin-for-google')); ?></div>
<div><?php echo esc_html(__('Main benefit', 'wp-reviews-plugin-for-google')); ?></div>
</div>
<?php foreach ($tiSalesRows as $index => $item): ?>
<div class="ti-sales-widget-row">
<div>
<div class="ti-sales-widget-row-title">
<span><?php echo esc_html($index + 1).'.'; ?></span>
<?php echo wp_kses_post($item[0]); ?>
<span class="ti-sales-widget-preview-icon"></span>
<div class="ti-sales-widget-preview" style="--ti-preview-width: <?php echo esc_attr($item[4]); ?>px;">
<img src="<?php echo esc_url($item[3]); ?>" alt="" loading="lazy" />
</div>
</div>
</div>
<div><?php echo esc_html($item[1]); ?></div>
<div><?php echo esc_html($item[2]); ?></div>
</div>
<?php endforeach; ?>
</div>
<div class="ti-sales-widget-cta">
<div class="ti-sales-widget-cta-content">
<strong><?php
/* translators: %d: 9 */
echo esc_html(sprintf(__('Recommended setup for +%d%% more sales', 'wp-reviews-plugin-for-google'), 9));
?></strong>
<span><?php
/* translators: %s: 35,000+ */
echo esc_html(sprintf(__('Based on data from %s businesses.', 'wp-reviews-plugin-for-google'), '35,000+'));
?></span>
</div>
</div>
</div>
</div>
<?php
$tiCampaign1 = 'wp-google-1';
$tiCampaign2 = 'wp-google-2';
include(plugin_dir_path(__FILE__) . '../include/get-more-customers-box.php');
?>
<?php if (!get_option($pluginManagerInstance->get_option_name('rate-us-feedback'), 0)): ?>
<?php include(plugin_dir_path(__FILE__) . '../include/rate-us-feedback-box.php'); ?>
<?php endif; ?>
<?php endif; ?>
</div>

<?php
defined('ABSPATH') or die('No script kiddies please!');
if (isset($_POST['save-highlight'])) {
check_admin_referer('ti-save-highlight');
$id = null;
$start = null;
$length = null;
if (isset($_POST['id'])) {
$id = (int)$_POST['id'];
}
if (isset($_POST['start'])) {
$start = sanitize_text_field(wp_unslash($_POST['start']));
}
if (isset($_POST['length'])) {
$length = sanitize_text_field(wp_unslash($_POST['length']));
}
if ($id) {
$highlight = "";
if (!is_null($start)) {
$highlight = $start . ',' . $length;
}
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->update($pluginManagerInstance->get_tablename('reviews'), ['highlight' => $highlight], ['id' => $id]);
wp_cache_delete('ti-reviews-cache-'.$pluginManagerInstance->getShortName());
}
exit;
}
if (isset($_GET['toggle-hide'])) {
check_admin_referer('ti-toggle-hide');
$id = (int)$_GET['toggle-hide'];
if ($id) {
$hidden = 1;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
if ($wpdb->get_var($wpdb->prepare('SELECT hidden FROM %i WHERE id = %s', $pluginManagerInstance->get_tablename('reviews'), $id))) {
$hidden = 0;
}
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->update($pluginManagerInstance->get_tablename('reviews'), ['hidden' => $hidden], ['id' => $id]);
wp_cache_delete('ti-reviews-cache-'.$pluginManagerInstance->getShortName());
}
header('Location: admin.php?page='.esc_attr($_page).'&tab=my-reviews');
exit;
}
/* Replied flag saving:
- Google: comes after source connect
- Facebook: we saved internal
- other: dont save anything & only show "Reply with ChatGPT" button
*/
if (isset($_POST['save-reply'])) {
check_admin_referer('ti-save-reply');
$id = null;
$reply = null;
if (isset($_POST['id'])) {
$id = (int)$_POST['id'];
}
$reply = wp_kses_post(wp_unslash($_POST['save-reply']));
if ($id && $reply) {
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->update($pluginManagerInstance->get_tablename('reviews'), ['reply' => $reply], ['id' => $id]);
wp_cache_delete('ti-reviews-cache-'.$pluginManagerInstance->getShortName());
}
exit;
}
if (isset($_POST['save-reply-generated'])) {
update_option($pluginManagerInstance->get_option_name('reply-generated'), 1, false);
exit;
}

if (isset($_POST['download_data'])) {
check_admin_referer('ti-download-reviews');
// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
$data = json_decode(wp_unslash($_POST['download_data']), true);
if (isset($data['is_new_reviews']) && $data['is_new_reviews']) {
if (isset($data['reviews']) && is_array($data['reviews']) && $data['reviews']) {
$pluginManagerInstance->save_reviews($data['reviews']);
if (!$pluginManagerInstance->getNotificationParam('review-download-finished', 'hidden')) {
$pluginManagerInstance->setNotificationParam('review-download-finished', 'active', true);
}
$pluginManagerInstance->sendNotificationEmail('review-download-finished');
}
$pageDetails = $pluginManagerInstance->getPageDetails();
if (isset($data['name'])) {
$pageDetails['name'] = $data['name'];
if (isset($pageDetails['address'])) {
$pageDetails['address'] = $data['address'];
}
if (isset($pageDetails['avatar_url'])) {
$pageDetails['avatar_url'] = $data['avatar_url'];
}
$pageDetails['rating_number'] = $data['rating_number'];
if (isset($data['rating_numbers']) && $data['rating_numbers']) {
$pageDetails['rating_numbers'] = $data['rating_numbers'];
}
if (isset($data['rating_numbers_last']) && $data['rating_numbers_last']) {
$pageDetails['rating_numbers_last'] = $data['rating_numbers_last'];
}
$pageDetails['rating_score'] = $data['rating_score'];
update_option($pluginManagerInstance->get_option_name('page-details'), $pageDetails, false);
$GLOBALS['wp_object_cache']->delete($pluginManagerInstance->get_option_name('page-details'), 'options');
}
if (!$pluginManagerInstance->getNotificationParam('review-download-available', 'hidden')) {
$pluginManagerInstance->setNotificationParam('review-download-available', 'do-check', true);
$pluginManagerInstance->setNotificationParam('review-download-available', 'active', false);
}
} else {
update_option($pluginManagerInstance->get_option_name('review-download-is-failed'), 1, false);
}
update_option($pluginManagerInstance->get_option_name('download-timestamp'), time() + (int)$data['next_update_available'], false);
exit;
}
$reviews = $pluginManagerInstance->getReviews();
$isReviewDownloadInProgress = $pluginManagerInstance->is_review_download_in_progress();
function trustindex_plugin_write_rating_stars($score)
{
global $pluginManagerInstance;
if ($pluginManagerInstance->is_ten_scale_rating_platform()) {
return '<div class="ti-rating-box">'. $pluginManagerInstance->formatTenRating($score) .'</div>';
}
$text = "";
$link = "https://cdn.trustindex.io/assets/platform/".ucfirst("google")."/star/";
if (!is_numeric($score)) {
return $text;
}
for ($si = 1; $si <= $score; $si++) {
$text .= '<img src="'. $link .'f.svg" class="ti-star" />';
}
$fractional = $score - floor($score);
if (0.25 <= $fractional) {
if ($fractional < 0.75) {
$text .= '<img src="'. $link .'h.svg" class="ti-star" />';
}
else {
$text .= '<img src="'. $link .'f.svg" class="ti-star" />';
}
$si++;
}
for (; $si <= 5; $si++) {
$text .= '<img src="'. $link .'e.svg" class="ti-star" />';
}
return $text;
}
wp_enqueue_style('trustindex-widget-css', 'https://cdn.trustindex.io/assets/widget-presetted-css/4-light-background.css', [], true);
wp_enqueue_script('trustindex-review-js', 'https://cdn.trustindex.io/assets/js/trustindex-review.js', [], true, true);
wp_add_inline_script('trustindex-review-js', '
jQuery(".ti-review-content").TI_shorten({
"showLines": 2,
"lessText": "'. esc_html(__('Show less', 'wp-reviews-plugin-for-google')) .'",
"moreText": "'. esc_html(__('Show more', 'wp-reviews-plugin-for-google')) .'",
});
jQuery(".ti-review-content").TI_format();
');
$downloadTimestamp = get_option($pluginManagerInstance->get_option_name('download-timestamp'), time());
$pageDetails = $pluginManagerInstance->getPageDetails();
if ($reviewDownloadFailed = get_option($pluginManagerInstance->get_option_name('review-download-is-failed'))) {
delete_option($pluginManagerInstance->get_option_name('review-download-is-failed'));
}
?>
<div class="ti-header-title"><?php echo esc_html(__('My Reviews', 'wp-reviews-plugin-for-google')); ?></div>
<div class="ti-box">
<?php if (!$isReviewDownloadInProgress): ?>
<?php if ($reviewDownloadFailed): ?>
<div class="ti-notice ti-notice-error">
<p><?php echo esc_html(__('The manual review download not available yet.', 'wp-reviews-plugin-for-google')); ?></p>
</div>
<?php endif; ?>
<?php if ($downloadTimestamp <= time()): ?>
<div class="ti-notice ti-d-none ti-notice-info" id="ti-connect-info">
<p><?php echo esc_html(__("A popup window should be appear! Please, go to there and continue the steps! (If there is no popup window, you can check the the browser's popup blocker)", 'wp-reviews-plugin-for-google')); ?></p>
</div>
<a href="#" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-download-reviews')); ?>" class="ti-btn ti-btn-lg ti-btn-loading-on-click ti-tooltip ti-show-tooltip ti-tooltip-light ti-mb-1 btn-download-reviews" data-delay=10>
<?php echo esc_html(__('Download new reviews', 'wp-reviews-plugin-for-google'));?>
<span class="ti-tooltip-message"><?php echo esc_html(__('Now, you can download your new reviews.', 'wp-reviews-plugin-for-google')); ?></span>
</a>
<?php else: ?>
<?php $days = ceil(($downloadTimestamp - time()) / 86400); ?>
<a href="#" class="ti-btn ti-btn-lg ti-btn-disabled ti-tooltip ti-show-tooltip ti-tooltip-light ti-mb-1">
<?php echo esc_html(__('Download new reviews', 'wp-reviews-plugin-for-google')); ?>
<span class="ti-tooltip-message"><?php
/* translators: %d: days */
echo esc_html(sprintf(__('The manual review download will be available again in %d day(s).', 'wp-reviews-plugin-for-google'), $days));
?></span>
</a>
<?php endif; ?>
<?php $pageDetails = $pluginManagerInstance->getPageDetails(); ?>
<input type="hidden" id="ti-noreg-page-id" value="<?php echo esc_attr($pageDetails['id']); ?>" />
<input type="hidden" id="ti-noreg-webhook-url" value="<?php echo esc_url($pluginManagerInstance->getWebhookUrl()); ?>" />
<input type="hidden" id="ti-noreg-email" value="<?php echo esc_attr(get_option('admin_email')); ?>" />
<input type="hidden" id="ti-noreg-version" value="<?php echo esc_attr($pluginManagerInstance->getVersion()); ?>" />

<?php
$reviewDownloadToken = get_option($pluginManagerInstance->get_option_name('review-download-token'));
if (!$reviewDownloadToken) {
$reviewDownloadToken = wp_create_nonce('ti-noreg-connect-token');
update_option($pluginManagerInstance->get_option_name('review-download-token'), $reviewDownloadToken, false);
}
?>
<input type="hidden" id="ti-noreg-connect-token" name="ti-noreg-connect-token" value="<?php echo esc_attr($reviewDownloadToken); ?>" />
<?php endif; ?>
<div class="ti-upgrade-notice">
<strong><?php echo esc_html(__('UPGRADE to PRO Features', 'wp-reviews-plugin-for-google')); ?></strong>
<p><?php
/* translators: %d: platform number */
echo esc_html(sprintf(__('Automatic review update, creating unlimited review widgets, downloading and displaying all reviews, %d review platforms available!', 'wp-reviews-plugin-for-google'), 137));
?></p>
<?php echo wp_kses_post($pluginManagerInstance->getProFeatureButton('wp-google-pro')); ?>
</div>

<?php if (!count($reviews)): ?>
<?php if (!$isReviewDownloadInProgress): ?>
<div class="ti-notice ti-notice-warning">
<p><?php echo esc_html(__('You had no reviews at the time of last review downloading.', 'wp-reviews-plugin-for-google')); ?></p>
</div>
<?php endif; ?>
<?php else: ?>
<input type="hidden" id="ti-widget-language" value="<?php echo esc_attr(get_option($pluginManagerInstance->get_option_name('lang'), 'en')); ?>" />
<table class="wp-list-table widefat fixed striped table-view-list ti-my-reviews ti-widget">
<thead>
<tr>
<th class="ti-text-center"><?php echo esc_html(__('Reviewer', 'wp-reviews-plugin-for-google')); ?></th>
<th class="ti-text-center" style="width: 90px;"><?php echo esc_html(__('Rating', 'wp-reviews-plugin-for-google')); ?></th>
<th class="ti-text-center"><?php echo esc_html(__('Date', 'wp-reviews-plugin-for-google')); ?></th>
<th style="width: 50%"><?php echo esc_html(__('Text', 'wp-reviews-plugin-for-google')); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($reviews as $review): ?>
<?php $reviewText = $pluginManagerInstance->getReviewHtml($review); ?>
<tr data-id="<?php echo esc_attr($review->id); ?>"<?php if ($review->hidden): ?> class="ti-hidden-review"<?php endif; ?>>
<td class="ti-text-center">
<img src="<?php echo esc_url($review->user_photo); ?>" class="ti-user-avatar" /><br />
<?php echo esc_html($review->user); ?>
</td>
<td class="ti-text-center source-<?php echo esc_attr(ucfirst("google")); ?>"><?php echo wp_kses_post(trustindex_plugin_write_rating_stars($review->rating)); ?></td>
<td class="ti-text-center"><?php echo esc_html($review->date); ?></td>
<td>
<div class="ti-review-content"><?php echo wp_kses_post($reviewText ? $reviewText : ""); ?></div>
<?php

$state = 'reply';
if ($review->reply) {
$state = 'replied';
}
$hideReplyButton = false;
$hideReplyButton = get_option($pluginManagerInstance->get_option_name('review-download-modal'), 1);
?>
<?php if (!$review->hidden): ?>
<?php if (!$hideReplyButton): ?>
<?php if ($review->reply): ?>
<a href="#" class="ti-btn ti-btn-default ti-btn-sm ti-btn-default-disabled btn-show-ai-reply"><?php echo esc_html(__('Reply', 'wp-reviews-plugin-for-google')); ?></a>
<?php else: ?>
<a href="#" class="ti-btn ti-btn-sm btn-show-ai-reply" data-edit-reply-text="<?php echo esc_html(__('Reply', 'wp-reviews-plugin-for-google')); ?>"><?php echo esc_html(__('Reply with ChatGPT', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
<?php endif; ?>
<?php if ($review->text): ?>
<a href="<?php echo esc_attr($review->id); ?>" class="ti-btn ti-btn-sm ti-btn-default btn-show-highlight<?php if (isset($review->highlight) && $review->highlight): ?> has-highlight<?php endif; ?>"><?php echo esc_html(__('Highlight text', 'wp-reviews-plugin-for-google')) ;?></a>
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=my-reviews&toggle-hide='. $review->id, 'ti-toggle-hide')); ?>" class="ti-btn ti-btn-sm ti-btn-default btn-toggle-hide">
<?php if (!$review->hidden): ?>
<?php echo esc_html(__('Hide review', 'wp-reviews-plugin-for-google')); ?>
<?php else: ?>
<?php echo esc_html(__('Show review', 'wp-reviews-plugin-for-google')); ?>
<?php endif; ?>
</a>
<?php if (!$review->hidden && !$hideReplyButton): ?>
<div class="ti-button-dropdown ti-reply-box<?php if ($state === 'replied'): ?> ti-active<?php endif; ?>" data-state="<?php echo esc_attr($state); ?>" data-original-state="<?php echo esc_attr($state); ?>">
<span class="ti-button-dropdown-arrow" data-button=".btn-show-ai-reply"></span>
<?php if ($state !== 'copy-reply'): ?>
<div class="ti-reply-box-state state-reply">
<div class="ti-button-dropdown-title">
<strong><?php echo esc_html(__('ChatGPT generated reply', 'wp-reviews-plugin-for-google')); ?></strong>
<span><?php echo esc_html(__('you can modify before upload', 'wp-reviews-plugin-for-google')); ?>
</div>
<textarea id="ti-ai-reply-<?php echo esc_attr($review->id); ?>" rows="1"></textarea>
<?php if (!$review->text): ?>
<div class="ti-alert ti-alert-empty-review d-none"><?php echo esc_html(__("The reply was generated in your widget language because the review's text is empty.", 'wp-reviews-plugin-for-google')); ?></div>
<?php endif; ?>
<a href="<?php echo esc_attr($review->id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-save-reply')); ?>" class="ti-btn ti-btn-sm btn-post-reply"><?php
/* translators: %s: platform's name */
echo esc_html(sprintf(__('Upload reply to %s', 'wp-reviews-plugin-for-google'), 'Google'));
?></a>
<a href="#" class="ti-btn ti-btn-sm ti-btn-no-background btn-hide-ai-reply"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
</div>

<div class="ti-reply-box-state state-replied">
<div class="ti-button-dropdown-title">
<strong><?php
/* translators: %s: Name */
echo esc_html(sprintf(__('Reply by %s', 'wp-reviews-plugin-for-google'), $pageDetails['name']));
?></strong>
</div>
<div class="ti-alert ti-d-none"><?php echo esc_html(__('Reply successfully uploaded.', 'wp-reviews-plugin-for-google')); ?></div>
<p><?php echo esc_html($review->reply); ?></p>
<?php if ($pluginManagerInstance->getShortName() === 'google'): ?>
<a href="<?php echo esc_attr($review->id); ?>" class="ti-btn ti-btn-sm ti-btn-white btn-show-edit-reply"><?php echo esc_html(__('Edit reply', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</div>

<div class="ti-reply-box-state state-edit-reply">
<div class="ti-button-dropdown-title">
<strong><?php echo esc_html(__('Edit reply', 'wp-reviews-plugin-for-google')); ?></strong>
<span><?php echo esc_html(__('change your previous reply', 'wp-reviews-plugin-for-google')); ?>
</div>
<textarea rows="1"><?php echo esc_html($review->reply); ?></textarea>
<a href="<?php echo esc_attr($review->id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-save-reply')); ?>" class="ti-btn ti-btn-sm btn-post-reply"><?php
/* translators: %s: platform's name */
echo esc_html(sprintf(__('Upload reply to %s', 'wp-reviews-plugin-for-google'), 'Google'));
?></a>
<a href="#" class="ti-btn ti-btn-sm ti-btn-no-background btn-hide-edit-reply"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
</div>
<?php endif; ?>
<div class="ti-reply-box-state state-copy-reply">
<div class="ti-button-dropdown-title">
<strong><?php echo esc_html(__('Copy the reply', 'wp-reviews-plugin-for-google')); ?></strong>
</div>
<div class="ti-alert ti-alert-warning ti-d-none">
<?php echo esc_html(__('We could not connect your account with the review.', 'wp-reviews-plugin-for-google')); ?>
<a href="#" class="btn-try-reply-again"><?php echo esc_html(__('Try again', 'wp-reviews-plugin-for-google')); ?></a>
</div>
<textarea id="ti-copy-ai-reply-<?php echo esc_attr($review->id); ?>" rows="1"></textarea>
<a href="#ti-copy-ai-reply-<?php echo esc_attr($review->id); ?>" class="ti-btn ti-btn-sm ti-tooltip ti-toggle-tooltip btn-copy2clipboard ">
<?php echo esc_html(__('Copy to clipboard', 'wp-reviews-plugin-for-google')) ;?>
<span class="ti-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo esc_html(__('Copied', 'wp-reviews-plugin-for-google')); ?>
</span>
</a>
<a href="#" class="ti-btn ti-btn-sm ti-btn-no-background btn-hide-ai-reply"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
</div>
</div>
<script type="application/ld+json"><?php echo json_encode([
'source' => [
'page_id' => $pageDetails['id'],
'name' => $pageDetails['name'],
'reviews' => [
'count' => $pageDetails['rating_number'],
'score' => $pageDetails['rating_score'],
],
'access_token' => isset($pageDetails['access_token']) ? $pageDetails['access_token'] : null
],
'review' => [
'id' => $review->reviewId,
'reviewer' => [
'name' => $review->user,
'avatar_url' => $review->user_photo
],
'rating' => $review->rating,
'text' => $review->text,
'created_at' => $review->date
]
]); ?></script>
<?php endif; ?>
<?php if (!$review->hidden && $review->text): ?>
<div class="ti-button-dropdown ti-highlight-box">
<span class="ti-button-dropdown-arrow" data-button=".btn-show-highlight"></span>
<div class="ti-button-dropdown-title">
<strong><?php echo esc_html(__('Highlight text', 'wp-reviews-plugin-for-google')); ?></strong>
<span><?php echo esc_html(__('just select the text you want to highlight', 'wp-reviews-plugin-for-google')); ?>
</div>
<div class="ti-highlight-content">
<div class='ti-raw-content'><?php echo wp_kses_post($reviewText); ?></div>
<div class='ti-selection-content'><?php echo wp_kses_post(preg_replace('/<mark class="ti-highlight">/', '', $reviewText)); ?></div>
</div>
<a href="<?php echo esc_attr($review->id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-save-highlight')); ?>" class="ti-btn ti-btn-sm btn-save-highlight"><?php echo esc_html(__('Save', 'wp-reviews-plugin-for-google')); ?></a>
<a href="#" class="ti-btn ti-btn-sm ti-btn-no-background btn-hide-highlight"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
<?php if ($review->highlight): ?>
<a href="<?php echo esc_attr($review->id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-save-highlight')); ?>" class="ti-btn ti-btn-sm ti-btn-danger ti-pull-right btn-remove-highlight"><?php echo esc_html(__('Remove highlight', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</div>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php if (!get_option($pluginManagerInstance->get_option_name('rate-us-feedback'), 0)): ?>
<?php include(plugin_dir_path(__FILE__ ) . '../include/rate-us-feedback-box.php'); ?>
<?php endif; ?>
<?php
$tiCampaign1 = 'wp-google-4';
$tiCampaign2 = 'wp-google-5';
include(plugin_dir_path(__FILE__ ) . '../include/get-more-customers-box.php');
?>

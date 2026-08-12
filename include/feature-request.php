<?php
defined('ABSPATH') or die('No script kiddies please!');
if (isset($_POST['command']) && $_POST['command'] === 'send-feature-request') {
check_admin_referer('send-feature-request_' . $pluginManagerInstance->get_plugin_slug(), '_wpnonce_send_feature_request');
$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : "";
$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : "";
$description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : "";
$workaround = isset($_POST['workaround']) ? wp_kses_post(wp_unslash($_POST['workaround'])) : "";
$attachments = isset($_POST['attachments']) ? wp_kses_post(wp_unslash($_POST['attachments'])) : "";
if ($email && $description) {
$subject = 'Feature request from '. $pluginNameForEmails .' plugin';
$message = 'We received a feature request to the '. $pluginNameForEmails .' plugin from <strong>'. $name .' ('. $email .', url: '. get_option('siteurl') .')</strong>:<br /><br /><strong>'. $description .'</strong><br /><br />Current workaround: <br /><br /><strong>'. $workaround .'</strong>';
if ($attachments) {
$message .= '<br />Attached urls: <br />- '. str_replace("\n", '<br />- ', $attachments);
}
ob_start();
include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'troubleshooting.php';
$troubleshootingData = ob_get_clean();
$message .= '<br /><br />Troubleshooting:<br />'. nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $troubleshootingData));
wp_mail('support@trustindex.io', $subject, $message, [ 'From: '. $email, 'Content-Type: text/html; charset=UTF-8' ]);
}
exit;
}
?>
<div class="ti-box ti-feature-request">
<div class="ti-box-inner">
<div class="ti-box-header"><?php echo esc_html(__('Missing a feature?', 'wp-reviews-plugin-for-google')); ?></div>
<p>
<?php echo esc_html(__('Anything you are missing in our product?', 'wp-reviews-plugin-for-google')); ?><br />
<?php echo esc_html(__('Drop a message here to let us know!', 'wp-reviews-plugin-for-google')); ?>
</p>
<form method="post" enctype="multipart/form-data">
<?php wp_nonce_field('send-feature-request_' . $pluginManagerInstance->get_plugin_slug(), '_wpnonce_send_feature_request' ); ?>
<input type="hidden" name="command" value="send-feature-request" />
<div class="ti-form-group">
<label><?php echo esc_html(__('Please describe the feature you need', 'wp-reviews-plugin-for-google')); ?>*</label>
<textarea class="ti-form-control" name="description" rows="3" placeholder="<?php echo esc_html(__('The more detail you can share, the better.', 'wp-reviews-plugin-for-google')); ?>"></textarea>
</div>
<div class="ti-form-group">
<label>
<?php echo esc_html(__('Attach images', 'wp-reviews-plugin-for-google')); ?>
(<?php
/* translators: %s: snipboard.io */
echo wp_kses_post(sprintf(__('use %s for image share', 'wp-reviews-plugin-for-google'), '<a href="https://snipboard.io/" target="_blank">snipboard.io</a>'));
?>)
</label>
<textarea class="ti-form-control" name="attachments" rows="3" placeholder="<?php echo esc_html(__('URL of images (each on a separate line)', 'wp-reviews-plugin-for-google')); ?>"></textarea>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Please describe your current workaround', 'wp-reviews-plugin-for-google')); ?></label>
<textarea class="ti-form-control" name="workaround" rows="3" placeholder="<?php echo esc_html(__('If you have one - otherwise leave it blank.', 'wp-reviews-plugin-for-google')); ?>"></textarea>
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Your name', 'wp-reviews-plugin-for-google')); ?></label>
<input type="text" class="ti-form-control" name="name" placeholder="<?php echo esc_html(__('The more detail you can share, the better.', 'wp-reviews-plugin-for-google')); ?>" />
</div>
<div class="ti-form-group">
<label><?php echo esc_html(__('Your email address', 'wp-reviews-plugin-for-google')); ?>*</label>
<input type="text" class="ti-form-control" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" />
</div>
<p><?php echo esc_html(__('Thanks for taking the time - we will get back to you as soon as possible to ask a few clarifying question or to give you an update.', 'wp-reviews-plugin-for-google')); ?></p>
<div class="ti-box-footer">
<a href="#" class="btn-send-feature-request ti-btn ti-tooltip ti-toggle-tooltip">
<?php echo esc_html(__('Send feature request', 'wp-reviews-plugin-for-google')); ?>
<span class="ti-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo esc_html(__('Feature request sent', 'wp-reviews-plugin-for-google')); ?>
</span>
</a>
</div>
</form>
</div>
</div>

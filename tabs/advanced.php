<?php
defined('ABSPATH') or die('No script kiddies please!');
if (isset($_GET['toggle_css_inline'])) {
check_admin_referer('ti-toggle-css');
$v = (int)$_GET['toggle_css_inline'];
update_option($pluginManagerInstance->get_option_name('load-css-inline'), $v, false);
if ($v && is_file($pluginManagerInstance->getCssFile())) {
wp_delete_file($pluginManagerInstance->getCssFile());
}
$pluginManagerInstance->handleCssFile();
header('Location: admin.php?page='.esc_attr($_page).'&tab=advanced');
exit;
}
if (isset($_GET['delete_css'])) {
check_admin_referer('ti-delete-css');
if (is_file($pluginManagerInstance->getCssFile())) {
wp_delete_file($pluginManagerInstance->getCssFile());
}
$pluginManagerInstance->handleCssFile();
header('Location: admin.php?page='.esc_attr($_page).'&tab=advanced');
exit;
}
if (isset($_POST['save-notification-email'])) {
check_admin_referer('ti-notification-email-save');
$type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : null;
$type = strtolower(trim($type));
$email = isset($_POST['save-notification-email']) ? sanitize_text_field(wp_unslash($_POST['save-notification-email'])) : null;
$email = strtolower(trim($email));
$pluginManagerInstance->setNotificationParam($type, 'email', $email);
exit;
}
$yesIcon = '<span class="dashicons dashicons-yes-alt"></span>';
$noIcon = '<span class="dashicons dashicons-dismiss"></span>';
$pluginUpdated = ($pluginManagerInstance->get_plugin_current_version() <= "13.3.1");
$cssInline = get_option($pluginManagerInstance->get_option_name('load-css-inline'), 0);
$css = get_option($pluginManagerInstance->get_option_name('css-content'));
$tiSuccess = "";
if (isset($_COOKIE['ti-success'])) {
$tiSuccess = sanitize_text_field(wp_unslash($_COOKIE['ti-success']));
setcookie('ti-success', '', time() - 60, "/");
}
$tiError = null;
$tiCommand = isset($_POST['command']) ? sanitize_text_field(wp_unslash($_POST['command'])) : null;
if (!in_array($tiCommand, [ 'connect', 'disconnect' ])) {
$tiCommand = null;
}
if ($tiCommand === 'connect') {
check_admin_referer('connect-reg_' . $pluginManagerInstance->get_plugin_slug());
$sanitizedEmail = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : "";
$sanitizedPassword = isset($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : "";
if ($sanitizedEmail && $sanitizedPassword) {
$serverOutput = $pluginManagerInstance->connect_trustindex_api([
'signin' => [
'username' => $sanitizedEmail,
'password' => html_entity_decode($sanitizedPassword),
],
'callback' => bin2hex(openssl_random_pseudo_bytes(10))
], 'connect');
if ($serverOutput['success']) {
setcookie('ti-success', 'connected', time() + 60, '/');
header('Location: #trustindex-admin');
exit;
}
else {
$tiError = esc_html(__('Wrong e-mail or password!', 'wp-reviews-plugin-for-google'));
}
}
else {
$tiError = esc_html(__('You must provide a password and a valid e-mail!', 'wp-reviews-plugin-for-google'));
}
}
else if ($tiCommand === 'disconnect') {
check_admin_referer('disconnect-reg_' . $pluginManagerInstance->get_plugin_slug());
delete_option($pluginManagerInstance->get_option_name('subscription-id'));
setcookie('ti-success', 'disconnected', time() + 60, '/');
header('Location: #trustindex-admin');
exit;
}
$trustindexSubscriptionId = $pluginManagerInstance->is_trustindex_connected();
$widgetNumber = $pluginManagerInstance->get_trustindex_widget_number();
?>
<h1 class="ti-header-title"><?php echo esc_html(__('Advanced', 'wp-reviews-plugin-for-google')); ?></h1>
<div class="ti-box">
<div class="ti-box-header"><?php echo esc_html(__('Notifications', 'wp-reviews-plugin-for-google')); ?></div>
<ul class="ti-troubleshooting-checklist">
<li>
<?php echo esc_html(__('Review download available', 'wp-reviews-plugin-for-google')); ?>
<ul>
<li>
<?php
$isNotificationActive = !$pluginManagerInstance->getNotificationParam('review-download-available', 'hidden', false);
echo esc_html(__('Notification', 'wp-reviews-plugin-for-google')) .': '. ($isNotificationActive ? wp_kses_post($yesIcon) : wp_kses_post($noIcon)); ?>
<?php if ($isNotificationActive): ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=advanced&notification=review-download-available&action=hide', 'ti-notification')); ?>" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Disable', 'wp-reviews-plugin-for-google')); ?></a>
<?php else: ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=advanced&notification=review-download-available&action=unhide', 'ti-notification')); ?>" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Enable', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</li>

</ul>
</li>
<li>
<?php echo esc_html(__('Review download finished', 'wp-reviews-plugin-for-google')); ?>
<ul>
<li>
<?php
$isNotificationActive = !$pluginManagerInstance->getNotificationParam('review-download-finished', 'hidden', false);
echo esc_html(__('Notification', 'wp-reviews-plugin-for-google')) .': '. ($isNotificationActive ? wp_kses_post($yesIcon) : wp_kses_post($noIcon)); ?>
<?php if ($isNotificationActive): ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=advanced&notification=review-download-finished&action=hide', 'ti-notification')); ?>" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Disable', 'wp-reviews-plugin-for-google')); ?></a>
<?php else: ?>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=advanced&notification=notification=review-download-finished&action=unhide', 'ti-notification')); ?>" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Enable', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</li>
<li>
<div class="ti-notification-email">
<div class="ti-notice ti-notice-error">
<p><?php echo esc_html(__('Invalid email', 'wp-reviews-plugin-for-google')); ?></p>
</div>
<div class="ti-inner">
<span><?php echo esc_html(__('Send email notification to:', 'wp-reviews-plugin-for-google')); ?></span>
<input type="text" data-type="review-download-finished" placeholder="email@example.com" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-notification-email-save')); ?>" class="ti-form-control" value="<?php echo esc_attr($pluginManagerInstance->getNotificationParam('review-download-finished', 'email', get_option('admin_email'))); ?>" />
<a href="#" class="ti-btn btn-notification-email-save"><?php echo esc_html(__('Save', 'wp-reviews-plugin-for-google')); ?></a>
</div>
<div class="ti-info-text"><?php echo esc_html(__('Leave the field blank if you do not want email notification.', 'wp-reviews-plugin-for-google')); ?></div>
</div>
</li>
</ul>
</li>
</ul>
</div>
<div class="ti-box">
<div class="ti-box-header"><?php echo esc_html(__("Troubleshooting", 'wp-reviews-plugin-for-google')); ?></div>
<p class="ti-bold"><?php echo esc_html(__('If you have any problem, you should try these steps:', 'wp-reviews-plugin-for-google')); ?></p>
<ul class="ti-troubleshooting-checklist">
<li>
<?php echo esc_html(__("Trustindex plugin", 'wp-reviews-plugin-for-google')); ?>
<ul>
<li>
<?php echo esc_html(__('Use the latest version:', 'wp-reviews-plugin-for-google')) .' '. ($pluginUpdated ? wp_kses_post($yesIcon) : wp_kses_post($noIcon)); ?>
<?php if (!$pluginUpdated): ?>
<a href="/wp-admin/plugins.php" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Update', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
</li>
<li>
<?php echo esc_html(__('Use automatic plugin update:', 'wp-reviews-plugin-for-google')); ?>
<a href="<?php echo esc_url(admin_url('plugins.php?s='.esc_attr($pluginManagerInstance->get_plugin_slug()))); ?>" class="ti-btn ti-btn-loading-on-click"><?php echo esc_html(__('Check', 'wp-reviews-plugin-for-google')); ?></a>
<div class="ti-notice ti-notice-warning">
<p><?php echo esc_html(__('You should enable it, to get new features and fixes automatically, right after they published!', 'wp-reviews-plugin-for-google')); ?></p>
</div>
</li>
</ul>
</li>
<?php if ($css): ?>
<li>
CSS
<ul>
<li><?php
$uploadDir = dirname($pluginManagerInstance->getCssFile());
WP_Filesystem();
echo esc_html(__('writing permission', 'wp-reviews-plugin-for-google')) .' (<strong>'. esc_html($uploadDir) .'</strong>): '. ($wp_filesystem->is_writable($uploadDir) ? wp_kses_post($yesIcon) : wp_kses_post($noIcon)); ?>
</li>
<li>
<?php echo esc_html(__('CSS content:', 'wp-reviews-plugin-for-google')); ?>
<?php
if (is_file($pluginManagerInstance->getCssFile())) {
$content = file_get_contents($pluginManagerInstance->getCssFile());
if ($content === $css) {
echo wp_kses_post($yesIcon);
}
else {
echo wp_kses_post($noIcon) .' '. esc_html(__("corrupted", 'wp-reviews-plugin-for-google')) .'
<div class="ti-notice ti-notice-warning">
<p><a href="'. esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=advanced&delete_css', 'ti-delete-css')) .'">'.
/* translators: %s: file path */
wp_kses_post(sprintf(__("Delete the CSS file at <strong>%s</strong>.", 'wp-reviews-plugin-for-google'), $pluginManagerInstance->getCssFile()))
.'</a></p>
</div>';
}
}
else {
echo wp_kses_post($noIcon);
}
?>
<span class="ti-checkbox ti-checkbox-row" style="margin-top: 5px">
<input type="checkbox" value="1" <?php if ($cssInline): ?>checked<?php endif;?> onchange="window.location.href = '?page=<?php echo esc_attr($_page); ?>&tab=advanced&_wpnonce=<?php echo esc_attr(wp_create_nonce('ti-toggle-css')); ?>&toggle_css_inline=' + (this.checked ? 1 : 0)">
<label><?php echo esc_html(__('Enable CSS internal loading', 'wp-reviews-plugin-for-google')); ?></label>
</span>
</li>
</ul>
</li>
<?php endif; ?>
<li>
<?php echo esc_html(__('If you are using cacher plugin, you should:', 'wp-reviews-plugin-for-google')); ?>
<ul>
<li><?php echo esc_html(__('clear the cache', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php echo esc_html(__("exclude Trustindex's JS file:", 'wp-reviews-plugin-for-google')); ?> <strong><?php echo esc_url('https://cdn.trustindex.io/'); ?>loader.js</strong>
<ul>
<li><a href="#" onclick="jQuery('#list-w3-total-cache').toggle(); return false;">W3 Total Cache</a>
<ol id="list-w3-total-cache" style="display: none;">
<li><?php echo esc_html(__('Navigate to', 'wp-reviews-plugin-for-google')); ?> "Performance" > "Minify"</li>
<li><?php echo esc_html(__('Scroll to', 'wp-reviews-plugin-for-google')); ?> "Never minify the following JS files"</li>
<li><?php echo esc_html(__('In a new line, add', 'wp-reviews-plugin-for-google')); ?> https://cdn.trustindex.io/*</li>
<li><?php echo esc_html(__('Save', 'wp-reviews-plugin-for-google')); ?></li>
</ol>
</li>
</ul>
</li>
</ul>
</li>
<li>
<?php
$pluginUrl = 'https://wordpress.org/support/plugin/' . $pluginManagerInstance->get_plugin_slug();
$screenshotUrl = 'https://snipboard.io';
$screencastUrl = 'https://streamable.com/upload-video';
$pastebinUrl = 'https://pastebin.com';
/* translators: %s: plugin's support forum link */
echo wp_kses_post(sprintf(__('If the problem/question still exists, please create an issue here: %s', 'wp-reviews-plugin-for-google'), '<a href="'. $pluginUrl .'" target="_blank">'. $pluginUrl .'</a>'));
?>
<br />
<?php echo esc_html(__('Please help us with some information:', 'wp-reviews-plugin-for-google')); ?>
<ul>
<li><?php echo esc_html(__('Describe your problem', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php
/* translators: %s: link */
echo wp_kses_post(sprintf(__('You can share a screenshot with %s', 'wp-reviews-plugin-for-google'), '<a href="'. $screenshotUrl .'" target="_blank">'. $screenshotUrl .'</a>'));
?></li>
<li><?php
/* translators: %s: link */
echo wp_kses_post(sprintf(__('You can share a screencast video with %s', 'wp-reviews-plugin-for-google'), '<a href="'. $screencastUrl .'" target="_blank">'. $screencastUrl .'</a>'));
?></li>
<li><?php
/* translators: %s: link */
echo wp_kses_post(sprintf(__('If you have an (webserver) error log, you can copy it to the issue, or link it with %s', 'wp-reviews-plugin-for-google'), '<a href="'. $pastebinUrl .'" target="_blank">'. $pastebinUrl .'</a>'));
?></li>
<li><?php echo esc_html(__('And include the information below:', 'wp-reviews-plugin-for-google')); ?></li>
</ul>
</li>
</ul>
<textarea class="ti-troubleshooting-info" readonly><?php include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'troubleshooting.php'; ?></textarea>
<a href=".ti-troubleshooting-info" class="ti-btn ti-pull-right ti-tooltip toggle-tooltip btn-copy2clipboard">
<?php echo esc_html(__('Copy to clipboard', 'wp-reviews-plugin-for-google')) ;?>
<span class="ti-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo esc_html(__('Copied', 'wp-reviews-plugin-for-google')); ?>
</span>
</a>
<div class="clear"></div>
</div>
<div class="ti-box">
<div class="ti-box-header"><?php echo esc_html(__('Re-create plugin', 'wp-reviews-plugin-for-google')); ?></div>
<p><?php echo wp_kses_post(__('Re-create the database tables of the plugin.<br />Please note: this removes all settings and reviews.', 'wp-reviews-plugin-for-google')); ?></p>
<a href="<?php echo esc_url(wp_nonce_url('?page='.esc_attr($_page).'&tab=free-widget-configurator&recreate', 'ti-recreate')); ?>" class="ti-btn ti-btn-loading-on-click ti-pull-right"><?php echo esc_html(__('Re-create plugin', 'wp-reviews-plugin-for-google')); ?></a>
<div class="clear"></div>
</div>
<div class="ti-box">
<div class="ti-box-header"><?php echo esc_html(__('Translation', 'wp-reviews-plugin-for-google')); ?></div>
<p>
<?php echo esc_html(__('If you notice an incorrect translation in the plugin text, please report it here:', 'wp-reviews-plugin-for-google')); ?>
 <a href="mailto:support@trustindex.io">support@trustindex.io</a>
</p>
</div>
<?php include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'feature-request.php'; ?>
<div class="ti-box" id="trustindex-admin">
<div class="ti-box-header"><?php echo esc_html(__('Connect your Trustindex account', 'wp-reviews-plugin-for-google')); ?></div>
<?php if ($tiSuccess === 'connected'): ?>
<?php echo wp_kses_post($pluginManager::get_noticebox('success', esc_html(__('Trustindex account successfully connected!', 'wp-reviews-plugin-for-google')))); ?>
<?php elseif ($tiSuccess === 'disconnected'): ?>
<?php echo wp_kses_post($pluginManager::get_noticebox('success', esc_html(__('Trustindex account successfully disconnected!', 'wp-reviews-plugin-for-google')))); ?>
<?php endif; ?>
<?php if ($tiError): ?>
<?php echo wp_kses_post($pluginManager::get_noticebox('error', $tiError)); ?>
<?php endif; ?>
<?php if ($trustindexSubscriptionId): ?>
<?php
$tiWidgets = $pluginManagerInstance->get_trustindex_widgets();
$tiPackage = is_array($tiWidgets) && $tiWidgets && isset($tiWidgets[0]['package']) ? $tiWidgets[0]['package'] : null;
?>
<p>
<?php
$tiAccountText = esc_html(__('Trustindex account', 'wp-reviews-plugin-for-google'));
/* translators: %s: Trustindex account */
echo esc_html(sprintf(__('Your %s is connected.', 'wp-reviews-plugin-for-google'), $tiAccountText));
?><br />
- <?php echo esc_html(__('Your subscription ID:', 'wp-reviews-plugin-for-google')); ?> <strong><?php echo esc_html($trustindexSubscriptionId); ?></strong><br />
<?php if ($tiPackage): ?>
- <?php echo esc_html(__('Your package:', 'wp-reviews-plugin-for-google')); ?> <strong><?php echo esc_html($tiPackage); ?></strong>
<?php endif; ?>
</p>
<?php if ($tiPackage === 'free'): ?>
<?php
$tabName = esc_html(__('Free Widget Configurator', 'wp-reviews-plugin-for-google'));
/* translators: %s: link */
echo wp_kses_post($pluginManager::get_noticebox('error', esc_html(sprintf(__("Once the trial period has expired, the widgets will not appear. You can subscribe or switch back to the \"%s\" tab", 'wp-reviews-plugin-for-google'), [ $tabName ]))));
?>
<?php elseif ($tiPackage === 'trial'): ?>
<?php
$tabName = esc_html(__('Free Widget Configurator', 'wp-reviews-plugin-for-google'));
/* translators: %s: link */
echo wp_kses_post($pluginManager::get_noticebox('warning', esc_html(sprintf(__("Once the trial period has expired, the widgets will not appear. You can subscribe or switch back to the \"%s\" tab", 'wp-reviews-plugin-for-google'), [ $tabName ]))));
?>
<?php endif; ?>
<form method="post" class="ti-mt-0" action="">
<input type="hidden" name="command" value="disconnect" />
<?php wp_nonce_field('disconnect-reg_' . $pluginManagerInstance->get_plugin_slug()); ?>
<button class="ti-btn ti-btn-loading-on-click ti-pull-right" type="submit"><?php echo esc_html(__('Disconnect', 'wp-reviews-plugin-for-google')); ?></button>
<div class="clear"></div>
</form>
<?php else: ?>
<p><?php
/* translators: %s: Plugin name */
echo esc_html(sprintf(__('You can connect your %s with your Trustindex account, and can display your widgets easier.', 'wp-reviews-plugin-for-google'), 'Widgets for Google Reviews'));
?></p>
<form id="form-connect" method="post" action="#trustindex-admin">
<input type="hidden" name="command" value="connect" />
<?php wp_nonce_field('connect-reg_' . $pluginManagerInstance->get_plugin_slug()); ?>
<div class="ti-form-group">
<label>E-mail</label>
<input type="email" placeholder="E-mail" name="email" class="ti-form-control" required="required" id="ti-reg-email2" value="<?php echo esc_attr($current_user->user_email); ?>" />
</div>
<div class="ti-form-group ti-mb-1">
<label><?php echo esc_html(__('Password', 'wp-reviews-plugin-for-google')); ?></label>
<input type="password" placeholder="<?php echo esc_html(__('Password', 'wp-reviews-plugin-for-google')); ?>" name="password" class="ti-form-control" required="required" id="ti-reg-password2" />
<span class="dashicons dashicons-visibility ti-toggle-password"></span>
</div>
<p class="ti-text-center">
<button type="submit" class="ti-btn ti-btn-loading-on-click ti-mb-1"><?php echo esc_html(__('CONNECT ACCOUNT', 'wp-reviews-plugin-for-google'));?></button>
<br />
<a class="ti-btn ti-btn-default" href="<?php echo esc_url('https://admin.trustindex.io/'); ?>forgot-password" target="_blank"><?php echo esc_html(__('Have you forgotten your password?', 'wp-reviews-plugin-for-google')); ?></a>
<a class="ti-btn ti-btn-default" href="https://www.trustindex.io/?a=sys&c=wp-google-4" target="_blank"><?php echo esc_html(__('Create a new Trustindex account', 'wp-reviews-plugin-for-google'));?></a>
</p>
</form>
<?php endif; ?>
<?php if ($trustindexSubscriptionId): ?>
<div class="ti-box-header ti-mt-2"><?php echo esc_html(__('Manage your Trustindex account', 'wp-reviews-plugin-for-google')); ?></div>
<a class="ti-btn" href="<?php echo esc_url('https://admin.trustindex.io/'); ?>widget" target="_blank"><?php echo esc_html(__("Go to Trustindex's admin!", 'wp-reviews-plugin-for-google')); ?></a>
<div class="ti-box-header ti-mt-2"><?php echo esc_html(__('Insert your widget into your wordpress site using shortcode', 'wp-reviews-plugin-for-google')); ?></div>
<?php if ($trustindexSubscriptionId): ?>
<?php if ($widgetNumber): ?>
<p><?php
/* translators: %d: widgets number */
echo esc_html(sprintf(__('You have got %d widgets saved in Trustindex admin.', 'wp-reviews-plugin-for-google'), $widgetNumber));
?></p>
<?php foreach ($tiWidgets as $wcIndex => $wc): ?>
<p class="ti-bold"><?php echo esc_html($wc['name']); ?>:</p>
<?php if ($wc['widgets']): ?>
<ul style="padding-left: 15px">
<?php foreach ($wc['widgets'] as $wiNum => $w): ?>
<li>
<?php echo esc_html($wiNum + 1); ?>.
<a href=".ti-w-<?php echo esc_attr($wcIndex .'-'. $wiNum); ?>" class="btn-toggle" data-ti-id="<?php echo esc_attr($w['id']); ?>"><?php echo esc_html($w['name']); ?></a>
<div style="display: none; padding: 15px 30px" class="ti-w-<?php echo esc_attr($wcIndex .'-'. $wiNum); ?>">
<?php
$trustindexShortCodeText = $pluginManagerInstance->get_shortcode_name().' data-widget-id="'.$w['id'].'"';
include(plugin_dir_path(__FILE__) . '../include/shortcode-paste-box.php');
?>
</div>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
-
<?php endif; ?>
<?php endforeach; ?>
<?php else: ?>
<?php echo wp_kses_post($pluginManager::get_noticebox('error', esc_html(__('You have no widgets saved!', 'wp-reviews-plugin-for-google')))); ?>
<?php endif; ?>
<a class="ti-btn" href="<?php echo esc_url('https://admin.trustindex.io/'); ?>widget" target="_blank"><?php echo esc_html(__('Create more!', 'wp-reviews-plugin-for-google')); ?></a>
<?php endif; ?>
<?php endif; ?>
</div>

<?php
/*
Plugin Name: Google Review by Emon
Plugin Title: Google Review by Emon
Plugin URI: https://wordpress.org/plugins/wp-reviews-plugin-for-google/
Description: Embed Google reviews fast and easily into your WordPress site. Increase SEO, trust and sales using Google reviews.
Tags: google, google places reviews, reviews, widget, google business
Author: Emon
Author URI: https://www.trustindex.io/
Contributors: trustindex
License: GPLv2 or later
Version: 13.3.6
Requires at least: 6.2
Requires PHP: 7.4
Text Domain: wp-reviews-plugin-for-google
Domain Path: /languages
Donate link: https://www.trustindex.io/prices/
*/
/*
Copyright 2019 Trustindex Kft (email: support@trustindex.io)
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once plugin_dir_path(__FILE__) . 'include' . DIRECTORY_SEPARATOR . 'cache-plugin-filters.php';
require_once plugin_dir_path(__FILE__) . 'trustindex-plugin.class.php';
$trustindex_pm_google = new TrustindexPlugin_google("google", __FILE__, "13.3.1", "Google Review by Emon", "Google");
$pluginManager = 'TrustindexPlugin_google';
$pluginManagerInstance = $trustindex_pm_google;

// Load GitHub Auto-Updater System for EmonAhammed1/Google-Review-Plugin
if (is_admin()) {
    require_once __DIR__ . '/class-github-updater.php';
    $gh_user = get_option('trustindex_github_user', 'EmonAhammed1');
    $gh_repo = get_option('trustindex_github_repo', 'Google-Review-Plugin');
    new Google_Review_Emon_GitHub_Updater(__FILE__, $gh_user, $gh_repo, '13.3.6');
}

// 100% Automatic Background WP-Cron & Live Frontend Auto-Sync Engine
add_action('trustindex_auto_sync_google_reviews', function() use ($pluginManagerInstance) {
    if (method_exists($pluginManagerInstance, 'auto_sync_reviews')) {
        $pluginManagerInstance->auto_sync_reviews();
    }
});
if (!wp_next_scheduled('trustindex_auto_sync_google_reviews')) {
    wp_schedule_event(time(), 'hourly', 'trustindex_auto_sync_google_reviews');
}

// Live Frontend Auto-Sync AJAX Handlers
add_action('wp_ajax_nopriv_trustindex_auto_sync', function() use ($pluginManagerInstance) {
    if (method_exists($pluginManagerInstance, 'auto_sync_reviews')) {
        $pluginManagerInstance->auto_sync_reviews();
    }
    wp_send_json_success();
});
add_action('wp_ajax_trustindex_auto_sync', function() use ($pluginManagerInstance) {
    if (method_exists($pluginManagerInstance, 'auto_sync_reviews')) {
        $pluginManagerInstance->auto_sync_reviews();
    }
    wp_send_json_success();
});

add_action('wp_ajax_trustindex_manual_sync', function() use ($pluginManagerInstance) {
    if (method_exists($pluginManagerInstance, 'auto_sync_reviews')) {
        $pluginManagerInstance->auto_sync_reviews(true);
    }
    wp_send_json_success(['message' => 'Reviews synced successfully']);
});

add_action('admin_init', function() { ob_start(); });
register_activation_hook(__FILE__, [ $pluginManagerInstance, 'activate' ]);
register_deactivation_hook(__FILE__, [ $pluginManagerInstance, 'deactivate' ]);
add_action('plugins_loaded', [ $pluginManagerInstance, 'load' ]);
add_action('wp_head', function() use($pluginManagerInstance) {
$url = isset($_SERVER['REQUEST_URI']) ? sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])) : '';
echo '<meta name="ti-site-data" content="'.esc_attr(base64_encode(json_encode([
'r' =>
'1:'.$pluginManagerInstance->getRegistrationCount(1) .
'!7:'.$pluginManagerInstance->getRegistrationCount(7) .
'!30:'.$pluginManagerInstance->getRegistrationCount(30),
'o' => wp_nonce_url(get_site_url() .'?'.http_build_query([
'ti-online-users-'.$pluginManagerInstance->getShortName() => 1,
'p' => esc_html($url),
]), 'ti-online-users-'.$pluginManagerInstance->getShortName()),
]))).'" />';
});
add_action('init', function() use($pluginManagerInstance) {
if (isset($_GET['ti-online-users-'.$pluginManagerInstance->getShortName()])) {
check_admin_referer('ti-online-users-'.$pluginManagerInstance->getShortName());
$page = isset($_REQUEST['p']) ? sanitize_text_field(wp_unslash($_REQUEST['p'])) : '';
$md5Value = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
$md5Value .= isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
$key = 'ti_uid_' . md5($md5Value);
$userId = get_transient($key);
if (!$userId) {
$userId = uniqid('', true);
set_transient($key, $userId, 3600);
}
echo esc_html($pluginManagerInstance->getOnlineUsers($userId, $page));
exit;
}
});
add_action('wp_insert_site', function($site) use($pluginManagerInstance) {
switch_to_blog($site->blog_id);
$tiReviewsTableName = $pluginManagerInstance->get_tablename('reviews');
$tiViewsTableName = $pluginManagerInstance->get_tablename('views');
include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'schema.php';
foreach (array_keys($ti_db_schema) as $tableName) {
if (!$pluginManagerInstance->is_table_exists($tableName)) {
dbDelta(trim($ti_db_schema[ $tableName ]));
}
}
restore_current_blog();
});
add_action('admin_menu', [ $pluginManagerInstance, 'add_setting_menu' ], 10);
add_filter('plugin_action_links', [ $pluginManagerInstance, 'add_plugin_action_links' ], 10, 2);
add_filter('plugin_row_meta', [ $pluginManagerInstance, 'add_plugin_meta_links' ], 10, 2);
if (!function_exists('register_block_type')) {
add_action('widgets_init', [ $pluginManagerInstance, 'init_widget' ]);
add_action('widgets_init', [ $pluginManagerInstance, 'register_widget' ]);
}
add_action('init', function() {
wp_register_script('trustindex-loader-js', 'https://cdn.trustindex.io/loader.js', [], true, [
'strategy' => 'async',
'in_footer' => true,
]);
});
add_action('init', [ $pluginManagerInstance, 'init_shortcode' ]);
add_filter('script_loader_tag', function($tag, $handle) {
if ('trustindex-loader-js' === $handle) {
$tag = str_replace('<script ', '<script data-ccm-injected="1" ', $tag);
}
return $tag;
}, 10, 2);
add_action('elementor/controls/controls_registered', function($controlsManager) {
require_once(__DIR__ . '/include/elementor-widgets.php');
$controlsManager->register_control('choose', new \Elementor\Control_Choose2());
});
add_action('elementor/widgets/register', function ($widgetsManager) use ($pluginManagerInstance) {
if (method_exists($widgetsManager, 'register')) {
require_once(__DIR__ . '/include/elementor-widgets.php');
$widgetsManager->register(new \Elementor\TrustrindexElementorWidget_Google([], [$pluginManagerInstance]));
}
});
add_action('elementor/widgets/widgets_registered', function ($widgetsManager) use ($pluginManagerInstance) {
if (method_exists($widgetsManager, 'register_widget_type')) {
require_once(__DIR__ . '/include/elementor-widgets.php');
$widgetsManager->register_widget_type(new \Elementor\TrustrindexElementorWidget_Google([], [$pluginManagerInstance]));
}
});
add_action('init', [ $pluginManagerInstance, 'register_tinymce_features' ]);
add_action('wp_ajax_list_trustindex_widgets', [ $pluginManagerInstance, 'list_trustindex_widgets_ajax' ]);
add_action('admin_enqueue_scripts', [ $pluginManagerInstance, 'trustindex_add_scripts' ]);
add_action('rest_api_init', [ $pluginManagerInstance, 'init_restapi' ]);
if (class_exists('Woocommerce') && !class_exists('TrustindexCollectorPlugin') && !function_exists('ti_woocommerce_notice')) {
function ti_woocommerce_notice() {
global $pluginManager;
if (!current_user_can($pluginManager::$permissionNeeded)) {
return;
}
$wcNotification = get_option('trustindex-wc-notification', time() - 1);
if ($wcNotification == 'hide' || (int)$wcNotification > time()) {
return;
}
?>
<div class="notice notice-warning trustindex-notification-row is-dismissible" style="margin: 5px 0 15px">
<p><strong><?php
/* translators: 1: plugin url, 2: plugin name */
echo wp_kses_post(sprintf(__('Download our new <a href="%1$s" target="_blank">%2$s</a> plugin and get features for free!', 'wp-reviews-plugin-for-google'), 'https://wordpress.org/plugins/customer-reviews-collector-for-woocommerce/', 'Customer Reviews Collector for WooCommerce'));
?></strong></p>
<ul style="list-style-type: disc; margin-left: 10px; padding-left: 15px">
<li><?php echo esc_html(__('Send unlimited review invitations for free', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php echo esc_html(__('E-mail templates are fully customizable', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php echo esc_html(__('Collect reviews on 100+ review platforms (Google, Facebook, Yelp, etc.)', 'wp-reviews-plugin-for-google')); ?></li>
</ul>
<p>
<a href="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wp-reviews-plugin-for-google/settings.php&wc_notification=open"), 'ti-wc-notification')); ?>" target="_blank" class="ti-close-notification" style="text-decoration: none">
<button class="button button-primary"><?php echo esc_html(__('Download plugin', 'wp-reviews-plugin-for-google')); ?></button>
</a>
<a href="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=wp-reviews-plugin-for-google/settings.php&wc_notification=hide"), 'ti-wc-notification')); ?>"target="_blank" class="ti-hide-notification" style="text-decoration: none">
<button class="button button-secondary"><?php echo esc_html(__('Do not remind me again', 'wp-reviews-plugin-for-google')); ?></button>
</a>
</p>
</div>
<?php
}
add_action('admin_notices', 'ti_woocommerce_notice');
}
add_action('admin_notices', function() use ($pluginManager, $pluginManagerInstance) {
if (!current_user_can($pluginManager::$permissionNeeded)) {
return;
}
foreach ($pluginManagerInstance->getNotificationOptions() as $type => $options) {
if (!$pluginManagerInstance->isNotificationActive($type)) {
continue;
}
echo '<div class="notice notice-'. esc_attr($options['type']) .' '. ($options['is-closeable'] ? 'is-dismissible' : '') .' trustindex-notification-row '. esc_attr($options['extra-class']).'" data-close-url="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=close'), 'ti-notification')) .'">';
if ($type === 'rate-us') {
echo '<div class="trustindex-star-row">&starf;&starf;&starf;&starf;&starf;</div>';
}
echo '<p>'. wp_kses_post($options['text']) .'<p>';
if ($type === 'rate-us') {
echo '
<a href="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=open'), 'ti-notification')) .'" class="ti-close-notification" target="_blank">
<button class="button ti-button-primary button-primary">'. esc_html(__('Write a review', 'wp-reviews-plugin-for-google')) .'</button>
</a>
<a href="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=later'), 'ti-notification')) .'" class="ti-remind-later">
'. esc_html(__('Maybe later', 'wp-reviews-plugin-for-google')) .'
</a>
<a href="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=hide'), 'ti-notification')) .'" class="ti-hide-notification" style="float: right; margin-top: 14px">
'. esc_html(__('Do not remind me again', 'wp-reviews-plugin-for-google')) .'
</a>
';
}
else {
echo '
<a href="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=open'), 'ti-notification')) .'">
<button class="button button-primary">'. esc_html($options['button-text']) .'</button>
</a>';
if ($options['remind-later-button']) {
echo '
<a href="'. esc_url(wp_nonce_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/settings.php&notification='. $type .'&action=later'), 'ti-notification')) .'" class="ti-remind-later" style="margin-left: 5px">
'. esc_html(__('Remind me later', 'wp-reviews-plugin-for-google')) .'
</a>';
}
}
echo '
</p>
</div>';
}
});
unset($pluginManagerInstance);
?>

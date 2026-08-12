<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class TrustindexWidget_google extends WP_Widget {
private $widget_fields = [
'ti-widget-ID' => [
'default' => '',
'required' => true,
'placeholder' => 'eg.: 478dcc2136263f2b3a3726ff',
'name' => 'Trustindex Widget ID',
'help' => null,
'help-icon' => '<span class="dashicons dashicons-editor-help btn-insert-tooltip"></span>'
],
];
private $errors = array();
public function __construct()
{
parent::__construct(
'trustindex_google_widget',
'Review Widgets for Google',
[
'classname' => 'trustindex-widget',
'description' => 'Embed Google reviews fast and easily into your WordPress site. Increase SEO, trust and sales using Google reviews.'
]
);
}
function widget($args, $instance)
{
global $wpdb;
global $trustindex_pm_google;
$pluginManager = 'TrustindexPlugin_google';
$pluginManagerInstance = $trustindex_pm_google;
if ($pluginManagerInstance->is_enabled()) {
extract($args);
echo wp_kses_post($before_widget);
$wasError = false;
foreach ($this->widget_fields as $fname => $fparams) {
if ($fparams['required'] && (!isset($instance[ $fname ]) || $instance[ $fname ] == "")) {
$wasError = true;
break;
}
}
if (!$wasError && $instance['ti-widget-ID']) {
echo wp_kses($pluginManagerInstance->renderWidgetFrontend($instance['ti-widget-ID']), $pluginManager::$allowedAttributesForWidget);
} else if ($pluginManagerInstance->is_noreg_linked()) {
$html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $pluginManagerInstance->renderWidgetFrontend());
echo wp_kses($html, $pluginManager::$allowedAttributesForWidget);
if (!is_file($chosedPlatform->getCssFile()) || get_option($chosedPlatform->get_option_name('load-css-inline'), 0)) {
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<style type="text/css">'.get_option($chosedPlatform->get_option_name('css-content')).'</style>';
}
} else {
/* translators: %s: URL */
echo wp_kses_post($pluginManagerInstance->frontEndErrorForAdmins(sprintf(__("Please fill out <strong>all the required fields</strong> in the <a href='%s'>widget settings</a> page", 'wp-reviews-plugin-for-google'), admin_url('admin.php?page='.$pluginManagerInstance->get_plugin_slug().'/settings.php'))));
}
echo wp_kses_post($after_widget);
} else {
}
}
function form($instance)
{
global $wp_version;
global $trustindex_pm_google;
$pluginManager = 'TrustindexPlugin_google';
$pluginManagerInstance = $trustindex_pm_google;
$tiWidgets = $pluginManagerInstance->get_trustindex_widgets();
$selectedWidgetId = isset($instance['ti-widget-ID']) ? esc_attr($instance['ti-widget-ID']) : $this->widget_fields['ti-widget-ID']['default'];
?>
<div class="trustindex-widget-admin">
<?php if ($pluginManagerInstance->is_trustindex_connected()): ?>
<?php if ($tiWidgets): ?>
<h2><?php echo esc_html(__('Your saved widgets', 'wp-reviews-plugin-for-google')); ?></h2>
<?php foreach ($tiWidgets as $wc): ?>
<p><strong><?php echo esc_html($wc['name']); ?>:</strong></p>
<p>
<?php foreach ($wc['widgets'] as $w): ?>
<a href="#" class="btn-copy-widget-id <?php if ($selectedWidgetId === $w['id']): ?>text-danger<?php endif; ?>" data-ti-id="<?php echo esc_attr($w['id']); ?>">
<span class="dashicons <?php if ($selectedWidgetId === $w['id']): ?>dashicons-yes<?php else: ?>dashicons-admin-post<?php endif; ?>"></span>
<?php echo esc_html($w['name']); ?>
</a><br />
<?php endforeach; ?>
</p>
<?php endforeach; ?>
<?php else: ?>
<?php echo wp_kses_post($pluginManager::get_alertbox('warning',
esc_html(__('You have no widget saved!', 'wp-reviews-plugin-for-google')) . ' '
. "<a target='_blank' href='" . "https://admin.trustindex.io/" . "widget'>". esc_html(__("Let's go, create amazing widgets for free!", 'wp-reviews-plugin-for-google'))."</a>"
)); ?>
<?php endif; ?>
<?php foreach ($this->widget_fields as $fname => $fparams): ?>
<div class="form-group">
<div class="col-sm-12">
<label class="<?php if (isset($this->errors[ $fname ])):?>text-danger<?php endif; ?>">
<?php echo esc_html($fparams['name']); ?> <?php if ($fparams['required']): ?><strong class="text-danger">*</strong><?php endif; ?>
<?php if ($fparams['help-icon']): ?>
<?php echo wp_kses_post($fparams['help-icon']); ?>
<?php endif; ?>
</label>
<input
type="text"
placeholder="<?php echo esc_attr($fparams['placeholder']); ?>"
id="<?php echo esc_attr($this->get_field_id($fname)); ?>"
name="<?php echo esc_attr($this->get_field_name($fname)); ?>"
value="<?php echo esc_attr(isset($instance[ $fname ]) ? $instance[ $fname ] : $fparams['default']); ?>"
class="form-control"
<?php if ($fparams['required']): ?>required="required"<?php endif; ?>
/>
<?php if ($fparams['help']): ?>
<small class="text-muted"><?php echo wp_kses_post($fparams['help']); ?></small>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
<div class="help-block block-help-template">
<span class="dashicons dashicons-dismiss"></span>
<p>
Check our portal, <a href="<?php echo esc_url('https://admin.trustindex.io/'); ?>widget" target="_blank">list your widgets</a> and find IDs in the first colums.
</p>
<img src="<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('static/img/help-where-is-id.jpg')); ?>" alt="ID column here: <?php echo esc_url('https://admin.trustindex.io/'); ?>widget" />
</div>
<?php else: ?>
<?php echo wp_kses_post($pluginManager::get_alertbox('warning',
esc_html(__('You have not set up your Trustindex account yet!', 'wp-reviews-plugin-for-google')) . ' ' .
esc_html(__('You can only list 10 reviews without it.', 'wp-reviews-plugin-for-google')) . '<br>'
/* translators: %s: URL */
. wp_kses_post(sprintf(__("Go to <a href='%s'>plugin setup page</a> to complete the one-step setup guide and enjoy the full functionalization!", 'wp-reviews-plugin-for-google'), admin_url('admin.php?page='.$pluginManagerInstance->get_plugin_slug().'/settings.php&tab=setup_trustindex')))
)); ?>
<?php endif; ?>
</div>
<?php
}
}
?>

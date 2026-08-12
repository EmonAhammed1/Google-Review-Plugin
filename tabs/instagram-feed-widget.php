<?php
defined('ABSPATH') or die('No script kiddies please!');
update_option($pluginManagerInstance->get_option_name('instagram-promo-opened'), 1, false);
?>
<div class="ti-container ti-narrow-page">
<h1 class="ti-header-title">Instagram Feed Widget</h1>
<div class="ti-preview-boxes-container">
<div class="ti-full-width">
<div class="ti-box">
<div class="ti-box-inner">
<div class="ti-box-header"><?php
/* translators: %s: Feed */
echo esc_html(sprintf(__('Display your %s with our free Widgets!', 'wp-reviews-plugin-for-google'), ' Feed'));
?></div>
<img class="ti-mb-2" src="<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('static/img/instagram-feed-widget.jpg')); ?>" />
<a class="ti-btn" href="https://wordpress.org/plugins/social-photo-feed-widget" target="_blank"><?php
/* translators: %s: Instagram Feed */
echo esc_html(sprintf(__('Create %s Widgets for Free', 'wp-reviews-plugin-for-google'), 'Instagram Feed'));
?></a>
<div class="ti-section-title"><strong><?php echo esc_html(__('Free features', 'wp-reviews-plugin-for-google')); ?></strong></div>
<ul class="ti-check-list">
<li><?php
/* translators: %s: Slider, Grid, List, Masonry */
echo wp_kses_post(sprintf(__('Multiple layout options: %s', 'wp-reviews-plugin-for-google'), '<strong>Slider, Grid, List, Masonry</strong>'));
?></li>
<li><strong><?php echo esc_html(__('Multiple card layouts', 'wp-reviews-plugin-for-google')); ?></strong></li>
<li><strong><?php echo esc_html(__('Automatic, daily updates', 'wp-reviews-plugin-for-google')); ?></strong></li>
<li><?php echo esc_html(__('Customizable', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php echo wp_kses_post(__('<strong>Likes and Comments Count</strong>: Display the number of likes and comments for each post', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php echo wp_kses_post(__('Fully responsive and <strong>mobile-friendly</strong>: a great layout on any screen size and container width', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php
/* translators: %s: Instagram */
echo esc_html(sprintf(__('Includes a Follow on %s button', 'wp-reviews-plugin-for-google'), 'Instagram'));
?></li>
<li><?php echo esc_html(__('Showcase a header at the top of your feed widget', 'wp-reviews-plugin-for-google')); ?></li>
<li><?php
/* translators: %s: Instagram */
echo wp_kses_post(sprintf(__('<strong>Load more %s photos</strong> with the "Load More" button', 'wp-reviews-plugin-for-google'), 'Instagram'));
?></li>
<li><?php echo esc_html(__('Easy setup process', 'wp-reviews-plugin-for-google')); ?></li>
<li><strong><?php echo esc_html(__('Shortcode integration', 'wp-reviews-plugin-for-google')); ?></strong></li>
</ul>
<a class="ti-btn" href="https://wordpress.org/plugins/social-photo-feed-widget" target="_blank"><?php
/* translators: %s: Instagram Feed */
echo esc_html(sprintf(__('Create %s Widgets for Free', 'wp-reviews-plugin-for-google'), 'Instagram Feed'));
?></a>
</div>
</div>
</div>
</div>
</div>

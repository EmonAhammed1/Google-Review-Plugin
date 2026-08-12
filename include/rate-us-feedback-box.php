<?php
defined('ABSPATH') or die('No script kiddies please!');
?>
<div class="ti-box ti-rate-us-box">
<div class="ti-box-header"><?php echo esc_html(__("Do you like our free plugin?", 'wp-reviews-plugin-for-google')); ?></div>
<p><strong><?php echo esc_html(__('Support our work by leaving a review!', 'wp-reviews-plugin-for-google')); ?></strong></p>
<div class="ti-quick-rating" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-rate-us')); ?>">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="ti-star-check" data-value="<?php echo esc_attr($i); ?>"></div><?php endfor; ?>
</div>
</div>
<div class="ti-modal ti-rateus-modal" id="ti-rateus-modal-feedback">
<div class="ti-modal-dialog">
<div class="ti-modal-content">
<span class="ti-close-icon btn-modal-close"></span>
<div class="ti-modal-body">
<div class="ti-rating-textbox">
<div class="ti-quick-rating">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="ti-star-check" data-value="<?php echo esc_attr($i); ?>"></div><?php endfor; ?>
<div class="clear"></div>
</div>
</div>
<div class="ti-rateus-title"><?php echo wp_kses_post(__('Thanks for your feedback!<br />Let us know how we can improve.', 'wp-reviews-plugin-for-google')); ?></div>
<input type="text" class="ti-form-control" placeholder="<?php echo esc_html(__('Contact e-mail', 'wp-reviews-plugin-for-google')); ?>" value="<?php echo esc_attr($current_user->user_email); ?>" />
<textarea class="ti-form-control" placeholder="<?php echo esc_html(__('Describe your experience', 'wp-reviews-plugin-for-google')); ?>"></textarea>
</div>
<div class="ti-modal-footer">
<a href="#" class="ti-btn ti-btn-default btn-modal-close"><?php echo esc_html(__('Cancel', 'wp-reviews-plugin-for-google')); ?></a>
<a href="#" data-nonce="<?php echo esc_attr(wp_create_nonce('ti-rate-us')); ?>" class="ti-btn btn-rateus-support"><?php echo esc_html(__('Contact our support', 'wp-reviews-plugin-for-google')); ?></a>
</div>
</div>
</div>
</div>
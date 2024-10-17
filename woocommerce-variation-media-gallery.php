<?php
/*
Plugin Name: WooCommerce Variation Media Gallery
Description: Adds a media gallery (images and videos) to WooCommerce product variations.
Version: 1.0.0
Author: Mike Opatskyi
Author URI: https://www.creomatix.com
Author Email: creomatix@gmail.com
Text Domain: woocommerce-variation-media-gallery
*/

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  // Enqueue admin scripts and styles
  add_action('admin_enqueue_scripts', 'wvmg_enqueue_admin_scripts');
  function wvmg_enqueue_admin_scripts()
  {
    wp_enqueue_media();
    wp_enqueue_script(
      'wvmg-admin-script',
      plugin_dir_url(__FILE__) . 'assets/admin/js/admin.js',
      array('jquery'),
      '1.0.0',
      true
    );
    wp_enqueue_style(
      'wvmg-admin-style',
      plugin_dir_url(__FILE__) . 'assets/admin/css/admin.css',
      array(),
      '1.0.0'
    );
  }

  // Add media gallery field to variations
  add_action('woocommerce_product_after_variable_attributes', 'wvmg_add_media_gallery_field', 10, 3);
  function wvmg_add_media_gallery_field($loop, $variation_data, $variation)
  {
    $variation_id = $variation->ID;
    ?>
    <div class="form-row form-row-full wvmg_media_gallery">
      <div>
        <label><?php _e('Additional Media Gallery:', 'woocommerce'); ?></label>
        <ul class="wvmg_media_list">
          <?php
          $media_ids = get_post_meta($variation_id, '_wvmg_media_gallery', true);
          if (!empty($media_ids)) {
            $media_ids = explode(',', $media_ids);
            foreach ($media_ids as $media_id) {
              // Get the attachment metadata
              $attachment_metadata = wp_get_attachment_metadata($media_id);
              $mime_type = get_post_mime_type($media_id);

              // Check if the media is a video based on MIME type
              if (strpos($mime_type, 'video') !== false) {
                // If it's a video, display a video element or video icon
                $video_url = wp_get_attachment_url($media_id);
                echo '<li data-attachment_id="' . esc_attr($media_id) . '">
                          <video width="100" height="100" controls>
                              <source src="' . esc_url($video_url) . '" type="' . esc_attr($mime_type) . '">
                              Your browser does not support the video tag.
                          </video>
                          <a href="#" class="remove_media">&times;</a>
                        </li>';
              } else {
                // If it's an image, display the thumbnail
                $thumb_url = wp_get_attachment_thumb_url($media_id);
                echo '<li data-attachment_id="' . esc_attr($media_id) . '">
                          <img src="' . esc_url($thumb_url) . '" />
                          <a href="#" class="remove_media">&times;</a>
                        </li>';
              }
            }
          }
          ?>
        </ul>
        <a href="#" class="button wvmg_upload_button"><?php _e('Add', 'woocommerce'); ?></a>
        <input type="hidden" class="wvmg_media_gallery_input"
          name="variable_wvmg_media_gallery[<?php echo $variation_id; ?>]"
          value="<?php echo esc_attr(get_post_meta($variation_id, '_wvmg_media_gallery', true)); ?>" />
      </div>
    </div>
    <?php
  }

  // Save media gallery data for variations
  add_action('woocommerce_save_product_variation', 'wvmg_save_media_gallery_field', 10, 2);
  function wvmg_save_media_gallery_field($variation_id, $i)
  {
    if (isset($_POST['variable_wvmg_media_gallery'][$variation_id])) {
      $media_ids = sanitize_text_field($_POST['variable_wvmg_media_gallery'][$variation_id]);
      update_post_meta($variation_id, '_wvmg_media_gallery', $media_ids);
    }
  }

  // Display admin notice if WooCommerce is not active
} else {
  add_action('admin_notices', 'wvmg_woocommerce_inactive_notice');
  function wvmg_woocommerce_inactive_notice()
  {
    echo '<div class="error"><p>' . esc_html__('WooCommerce Variation Media Gallery requires WooCommerce to be installed and active.', 'woocommerce-variation-media-gallery') . '</p></div>';
  }
}
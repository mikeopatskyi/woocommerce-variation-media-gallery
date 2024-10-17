
# WooCommerce Variation Media Gallery

This documentation provides instructions for installing and using the **WooCommerce Variation Media Gallery** plugin. This plugin allows you to add and display additional media (images and videos) for WooCommerce product variations on the single product page.

---

## Table of Contents
1. [Plugin Description](#plugin-description)
2. [How to Install the Plugin](#how-to-install-the-plugin)
3. [How to Display Variation Media on the Single Product Page](#how-to-display-variation-media-on-the-single-product-page)

---

## Plugin Description

**Plugin Name**: WooCommerce Variation Media Gallery  
**Version**: 1.0.0  
**Author**: Mike Opatskyi  
**Author URI**: [https://www.creomatix.com](https://www.creomatix.com)  
**Description**: Adds a media gallery (images and videos) to WooCommerce product variations. Allows for media files (images and videos) to be assigned to WooCommerce product variations and displayed on the front end.

---

## How to Install the Plugin

### 1. Upload the Plugin

1. Create a folder called `woocommerce-variation-media-gallery` in your WordPress `wp-content/plugins` directory.
2. Add the plugin PHP file and additional assets (JavaScript and CSS files) into this folder.
3. Ensure that your folder structure looks like this:

```
woocommerce-variation-media-gallery
├── assets
│   ├── admin
│   │   ├── js
│   │   │   └── admin.js
│   │   └── css
│   │       └── admin.css
└── woocommerce-variation-media-gallery.php
```

4. Add the following code to `woocommerce-variation-media-gallery.php`:

```php
<?php
/*
Plugin Name: WooCommerce Variation Media Gallery
Description: Adds a media gallery (images and videos) to WooCommerce product variations.
Version: 1.0.0
Author: Mike Opatskyi
Author URI: https://www.creomatix.com
*/

if (!defined('ABSPATH')) exit;

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Enqueue admin scripts and styles
    add_action('admin_enqueue_scripts', 'wvmg_enqueue_admin_scripts');
    function wvmg_enqueue_admin_scripts() {
        wp_enqueue_media();
        wp_enqueue_script('wvmg-admin-script', plugin_dir_url(__FILE__) . 'assets/admin/js/admin.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('wvmg-admin-style', plugin_dir_url(__FILE__) . 'assets/admin/css/admin.css', array(), '1.0.0');
    }

    // Add media gallery field to variations
    add_action('woocommerce_product_after_variable_attributes', 'wvmg_add_media_gallery_field', 10, 3);
    function wvmg_add_media_gallery_field($loop, $variation_data, $variation) {
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
                            $mime_type = get_post_mime_type($media_id);
                            if (strpos($mime_type, 'video') !== false) {
                                $video_url = wp_get_attachment_url($media_id);
                                echo '<li data-attachment_id="' . esc_attr($media_id) . '">
                                        <video width="100" height="100" controls>
                                            <source src="' . esc_url($video_url) . '" type="' . esc_attr($mime_type) . '">
                                            Your browser does not support the video tag.
                                        </video>
                                        <a href="#" class="remove_media">&times;</a>
                                      </li>';
                            } else {
                                $thumb_url = wp_get_attachment_thumb_url($media_id);
                                echo '<li data-attachment_id="' . esc_attr($media_id) . '">
                                        <img src="' . esc_url($thumb_url) . '" alt="' . esc_attr(get_the_title($media_id)) . '" />
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
    function wvmg_save_media_gallery_field($variation_id, $i) {
        if (isset($_POST['variable_wvmg_media_gallery'][$variation_id])) {
            $media_ids = sanitize_text_field($_POST['variable_wvmg_media_gallery'][$variation_id]);
            update_post_meta($variation_id, '_wvmg_media_gallery', $media_ids);
        }
    }

} else {
    add_action('admin_notices', 'wvmg_woocommerce_inactive_notice');
    function wvmg_woocommerce_inactive_notice() {
        echo '<div class="error"><p>' . esc_html__('WooCommerce Variation Media Gallery requires WooCommerce to be installed and active.', 'woocommerce-variation-media-gallery') . '</p></div>';
    }
}
```

5. **Activate the Plugin**:
   - Go to **WordPress Admin > Plugins** and activate the **WooCommerce Variation Media Gallery** plugin.

---

## How to Display Variation Media on the Single Product Page

To display the media gallery for each selected variation on the single product page, you can modify `single-product.php` or another appropriate WooCommerce template file.

### 1. Open `single-product.php`
- Navigate to your theme's folder: `wp-content/themes/your-theme/woocommerce/single-product.php`.
- If this file doesn’t exist, copy it from `wp-content/plugins/woocommerce/templates/single-product.php`.

### 2. Add the Following Code to Display Variation Media:

```php
if (function_exists('is_product') && is_product()) {
    
    // Get the global product object
    global $product;

    // Check if the product is variable
    if ($product->is_type('variable')) {

        // Get available variations
        $available_variations = $product->get_available_variations();

        // Loop through the variations to get media for each variation
        foreach ($available_variations as $variation) {

            // Get variation ID
            $variation_id = $variation['variation_id'];

            // Get media gallery for the variation
            $media_ids = get_post_meta($variation_id, '_wvmg_media_gallery', true);

            // If media exists for this variation, display it
            if (!empty($media_ids)) {
                $media_ids = explode(',', $media_ids);

                echo '<div class="variation-media-gallery">';

                foreach ($media_ids as $media_id) {
                    $mime_type = get_post_mime_type($media_id);

                    // Check if the media is a video or an image
                    if (strpos($mime_type, 'video') !== false) {
                        // Output video element
                        $video_url = wp_get_attachment_url($media_id);
                        echo '<div class="variation-media-item">';
                        echo '<video width="320" height="240" controls>
                              <source src="' . esc_url($video_url) . '" type="' . esc_attr($mime_type) . '">
                              Your browser does not support the video tag.
                              </video>';
                        echo '</div>';
                    } else {
                        // Output image element
                        $thumb_url = wp_get_attachment_thumb_url($media_id);
                        echo '<div class="variation-media-item">';
                        echo '<img src="' . esc_url($thumb_url) . '" alt="' . esc_attr(get_the_title($media_id)) . '" />';
                        echo '</div>';
                    }
                }

                echo '</div>'; // Close the gallery div
            }
        }
    }
}
```

### 3. Save and Refresh the Product Page

Once you’ve added this code to the `single-product.php` template, refresh your WooCommerce product page to see the media gallery for each variation.

---

## Conclusion

With this plugin, you can now assign and display a media gallery for each variation of a WooCommerce product, including both images and videos.


# WooCommerce Variation Media Gallery

This documentation provides instructions for installing and using the **WooCommerce Variation Media Gallery** plugin. This plugin allows you to add and display additional media (images and videos) for WooCommerce product variations on the single product page.

---

## Table of Contents
1. [Plugin Description](#plugin-description)
2. [How to Display Variation Media on the Single Product Page](#how-to-display-variation-media-on-the-single-product-page)

---

## Plugin Description

**Plugin Name**: WooCommerce Variation Media Gallery  
**Version**: 1.0.0  
**Author**: Mike Opatskyi  
**Author URI**: [https://www.creomatix.com](https://www.creomatix.com)  
**Description**: Adds a media gallery (images and videos) to WooCommerce product variations. Allows for media files (images and videos) to be assigned to WooCommerce product variations and displayed on the front end.

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

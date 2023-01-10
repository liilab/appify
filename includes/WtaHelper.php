<?php

namespace WebToApp;

use WebToApp\Traits\Singleton;

/**
 * Class WtaHelper
 * @package Appify
 */

class WtaHelper
{

    use Singleton;

    /**
     * Current User ID
     * @return int
     */

    public static function get_current_user_id()
    {
        $current_user = wp_get_current_user();
        return $current_user->ID;
    }

    /**
     * Return Error response
     */

    public static function return_error_response($message = "Something Error!")
    {
        $response = array(
            "status" => "error",
            "message" => $message,
        );

        echo json_encode($response);
        wp_die();
    }

    /**
     * String Cleaner
     */

    public static function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

    /**
     * @param $product
     * 
     * @return array
     */

    public static function get_product_dimensions($product)
    {
        $dimensions = array(
            'length' => '',
            'width'  => '',
            'height' => '',
            'unit'   => get_option('woocommerce_dimension_unit'),
        );

        if ($product->has_dimensions()) {
            $dimensions['length'] = wc_format_localized_decimal($product->get_length());
            $dimensions['width']  = wc_format_localized_decimal($product->get_width());
            $dimensions['height'] = wc_format_localized_decimal($product->get_height());
        }

        return $dimensions;
    }

    /**
     * @param $product
     * 
     * @return array
     */

    public static function get_images_meta($product)
    {
        $images_meta = array();
        $attachment_ids = $product->get_gallery_image_ids();

        foreach ($attachment_ids as $attachment_id) {
            $attachment = get_post($attachment_id);
            $size = wc_get_image_size('shop_single');
            $size = array('width' => $size['width'], 'height' => $size['height']);
            $images_meta[] = array('caption' => $attachment->post_excerpt, 'size' => $size);
        }

        return $images_meta;
    }

    /**
     * @param $product
     * 
     * @return array
     */

    public static function get_thumbnail($product)
    {
        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'full');
        if ($thumbnail) {
            return $thumbnail[0];
        } else {
            return wc_placeholder_img_src();
        }
    }

    /**
     * @param $product
     * 
     * @return array
     */

    public static function get_thumbnail_meta($product)
    {
        $thumbnail_id = $product->get_image_id();
        $thumbnail_meta = array();
        if ($thumbnail_id) {
            $thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
        }
        return $thumbnail_meta;
    }

    /**
     * @param WC_Product|WC_Product_Variable $product
     *
     * @return float|int
     */

    public static function get_sale_percentage($product)
    {
        $maximumper = 0;
        if (!is_a($product, 'WC_Product_Variable')) {
            $sale_price = $product->get_sale_price() ? $product->get_sale_price() : (($product->get_price() < $product->get_regular_price()) ? $product->get_price() : '');
            if ($sale_price) {
                $maximumper = ($product->is_on_sale() && 0 != $product->get_regular_price() && ($product->get_regular_price() > $sale_price)) ? round((($product->get_regular_price() - $sale_price) / $product->get_regular_price()) * 100) : 0;
            }
        } else {
            $maximumper = apply_filters('wta_wc_sale_percentage', $maximumper, $product);
            if ($maximumper != 0 && !empty($maximumper)) {
                return $maximumper;
            }
            $regular_price = $product->get_variation_regular_price();
            $sales_price = $product->get_variation_price();
            $percentage        = (0 != $regular_price) ? round(((($regular_price - $sales_price) / $regular_price) * 100), 1) : 0;
            if ($percentage > $maximumper && ($percentage > 0 && $percentage < 100)) {
                $maximumper = $percentage;
            }
        }

        $percentage =  ($maximumper <= 0 || $maximumper >= 100) ? 0 : $maximumper;
        $percentage = (string)number_format((float)$percentage, 2, '.', '');

        return $percentage;
    }

    /**
     * @param WC_Product|WC_Product_Variable $price
     *
     * @return float|int
     */


    public static function get_display_price($price)
    {
        $price = wc_price($price);
        $price = strip_tags($price);
        $price = preg_replace('/&nbsp;/', ' ', $price);
        $price = html_entity_decode($price, ENT_QUOTES, 'UTF-8');

        return $price;
    }


    /**
     * @param WC_Product|WC_Product_Variable $price
     * 
     * @return string
     */

    public static function get_price_display($price)
    {
        if ($price) {
            return $price . html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
        } else {
            return '';
        }
    }

    /**
     * @param $url
     * 
     * @return $url
     */

    public static function ensure_absolute_link($url)
    {
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = get_site_url(null, $url);
        }
        if (substr($url, 0, 2) === '//') {
            $url = 'https:' . $url;
        }
        return $url;
    }


    /**
     * Get Price
     * 
     * @param WC_Product|WC_Product_Variable $product
     */

    public static function get_price($product, $price_type = 'normal')
    {
        if ($price_type == 'normal') {
            if ($product->get_type() == 'variable') {
                $price = $product->get_variation_price('min', true) . '-' . $product->get_variation_price('max', true);
                if ($price == '-') {
                    $price = "";
                }
            } else {
                $price = $product->get_price();
            }

            return $price;
        } else if ($price_type == 'regular') {
            if ($product->get_type() == 'variable') {
                $price = $product->get_variation_regular_price('max', true);
            } else {
                $price = $product->get_regular_price();
            }

            return $price;
        } else if ($price_type == 'sale') {
            if ($product->get_type() == 'variable') {
                $price = $product->get_variation_sale_price('min', true) . '-' . $product->get_variation_sale_price('max', true);
                if ($price == '-') {
                    $price = "";
                }
            } else {
                $price = $product->get_sale_price();
            }

            return $price;
        }
    }

    /**
     * Get ID
     * 
     * @param Object
     */

    public static function get_id($object)
    {
        if (method_exists($object, 'get_id')) {
            return $object->get_id();
        } elseif (is_object($object)) {
            return $object->id;
        }
    }


    /**
     * @return string
     */

    public static function getAppUrl()
    {

        $menu_slug = 'web-to-app';

        $url = admin_url('admin.php?page=' . $menu_slug);

        return $url;
    }
}

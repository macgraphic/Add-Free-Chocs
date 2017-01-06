<?php
/*
 * Plugin Name: Add Free Chocs
 * Plugin URI: .
 * Version: 0.2.0
 * Description: Add Free Chocolates to cart when an item from the flower-bunch category is ordered.
 * Author: Mark Smallman | GraphicAndWeb.com
 * Author URI: http://www.graphicandweb.com
 * License: GNU General Public License v3 (or newer)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function get_products_in_cart() {
	$cart_ids = array();
	foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
		$cart_product = $values['data'];
		$cart_ids[]   = $cart_product->id;
	}
	return $cart_ids;
}
function get_categories_in_cart( $cart_ids ) {
	$cart_categories = array();
	foreach( $cart_ids as $id ) {
		$products_categories = get_the_terms( $id, 'product_cat' );
		if ( ! empty( $products_categories ) ) {
			// Loop through each product category and add it to our $cart_categories array
			foreach ( $products_categories as $products_category ) {
				$cart_categories[] = $products_category->slug;
			}
		}
	}
	return $cart_categories;
}
function check_for_category_in_cart( $categories, $cart_categories ) {
	if ( ! empty( array_intersect( $categories, $cart_categories ) ) ) {
		$required_category_in_cart = true;
	} else {
		$required_category_in_cart = false;
	}
	return $required_category_in_cart;
}
function check_for_product_in_cart( $cart_ids, $product_id ) {
	$product_in_cart = false;
	foreach ( $cart_ids as $cart_id ) {
		if ( $cart_id == $product_id ){
			$product_in_cart = true;
		}
	}
	return $product_in_cart;
}
add_action( 'template_redirect', 'remove_product_from_cart' );
function remove_product_from_cart( $product_id ) {
    // Run only in the Cart or Checkout Page
    if( is_cart() || is_checkout() ) {
        // Cycle through each product in the cart
        foreach( WC()->cart->cart_contents as $product ) {
            // Get the Variation or Product ID
            $cart_id = ( isset( $product['variation_id'] ) && $product['variation_id'] != 0 ) ? $product['variation_id'] : $product['product_id'];
            // Check to see if IDs match
            if( $product_id == $cart_id ) {
                // Get it's unique ID within the Cart
                $product_unique_id = WC()->cart->generate_cart_id( $cart_id );
                // Remove it from the cart by un-setting it
                unset( WC()->cart->cart_contents[$product_unique_id] );
            }
        }
    }
}
add_action( 'wp_loaded', 'add_gift_product_to_cart', 10 );
function add_gift_product_to_cart() {
	// Add your special category slugs here
	$categories = array( 'floral-bunches' );
	// ID of free product to add
	$gift_product_id = 1100;
	global $woocommerce; if ( ! is_admin() && sizeof( WC()->cart->get_cart() ) > 0 ) {
		$cart_ids = get_products_in_cart();
		$cart_categories = get_categories_in_cart( $cart_ids );
		$gift_product_in_cart = check_for_product_in_cart( $cart_ids, $gift_product_id );
		$required_category_in_cart = check_for_category_in_cart( $categories, $cart_categories );
		// Add the gift product to cart
		if ( $required_category_in_cart && ! $gift_product_in_cart ) {
			WC()->cart->add_to_cart( $gift_product_id, 1 );
		}
	}
}
add_action( 'woocommerce_check_cart_items', 'remove_gift_product' );
function remove_gift_product() {
	// Add your special category slugs here
	$categories = array( 'floral-bunches' );
	// ID of free product to add
	$gift_product_id = 1100;
	$cart_ids = get_products_in_cart();
	$cart_categories = get_categories_in_cart( $cart_ids );
	$gift_product_in_cart = check_for_product_in_cart( $cart_ids, $gift_product_id );
	$required_category_in_cart = check_for_category_in_cart( $categories, $cart_categories );
	// Remove gift item from cart
	if ( ! $required_category_in_cart && $gift_product_in_cart ) {
		remove_product_from_cart( $gift_product_id);
		wc_add_notice( __( 'A gift item was removed from your cart because it is no longer available.', 'woocommerce' ), 'error' );
	}
}

?>

<?php 
/**
* @since 1.0.0
* @package wp-pagalo-woocommerce
* @author xicoofficial
* 
* Plugin Name: Pagalo - WooCommerce Payment Gateway
* Plugin URI: https://digitallabs.agency
* Description: Receive Visa and Mastercard payments on WooCommerce with this custom payment gateway integration with Pagalo.
* Version: 2.1.0
* Author: Digital Labs
* Author URI: https://digitallabs.agency
* Licence: GPL-3.0+
* Text Domain: wp-pagalo-woocommerce
* Domain Path: /languages/
* WC requires at least: 8.0.0 
* WC tested up to: 9.3
*/
 


function dl_p_cuotas_load_textdomain() {
    load_plugin_textdomain( 'wp-pagalo-woocommerce', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'dl_p_cuotas_load_textdomain' );



use pagalo_woocommerce_cuotas\Admin\Util;


// If this file is accessed directory, then abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// require_once('admin/wp-functions.php');

defined( 'ABSPATH' ) or exit;
// Make sure WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	require_once( trailingslashit( dirname( __FILE__ ) ) . 'inc/autoloader.php' );
	require_once( trailingslashit( dirname( __FILE__ ) ) . 'admin/wp-filters.php');
	require_once( trailingslashit( dirname( __FILE__ ) ) . 'admin/wp-actions.php');
	return;
}

else {
	add_action( 'admin_notices', 'dl_p_cuotas_add_error_notice', 10 );
}

/**
* Add custom action links
*/
function dl_p_cuotas_pagalo_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=dl_p_cuotas_pagalo' ) . '">' . __( 'Settings', 'wp-pagalo-woocommerce' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dl_p_cuotas_pagalo_action_links' );

function dl_p_cuotas_add_error_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Pagalo - WooCommerce Payment Gateway requires WooCommerce to work', 'wp-pagalo-woocommerce' ); ?></p>
    </div>
    <?php
}

function dl_p_cuotas_add_api_notice () {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo __('Please', 'wp-pagalo-woocommerce') . ' <a href="' . admin_url() . 'options-general.php?page=pagalo-cuotas-api">' . __('activate', 'wp-pagalo-woocommerce') . ' </a> ' . __('the Pagalo Plugin with Installments Licence to start using this plugin!', 'wp-pagalo-woocommerce'); ?></p>
    </div>
    <?php
}

function dl_pagalo_woocommerce_cuotas_declare_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'hpos', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}	
add_action( 'before_woocommerce_init', 'dl_pagalo_woocommerce_cuotas_declare_hpos_compatibility' );


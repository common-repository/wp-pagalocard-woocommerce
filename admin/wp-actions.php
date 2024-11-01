<?php

namespace pagalo_woocommerce_cuotas\Admin;

/**
 * Tell WordPress to load a translation file if it exists for the user's language
 */
function dl_p_cuotas_load_plugin_textdomain() {
    load_plugin_textdomain( 'wp-pagalo-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\dl_p_cuotas_load_plugin_textdomain' );


function dl_p_cuotas_pagalo_init() {
    //if condition use to do nothin while WooCommerce is not installed
	if ( ! class_exists( 'WC_Payment_Gateway_CC' ) ) return;
	
	include_once(  plugin_dir_path( __DIR__ ) .'admin/helpers/class-wc-gateway-pagalo-cuotas.php' );

	// class add it too WooCommerce
	function dl_p_cuotas_add_pagalocard_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Pagalo_Cuotas';
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', __NAMESPACE__ . '\\dl_p_cuotas_add_pagalocard_gateway' );

}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\dl_p_cuotas_pagalo_init', 0 );



/* Add custom Scripts */
function dl_p_cuotas_load_plugin_scripts() {
    wp_enqueue_script('wc_pg_cleave', plugin_dir_url( __DIR__ ) . 'dist/node_modules/cleave.js/dist/cleave.min.js');
	wp_enqueue_script('wc_pg_device', plugin_dir_url( __DIR__ ) . 'dist/assets/js/app.min.js');
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\dl_p_cuotas_load_plugin_scripts');

// enqueue admin scripts
function wpdocs_selectively_enqueue_admin_script( $hook ) {
    if ( 'woocommerce_page_wc-settings' != $hook ) {
        return;
    }
    wp_enqueue_script( 'wp-pagalo-cuotas-woocommerce-admin-js', plugin_dir_url( __DIR__ ) . 'dist/assets/js/admin.min.js', array(), filemtime(  plugin_dir_path( dirname( __FILE__ ) )  . '/dist/assets/js/admin.min.js' ) );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\wpdocs_selectively_enqueue_admin_script' );

if( ! function_exists('dl_promo_admin_notice') ) {
	function dl_promo_admin_notice(){
		echo '<div class="notice notice-info">
			<p>' . __('You are using the <strong>Pagalo - WooCommerce Payment Gateway</strong> plugin developed by <a href="https://digitallabs.agency" target="_blank">Digital Labs</a>. If you need assistance configuring the plugin, help with your eCommerce site or just want to say hi, feel free to contact us <a href="https://digitallabs.agency/contacto" target="_blank">here</a>. We will be happy to work with you.', 'wp-pagalo-woocommerce') . '</p>
			</div>';
	}
	add_action('admin_notices', __NAMESPACE__ . '\\dl_promo_admin_notice' );
}



function dl_declare_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'hpos', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}	
add_action( 'before_woocommerce_init', __NAMESPACE__ . '\\dl_declare_hpos_compatibility' );


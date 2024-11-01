<?php

namespace pagalo_woocommerce_cuotas\Admin;


/**
* Customize credict card form
*/
function dl_p_cuotas_pagalo_custom_credit_card_fields ($cc_fields , $payment_id){
	$new_fields = array(
	 'card-name-field' => '<p class="form-row form-row-wide"><label for="' . esc_attr( $payment_id ) . '-card-name">'
	 		. __( 'Cardholder Name', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
	 	</label>
	 	<input id="' . esc_attr( $payment_id ) . '-card-name" class="input-text wc-credit-card-form-card-name" type="text" maxlength="30" autocomplete="off" placeholder="' . __('CARDHOLDER NAME', 'wp-pagalo-woocommerce') . '" name="' . esc_attr( $payment_id ) . '-card-name' . '" />
	 </p>',
	 'card-number-field' => '<p class="form-row form-row-wide"><label for="' . esc_attr( $payment_id ) . '-card-number">'
	 		. __( 'Card Number', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span><img src="' . plugin_dir_url( __DIR__ ) .'assets/images/mastercard.png"><img src="' . plugin_dir_url( __DIR__ ) . 'assets/images/visa.png">
	 	</label>
	 	<input id="' . esc_attr( $payment_id ) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="•••• •••• •••• ••••" name="' . esc_attr( $payment_id ) . '-card-number' . '" />
	 </p>',
	 'card-expiry-field' => '<p class="form-row form-row-first"><label for="' . esc_attr( $payment_id ) . '-card-expiry">'
	 		. __( 'Expiry (MM/YYYY)', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
	 	</label>
	 	<input id="' . esc_attr( $payment_id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . __('MM / YYYY', 'wp-pagalo-woocommerce') . '" name="' . esc_attr( $payment_id ) . '-card-expiry' . '" />
	 </p>',
	 'card-cvc-field' => '<p class="form-row form-row-last"><label for="' . esc_attr( $payment_id ) . '-card-cvc">'
	 		. __( 'Card Code', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
	 	</label>
	 	<input id="' . esc_attr( $payment_id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc"inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="CVV" style="width:100px" name="' . esc_attr( $payment_id ) . '-card-cvc' . '" />
	 </p>'
	);

	return $new_fields;
}

add_filter( 'woocommerce_credit_card_form_fields' , __NAMESPACE__ . '\\dl_p_cuotas_pagalo_custom_credit_card_fields' , 10, 2 );





// Hook in
add_filter( 'woocommerce_checkout_fields' , __NAMESPACE__ . '\\dl_p_add_nit_to_checkout_fields' );

// Our hooked in function – $fields is passed via the filter!
function dl_p_add_nit_to_checkout_fields( $fields ) {

	$payment_gateway = WC()->payment_gateways->payment_gateways()['dl_p_cuotas_pagalo'];

	if ( property_exists( $payment_gateway, "pc_nit_enabled") && $payment_gateway->pc_nit_enabled == 'yes' ) {
		$fields['billing']['billing_nit'] = array(
			'label'     => __('NIT', 'woocommerce'),
			'placeholder'   => _x('C/F', 'placeholder', 'woocommerce'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true,
			'priority'	=> 25,
		);
	}

    return $fields;
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', __NAMESPACE__ . '\\dl_p_nit_checkout_field_display_admin_order_meta', 10, 1 );

function dl_p_nit_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('NIT').':</strong> ' . $order->get_meta( '_billing_nit', true ) . '</p>';
}
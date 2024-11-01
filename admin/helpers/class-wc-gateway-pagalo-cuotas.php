<?php

// namespace pagalo_woocommerce_cuotas\Admin\Helpers;
include_once( 'pagalo-sdk-php/class-pagalo-api.php' );

use pagalo_woocommerce_cuotas\Admin\Helpers;
use pagalo_woocommerce_cuotas\Admin\Util;


/**
* @package wp-pagalo-woocommerce
* @author XicoOfficial
* @since 1.1.0
 */

#[AllowDynamicProperties]
class WC_Gateway_Pagalo_Cuotas extends WC_Payment_Gateway {

	private $pc_method;
	function __construct() {
		// global ID
		$this->id = "dl_p_cuotas_pagalo";
		// Show Title
		$this->method_title = __( "Pagalo", 'wp-pagalo-woocommerce' );
		// Show Description
		$this->method_description = __( "Pagalo Payment Gateway Plug-in for WooCommerce. Accept Visa and MasterCard cards, this plugin works on Cybersource or Epay mode and also enables visa instalments", 'wp-pagalo-woocommerce' );
		// vertical tab title
		$this->title = __( "Pagalo", 'wp-pagalo-woocommerce' );
		$this->icon = null;
		$this->has_fields = true;
		// support default form with credit card
		// $this->supports = array( 'default_credit_card_form' );
		// setting defines
		$this->init_form_fields();
		// load time variable setting
		$this->init_settings();
		$this->logger = new Util\Logger();
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		// further check of SSL if you want
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );
		add_action( 'woocommerce_after_checkout_form', array( $this,	'dl_p_cuotas_load_devicefingerprint_script' ) );
		// Check if the keys have been configured
		if ( $this->pc_version == '1' && ( $this->pc_v1_idenEmpresa == '' || $this->pc_v1_key_secret == '' || $this->pc_v1_key_public == '' || $this->pc_merchantID == '' ) ) {	
			if( !is_admin() && $this->enabled == "yes") {
				wc_add_notice( __("V1 Some information is missing for the payment gateway configuration so the payment won't work. Please contact the store owner for more information or alternative ways to pay.", "wp-pagalo-woocommerce"), 'error');
			}
		}
		if ( $this->pc_version == '2' &&  $this->pc_v2_credencial == '' ) {	
			if( !is_admin() && $this->enabled == "yes") {
				wc_add_notice( __("V2 Some information is missing for the payment gateway configuration so the payment won't work. Please contact the store owner for more information or alternative ways to pay.", "wp-pagalo-woocommerce"), 'error');
			}
		}
		// Save settings
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

	} // Here is the  End __construct()


	// administration fields for specific Gateway
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'			=> __( 'Enable / Disable', 'wp-pagalo-woocommerce' ),
				'label'			=> __( 'Enable this payment gateway', 'wp-pagalo-woocommerce' ),
				'type'			=> 'checkbox',
				'default'		=> 'no',
			),
			'title' => array(
				'title'			=> __( 'Title', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'description'	=> __( 'Title of this payment option displayed to the clients on the checkout process.', 'wp-pagalo-woocommerce' ),
				'default'		=> __( 'Credit card (MasterCard or Visa)', 'wp-pagalo-woocommerce' ),
			),
			'description' => array(
				'title'			=> __( 'Description', 'wp-pagalo-woocommerce' ),
				'type'			=> 'textarea',
				'description'	=> __( 'Description of this payment option displayed to the clients on the checkout process.', 'wp-pagalo-woocommerce' ),
				'default'		=> __( 'Successfull payment through credit card.', 'wp-pagalo-woocommerce' ),
				'css'				=> 'max-width:400px;'
			),
			'pc_version' => array(
				'title'			=> __( 'Version', 'wp-pagalo-woocommerce' ),
				'type'			=> 'select',
				'default' => 'V2',
				'options' => array(
			    	'2' => 'V2',
			    	'1' => 'V1',
			    ),
				'description' => __( 'Most accounts should use EPAY mode by default. Contact Pagalo if your website requires CYBERSOURCE mode.', 'wp-pagalo-woocommerce' ),
				'default'		=> 'no',
			),
			'pc_mode' => array(
				'title'			=> __( 'Mode', 'wp-pagalo-woocommerce' ),
				'type'			=> 'select',
				'default' => 'CyberSource',
				'options' => array(
			    	'cybersource' => 'CyberSource',
			    	'EPAY' => 'EPAY',
			    ),
				'description' => __( 'Most accounts should use CyberSource mode by default. Contact Pagalo if your website requires EPAY mode.', 'wp-pagalo-woocommerce' ),
				'default'		=> 'no',
			),
			'pc_v1_idenEmpresa' => array(
				'title'			=> __( 'IdenEmpresa', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'desc_tip'	=> __( 'This is the IdemEmpresa provided by PagaloCard when you signed up for an account.', 'wp-pagalo-woocommerce' ),
			),
			'pc_v1_token' => array(
				'title'			=> __( 'Token', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'desc_tip'	=> __( 'This is the Token provided by PagaloCard when you signed up for an account.', 'wp-pagalo-woocommerce' ),
			),
			'pc_v1_key_public' => array(
				'title'			=> __( 'API Public Key', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'desc_tip'	=> __( 'This is the Public Key provided by PagaloCard when you signed up for an account.', 'wp-pagalo-woocommerce' ),
			),
			'pc_v1_key_secret' => array(
				'title'			=> __( 'API Secret Key', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'desc_tip'	=> __( 'This is the API Secret Key provided by PagaloCard when you signed up for an account.', 'wp-pagalo-woocommerce' ),
			),
			'pc_v2_credencial' => array(
				'title'			=> __( 'API Credential', 'wp-pagalo-woocommerce' ),
				'type'			=> 'password',
				'desc_tip'	=> __( 'This is the API Credential provided by Pagalo when you signed up for an account.', 'wp-pagalo-woocommerce' ),
			),
			'pc_merchantID' => array(
				'title'			=> __( 'merchantID', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'description'	=> __( 'Leave <b>visanetgt_jupiter</b> when using Pagalo Card&apos;s merchant ID, only change this setting if you have acquired your own key. Please contact Pagalo&apos;s team for more information on this field.', 'wp-pagalo-woocommerce' ),
				'desc_tip'	=> __( 'This is the merchant ID provided by PagaloCard when you signed up for an account.', 'wp-pagalo-woocommerce' ),
				'default'		=> 'visanetgt_jupiter',
				'class'     => 'show_merchant_id'
			),
			'pc_nit_enabled' => array(
				'title'			=> __( 'Enable / Disable NIT field on checkout', 'wp-pagalo-woocommerce' ),
				'label'			=> __( 'Enable NIT field on checkout form', 'wp-pagalo-woocommerce' ),
				'type'			=> 'checkbox',
				'default'		=> 'no',
			),
			'pc_send_single_product' => array(
				'title'			=> __( 'Don\'t send order details to Pagalo', 'wp-pagalo-woocommerce' ),
				'type'			=> 'checkbox',
				'description'	=> __( 'If this setting is enabled, the plugin will send a single product named "Order #{order_id} at {http://yourwebsite.com}"', 'wp-pagalo-woocommerce' ),
				'desc_tip'	=> __( 'Enable this option if you don\'t want to send order details to Pagalo or if you are using an advanced discount plugin that doesn\'t apply discounts at the Product level', 'wp-pagalo-woocommerce' ),
				'default'		=> 'no',
				'class'     => 'send_single_product'
			),
			'pc_installments_enabled' => array(
				'title'			=> __( 'Enable / Disable Installments', 'wp-pagalo-woocommerce' ),
				'label'			=> __( 'Enable installments on this payment gateway', 'wp-pagalo-woocommerce' ),
				'type'			=> 'checkbox',
				'default'		=> 'no',
			),
			'pc_installments' => array(
				'title'			=> __( 'Installments', 'wp-pagalo-woocommerce' ),
				'type'			=> 'multiselect',
				'description'	=> __( 'Select the installment options you would like users to have when paying.', 'wp-pagalo-woocommerce' ),
				'desc_tip'	=> __( '', 'wp-pagalo-woocommerce' ),
				'options' => array(
			          '3' => '3 ' . __('installments', 'wp-pagalo-woocommerce' ),
			          '6' => '6 ' . __('installments', 'wp-pagalo-woocommerce' ),
			          '10' => '10 ' . __('installments', 'wp-pagalo-woocommerce' ),
			          '12' => '12 ' . __('installments', 'wp-pagalo-woocommerce' ),
			     ),
				'class'     => 'show_installments'
			),
			'pc_installments_min' => array(
				'title'			=> __( 'Installments Minimum Amount', 'wp-pagalo-woocommerce' ),
				'type'			=> 'text',
				'description'	=> __( 'Type the minimum amount required to enable installment options. The field has to be a number grater than 200. If the input entered is not a text or it is a number smaller than 200, then it will be ignored and the plugin will use 200 as the minimum amount.', 'wp-pagalo-woocommerce' ),
				'desc_tip'	=> __( '', 'wp-pagalo-woocommerce' ),
				'default'		=> 200,
				
				'class'     => 'show_installments_min'
			),

		);		
	}





	public function payment_fields() {
	 
		// ok, let's display some description before the payment form
		if ( $this->description ) {
			// you can instructions for test mode, I mean test card numbers etc.
			// display the description with <p> tags etc.
			echo wpautop( wp_kses_post( $this->description ) );
		}

		$currency = get_woocommerce_currency();
		$value = max( 0, apply_filters( 'woocommerce_calculated_total', round( WC()->cart->cart_contents_total + WC()->cart->fee_total + WC()->cart->tax_total, WC()->cart->dp ), WC()->cart ) );

		
		$show_installments = false;

		$pc_installments_min = 200;

		if (is_numeric( $this->pc_installments_min ) && $this->pc_installments_min > 200 ) {
			$pc_installments_min = $this->pc_installments_min;
		}  

		if ($currency == 'GTQ' && $value > $pc_installments_min && $this->pc_installments_enabled == 'yes') {
			$show_installments = true;
		}




	 
		// I will echo() the form, but you can close PHP tags and print it directly in HTML
		echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
	 
		// Add this action hook if you want your custom payment gateway to support it
		do_action( 'woocommerce_credit_card_form_start', $this->id );
	 
		// I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc

		if( $show_installments ) {
			echo '<p class="form-row form-row-first"><label for="' . esc_attr( $this->id ) . '-installments">'
			 		. __( 'Method of payment', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
			 	</label>
			 	<select id="' . esc_attr( $this->id ) . '-installments" class="input-text wc-credit-card-form-installments" name="' . esc_attr( $this->id ) . '-installments' . '" >';

			 	echo '<option value="1" selected>' . __('Up-front', 'wp-pagalo-woocommerce') . '</option>';
				foreach ($this->pc_installments as $installment) {
					echo '<option value="' . $installment .  '">' . $installment  . ' ' .  __('installments', 'wp-pagalo-woocommerce') . '</option>';
				}

				echo '</select>
			 </p>';

		}

		echo '<p class="form-row form-row-wide"><label for="' . esc_attr( $this->id ) . '-card-name">'
			 		. __( 'Cardholder Name', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
			 	</label>
			 	<input id="' . esc_attr( $this->id ) . '-card-name" class="input-text wc-credit-card-form-card-name" type="text" maxlength="30" autocomplete="off" placeholder="' . __('CARDHOLDER NAME', 'wp-pagalo-woocommerce') . '" name="' . esc_attr( $this->id ) . '-card-name' . '" />
			 </p>
			<p class="form-row form-row-wide"><label for="' . esc_attr( $this->id ) . '-card-number">'
			 		. __( 'Card Number', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span><img src="' . plugin_dir_url( __DIR__ ) .'images/mastercard.png"><img src="' . plugin_dir_url( __DIR__ ) . 'images/visa.png">
			 	</label>
			 	<input style="background-image: unset;" id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="•••• •••• •••• ••••" name="' . esc_attr( $this->id ) . '-card-number' . '" />
			 </p>
			 <p class="form-row form-row-first"><label for="' . esc_attr( $this->id ) . '-card-expiry">'
			 		. __( 'Expiry (MM/YY)', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
			 	</label>
			 	<input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . __('MM / YY', 'wp-pagalo-woocommerce') . '" name="' . esc_attr( $this->id ) . '-card-expiry' . '" />
			 </p>

			<p class="form-row form-row-last"><label for="' . esc_attr( $this->id ) . '-card-cvc">'
			 		. __( 'Card Code', 'wp-pagalo-woocommerce' ) . ' <span class="required">*</span>
			 	</label>
			 	<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc"inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="4" placeholder="CVV" style="width:100px" name="' . esc_attr( $this->id ) . '-card-cvc' . '" />
			 </p>';
	 
		do_action( 'woocommerce_credit_card_form_end', $this->id );
	 
		echo '<div class="clear"></div></fieldset>';
		echo '
		<script>
		var cleave = new Cleave("#dl_p_cuotas_pagalo-card-number", {
		    creditCard: true,
		    onCreditCardTypeChanged: function (type) {
		        // update UI ...
		    }
		});	
		var cleave2 = new Cleave("#dl_p_cuotas_pagalo-card-expiry", {
		    date: true,
		    datePattern: ["m", "y"]
		});
		</script>	';
	 
	}



	
	// Response handled for payment gateway
	public function process_payment( $order_id ) {
		global $woocommerce;
		$customer_order = new \WC_Order( $order_id );
		$pg_version = $this->pc_version;
		$pg_env = $this->pc_mode;
		$pg_method = $this->pc_method;
		$pagalo_api = new pagalo_sdk\Pagalo_API();
		$woocommerce_pagalo = new Helpers\WooCommerce_Pagalo();

		if ( isset( $_POST['dl_p_cuotas_pagalo-installments'] ) && $_POST['dl_p_cuotas_pagalo-installments'] > 1 ) {
			$pagalo_api->set_mode('EPAY');
		} else {
			$pagalo_api->set_mode($this->pc_mode);
		}

		// $pagalo_api->set_debug_mode( true );

		if( $pg_version == '1' ) {
			$pagalo_api->set_empresa($this->pc_key_secret, $this->pc_key_public, $this->pc_idenEmpresa );
			$pagalo_api->set_token( $this->pc_token );
		}
		if( $pg_version == '2' ) {
			$pagalo_api->set_api_credencial($this->pc_v2_credencial);
			$pagalo_api->set_current_total($woocommerce_pagalo->get_current_total( $customer_order ));
			$pagalo_api->set_currency( $woocommerce_pagalo->get_currency( $customer_order ) );

			if( $pg_method == "cybersource" ) {
				$pagalo_api->set_deviceFinger( $woocommerce_pagalo->get_deviceFinger( ) );
			}
		}
		$pagalo_api->set_client_data( $woocommerce_pagalo->get_client_data_on_checkout( $customer_order ) );
		$pagalo_api->set_detalle_data( $woocommerce_pagalo->get_detalle_data( $customer_order ) );
		$pagalo_api->set_credit_card_data( $woocommerce_pagalo->get_credit_card() );

	
		$response = $pagalo_api->make_payment();

		// 100 o 200 means the transaction was a success
		if ( $response['result']) {
			// Payment successful
			$customer_order->add_order_note( __( 'PagaloCard complete payment.', 'wp-pagalo-woocommerce' ) );							 
			// paid order marked
			$customer_order->payment_complete();
			// save transaction ID with order
			$customer_order->set_transaction_id( $response['transaction_id'] );
			// save customer order
			$customer_order->save();

			// this is important part for empty cart
			$woocommerce->cart->empty_cart();

			// Redirect to thank you page
			return array( 'result'   => 'success', 'redirect' => $this->get_return_url( $customer_order ) );
		} else {
			//transiction fail
			$customer_order->add_order_note( $response['message']);

			if( current_user_can('edit_plugins') ) {
				wc_add_notice($response['message'], 'error');	
			} else {
				wc_add_notice($response['message'], 'error');				
			}

		}

	}
	
	// Validate fields
	public function validate_fields() {
		return true;
	}

	public function do_ssl_check() {
		if( $this->enabled == "yes") {
			if( ($this->pc_v1_idenEmpresa == '' || $this->pc_v1_key_secret == '' || $this->pc_v1_key_public == '') && $this->pc_v2_credencial == '' ) {
				echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled but the pulgin hasn't been configured yet. Please ensure that you have a valid Secret Key, Public Key and Company ID. You can configure them <a href=\"%s\">here</a>", 'wp-pagalo-woocommerce' ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout&section=dl_p_cuotas_pagalo' ) ) ."</p></div>";	
			}
    }     
  }

  /* Add devicefingerprint script */
  public function dl_p_cuotas_load_devicefingerprint_script() {
  		$cybs_environment = ( "yes" == "yes" ) ? 'test' : 'live';
	    echo '<script>jQuery("#order_review").html(jQuery("#order_review").html() + ';
	    echo "'";
	    echo '<input type="hidden" name="deviceFingerprintID" id="deviceFingerprintID" value="';
	    echo "'";
	    echo '+ cybs_dfprofiler("' . $cybs_environment . '","' . $this->pc_merchantID . '") + ';
	    echo "'";
	    echo '"';
	    echo ">');</script>";
  }







}
<?php

namespace pagalo_woocommerce_cuotas\Admin\Helpers;

/**
* @package wp-pagalocard-woocommerce
* @author XicoOfficial
* @since 1.2.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\pagalo_woocommerce_cuotas\Admin\Helpers\WooCommerce_Pagalo' ) ) {
	
/**
 * WC_Cardpay_Authnet_API
 */
 class WooCommerce_Pagalo {


	public function get_credit_card() {

		$pg_payment_method_id = \WC()->session->get( 'chosen_payment_method' );
		$pg_payment_method = \WC()->payment_gateways()->payment_gateways()[ $pg_payment_method_id ];
		$pg_version = $pg_payment_method->get_option( "pc_version" );
		$pg_method = $pg_payment_method->get_option( "pc_method" );

		$nameCard = "";
		$accountNumber = "";
		$date_array = array();
		$date_array_year = "";
		$CVVCard = "";

		if( isset( $_POST['dl_p_cuotas_pagalo-card-name'] ) )	{
			$nameCard = sanitize_text_field( $_POST['dl_p_cuotas_pagalo-card-name'] );

		}
		if( isset( $_POST['dl_p_cuotas_pagalo-card-number'] ) )	{
			$accountNumber = str_replace( array(' ', '-' ), '', sanitize_text_field( $_POST['dl_p_cuotas_pagalo-card-number'] ) );
		}

		if ( array_key_exists('dl_p_cuotas_pagalo-card-expiry', $_POST) ) {
			$date_array = sanitize_text_field( $_POST['dl_p_cuotas_pagalo-card-expiry'] );
			$date_array = explode("/", str_replace(' ', '', $date_array));
			$date_array_year = $date_array[1];
			if(strlen($date_array_year) == 2 ) {
			$date_array_year = '20' . $date_array_year;
			}
		}

		if ( isset( $_POST['dl_p_cuotas_pagalo-card-cvc'] ) ) {
			$CVVCard = sanitize_text_field( $_POST['dl_p_cuotas_pagalo-card-cvc'] );
		}


		if( $pg_version == "1" ) {
			$credit_card_data = array(
				'nameCard'			=> $nameCard,
				'accountNumber'		=> $accountNumber,
				'expirationMonth'	=> $date_array[0],
				'expirationYear'	=> $date_array_year, 
				'CVVCard'			=> $CVVCard
			);
		}

		if ( $pg_version == "2") {
			$credit_card_data = array(
				'name_card'			=> $nameCard,
				'number_card'		=> $accountNumber,
				'expiration_month'	=> $date_array[0],
				'expiration_year'	=> $date_array_year, 
				'cvv_card'			=> $CVVCard,
				'quota_active' 		=> false,
				'quota' 			=> "1"
			);
		}


		if( $pg_version == "1" ) {
			if ( isset( $_POST['dl_p_cuotas_pagalo-installments'] ) ) {
				$nCuotas = sanitize_text_field( $_POST['dl_p_cuotas_pagalo-installments'] );
				if( $nCuotas != '1') {
					$credit_card_data['nCuotas'] = $nCuotas;
				}
			}
		}
		if ( $pg_version == "2") {
			if ( isset( $_POST['dl_p_cuotas_pagalo-installments'] ) ) {
				$nCuotas = sanitize_text_field( $_POST['dl_p_cuotas_pagalo-installments'] );
				if( $nCuotas != '1') {
					$credit_card_data['quota_active'] = true;
					$credit_card_data['quota'] = intval($nCuotas);
				}
			}
		}

		return $credit_card_data;

	}

    /**
	 * get_client_data_on_checkout function
	 * 
	 * @return string
	 */
	public function get_client_data_on_checkout( $customer_order ) {
		$pg_payment_method_id = \WC()->session->get( 'chosen_payment_method' );
		$pg_payment_method = \WC()->payment_gateways()->payment_gateways()[ $pg_payment_method_id ];
		$pg_version = $pg_payment_method->get_option( "pc_version" );
		$pg_method = $pg_payment_method->get_option( "pc_method" );

		$codigo = $customer_order->get_user_id();
        $customer_nit = $customer_order->get_meta('_billing_nit', true);

        if ($customer_nit == '') {
            $customer_nit = 'C/F';
        }

		if ($codigo == 0) {
			$codigo = $customer_order->get_id();
		}

		$deviceFingerprintID = "";
		if(isset($_POST['deviceFingerprintID'])) {
			$deviceFingerprintID = sanitize_text_field( $_POST['deviceFingerprintID'] );	
		}

		$street1 = $customer_order->get_billing_address_1();
		$country = $customer_order->get_billing_country();
		$city = $customer_order->get_billing_city();
		$state = $customer_order->get_billing_state();
		$postalCode = $customer_order->get_billing_postcode();

		if ($street1 == '') {
			$street1 = $customer_order->get_shipping_address_1();
		}
		if ($country == '') {
			$country = $customer_order->get_shipping_country();
		}
		if ($city == '') {
			$city = $customer_order->get_shipping_city();
		}
		if ($state == '') {
			$state = $customer_order->get_shipping_state();
		}
		if ($postalCode == '') {
			$postalCode = $customer_order->get_shipping_postcode();
		}
		if($postalCode == '' && $country == 'GT') {
			$postalCode = '01010'; //hardcode postal code, only for Guatemala
		}
		if( $pg_version == '1' ) {
			$client_data = array(
				'codigo'		=> $codigo,
				'nit'           => $customer_nit,
				'firstName' 	=> $customer_order->get_billing_first_name(),
				'lastName'  	=> $customer_order->get_billing_last_name(),
				'street1'		=> $street1,
				'phone'			=> $customer_order->get_billing_phone(),
				'country'		=> $country,
				'city'			=> $city,
				'state'			=> $state,
				'postalCode'	=> $postalCode,
				'email'			=> $customer_order->get_billing_email(),
				'ipAddress'		=> $customer_order->get_customer_ip_address(),
				'Total'			=> $customer_order->get_total(),
				'fecha_transaccion'=> $customer_order->get_date_created(),
				'currency'		=> $customer_order->get_currency(),
				  'deviceFingerprintID' => $deviceFingerprintID,
			);

			if( $pg_method == "cybersource" ) {
				$client_data["deviceFingerprintID"] = $deviceFingerprintID;
			}
		}
		if( $pg_version == "2" ) {
			$client_data = array(
				'first_name' 	=> $customer_order->get_billing_first_name(),
				'last_name'  	=> $customer_order->get_billing_last_name(),
				'phone'			=> $customer_order->get_billing_phone(),
				'email'			=> $customer_order->get_billing_email(),
				'country'		=> $country,
				'city'			=> $city,
				'state'			=> $state,
				'postal_code'	=> $postalCode,
				'location'		=> $street1
			);
		}
		return $client_data;
	}



	/**
	 * get_detalle_data function
	 * 
	 * @return string
	 */
	public function get_detalle_data( $customer_order ) {
		$pg_payment_method_id = \WC()->session->get( 'chosen_payment_method' );
		$pg_payment_method = \WC()->payment_gateways()->payment_gateways()[ $pg_payment_method_id ];
		$pg_version = $pg_payment_method->get_option( "pc_version" );
		$pg_method = $pg_payment_method->get_option( "pc_method" );
		$pg_send_single_product = $pg_payment_method->get_option( "pc_send_single_product" );
		$detalle = array();

		if( $pg_send_single_product == "yes" ) {
			if( $pg_version == "1" ) {
				$detalle[] = array(
					'id_producto'	=> 'web-001',
					'cantidad'		=> 1,
					'tipo'			=> 'product',
					'nombre'		=> 'Order #' . $customer_order->get_id() . ' - ' . get_site_url(),
					'precio'		=> $customer_order->get_total(),
					'Subtotal'		=> $customer_order->get_total(),
				);
			}
			if( $pg_version == "2" ) {
				$detalle[] = array(
					'uuid_product'	=> 'web-001',
					'name'			=> 'Order #' . $customer_order->get_id() . ' - ' . get_site_url(),
					'amount'		=> 1,
					'quantity'      => $customer_order->get_total(),
					'Subtotal'		=> $customer_order->get_total(),
				);
			}
			return $detalle;
		}


		$products = $customer_order->get_items();

		foreach ( $products as $product ) {

			$product_price = '';

			$_product = wc_get_product($product->get_product_id());
			$product_price = ($_product->is_on_sale()) ? $_product->get_sale_price() : $_product->get_regular_price();

			if( $pg_version == "1" ) {
				$detalle[] = array(
					'id_producto'	=> $product->get_product_id(),
					'cantidad'		=> $product->get_quantity(),
					'tipo'			=> $product->get_type(),
					'nombre'		=> $product->get_name(),
					'precio'		=> $product->get_total()/$product->get_quantity(),
					'Subtotal'		=> $product->get_total(),
				);
			}
			if( $pg_version == "2" ) {
				$detalle[] = array(
					'uuid_product'	=> $product->get_product_id(),
					'name'			=> $product->get_name(),
					'amount'		=> $product->get_total()/$product->get_quantity(),
					'quantity'      => intval($product->get_quantity()),
					'Subtotal'		=> $product->get_total(),
				);
			}
		}
		
		// Iterating through order fee items ONLY
		foreach( $customer_order->get_items('fee') as $item_id => $item_fee ) {

			$item_fee_name = $item_fee->get_name();
			if( $pg_version == "1" ) {
				$detalle[] = array(
					'id_producto'	=> strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $item_fee_name))),
					'cantidad'		=> '1',
					'tipo'			=> __('Fee', 'wp-pagalo-woocommerce'),
					'nombre'		=> $item_fee_name,
					'precio'		=> $item_fee->get_total(),
					'Subtotal'		=> $item_fee->get_total(),
				);
			}
			if( $pg_version == "2" ) {
				$detalle[] = array(
					'uuid_product'	=> $product->get_product_id(),
					'name'			=> $product->get_name(),
					'amount'		=> $product->get_total()/$product->get_quantity(),
					'quantity'		=> $product->get_quantity(),
					'Subtotal'		=> $product->get_total(),
				);
			}
		}

		if ( $customer_order->get_total_shipping() > 0 ) {
			
			if( $pg_version == "1" ) {
				$detalle[] = array(
					'id_producto'	=> 'shipping01',
					'cantidad'		=> '1',
					'tipo'				=> __( 'Shipping', 'wp-pagalo-woocommerce' ),
					'nombre'			=> __( 'Shipping', 'wp-pagalo-woocommerce' ),
					'precio'			=> $customer_order->get_total_shipping(),
					'Subtotal'		=> $customer_order->get_total_shipping(),
				);
			}
			if( $pg_version == "2" ) {
				$detalle[] = array(
					'uuid_product'	=> 'shipping01',
					'name'			=> __( 'Shipping', 'wp-pagalo-woocommerce' ),
					'amount'		=> $customer_order->get_total_shipping(),
					'quantity'		=> '1',
					'Subtotal'		=> $customer_order->get_total_shipping(),
				);
			}
		}

		return $detalle;
	}
	public function get_current_total( $customer_order ) {
		return $customer_order->get_total();
	}	

	public function get_currency( $customer_order ){
		return $customer_order->get_currency();
	}
}

}



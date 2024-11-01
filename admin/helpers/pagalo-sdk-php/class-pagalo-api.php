<?php

namespace pagalo_sdk;

/**
* @package wp-pagalocard-woocommerce
* @author XicoOfficial
* @since 1.2.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\pagalo_sdk\Pagalo_API' ) ) {
	
/**
 * WC_Cardpay_Authnet_API
 */
 class Pagalo_API {

	public $wc_pre_30;

	public function __construct( $version = "2", $method = "epay", $env = "prod" ) {


		$this->method = $method;
        $this->version = $version;
        $this->env = $env;
        $this->api_url = 'https://api.pagalo.co';

        if( $this->version == "1" ) {
            $this->api_url = 'https://app.pagalocard.com';
        } else {
            if ( $this->env == "test" ) {
                $this->api_url = 'https://apitest.pagalo.co';
            }
        }

		$this->wc_pre_30 = version_compare( WC_VERSION, '3.0.0', '<' );

    	$this->idenEmpresa = '';
    	$this->key_public = '';
    	$this->key_secret = '';
    	$this->token = '';
    	$this->merchantID = '';

		$this->api_credencial = '';

        $this->client_data = '';
		$this->credit_card_data  = '';
		$this->deviceFinger = '';
		$this->detalle_data = '';
		$this->debug_mode = false;
        if( defined( 'DL_DEBUG' ) && DL_DEBUG == true ) {
            $this->debug_mode = true;
        }

        $this->transaction_id = "";
        $this->current_total = 0;
        $this->currency = 'GTQ';
        $this->type_detailed = "producto";
	}



    public function send_http_request($data, $endpoint='integracion', $method = 'POST') {

    	if( $this->version == '1' ) {
            $request_url = $this->api_url . $endpoint . '/' . $this->token;
            $remote_post_args = array( 
                'method'    => 'POST', 
                'body'      =>  json_encode($data), 
                'timeout'   => 25, 
                'sslverify' => true, 
                'headers' => array( 'Content-Type' => 'application/json' ) 
            );
        }

        if( $this->version == '2' ) {
            $request_url = $this->api_url . $endpoint;
            $remote_post_args = array( 
                'method'    => 'POST', 
                'body'      =>  json_encode($data), 
                'timeout'   => 25,
                'sslverify' => true, 
                'headers' => array( 
                    'Content-Type'  => 'application/json',
                    'authorization' => $this->api_credencial
                    )
            );
        }

		$data_log = $data;
		if ( isset( $data_log['tarjetaPagalo'] )) {
			$data_log['tarjetaPagalo'] = "*** Hidden for security **";
		}
		if ( isset( $data_log['card_payment'] )) {
			$data_log['card_payment'] = "*** Hidden for security **";
		}
		$this->log("Data sent:" . print_r($data, true));
		$this->log("URL:" . $request_url );

        $result = wp_remote_post( $request_url, $remote_post_args ); 

        if ( is_wp_error( $result ) ) {
             $error_message = $result->get_error_message();
              echo "Something went wrong: $error_message";
        }

        $response_body = wp_remote_retrieve_body($result);

		if ( $this->debug_mode ) {
	    	$this->log('Response: ' . print_r($response_body, true));
		}
        return json_decode($response_body, true);

    }


	/**
	 * set_empresa_data function
	 * 
	 * @return string
	 */
	public function set_empresa( $key_secret, $key_public, $idenEmpresa ) {
		$this->key_secret = $key_secret;
		$this->key_public = $key_public;
		$this->idenEmpresa = $idenEmpresa;
	}

	/**
	 * get_empresa_data function
	 * 
	 * @return string
	 */
	public function get_empresa_data() {
		$empresa_data = array(
			'key_secret'=> $this->key_secret,
			'key_public'=> $this->key_public,
			'idenEmpresa'=> $this->idenEmpresa,
		);

		$empresa_data = json_encode( $empresa_data );
		return $empresa_data;
	}

	/**
	 * set_empresa_data function
	 * 
	 * @return string
	 */
	public function set_token( $token ) {
		$this->token = $token;
	}

	/**
	 * set_api_credencial function
	 * 
	 * @return string
	 */
	public function set_api_credencial( $api_credencial ) {
		$this->api_credencial = $api_credencial;
	}

	/**
	 * set_empresa_data function
	 * 
	 * @return string
	 */
	public function set_mode( $mode ) {
		$this->method = $mode;
	}


	/**
	 * set_client_data function
	 * 
	 * @return string
	 */
	public function set_client_data( $client ) {
		$this->client_data = $client;
	}

	/**
	 * get_client_data function
	 * 
	 * @return string
	 */
	public function get_client_data( ) {
		$client_data = json_encode( $this->client_data );
		return $client_data;
	}


	/**
	 * get_detalle_data function
	 * 
	 * @return string
	 */
	public function set_detalle_data( $detalle ) {
		$this->detalle_data = $detalle;
	}

	/**
	 * get_detalle_data function
	 * 
	 * @return string
	 */
	public function get_detalle_data( ) {
		$detalle_data = json_encode( $this->detalle_data );
		return $detalle_data;
	}

	public function set_credit_card_data( $credit_card_data ) {
		$this->credit_card_data = $credit_card_data;
	}


	/**
	 * get_credit_card_data function
	 * 
	 * @return string
	 */
	public function get_credit_card_data( ) {

		$credit_card_data = json_encode( $this->credit_card_data );

		return $credit_card_data;
	}

	/**
	 * set_current_total function
	 * 
	 * @return string
	 */
	public function set_current_total( $current_total ) {
		$this->current_total = $current_total;
	}

	/**
	 * set_currency function
	 * 
	 * @return string
	 */
	public function set_currency( $currency  ) {
        $this->currency = $currency;
	}

	public function set_deviceFinger( $deviceFinger ) {
		$this->deviceFinger = $deviceFinger;
	}

	public function get_deviceFinger() {
		return $this->deviceFinger;
	}

	public function set_debug_mode( $debug_mode ) {
		if ( is_bool( $debug_mode ) ) {
			$this->debug_mode = $debug_mode; 
			return true;
		}

		return false;
	}

    public function set_transaction_id( $transaction_id ) {
        $this->transaction_id = $transaction_id;
    }

    public function get_transaction_id() {
        return $this->transaction_id;
    }

	public function get_debug_mode( ) {
		return $this->debug_mode;
	}


	public function make_payment() {
		$this->log("make_payment init");
        if( $this->version == '1' ) {
            $endpoint = '/api/v1/' . ($this->method == 'epay' ? 'integracionpg' : 'integracion');
            $data = array(
                'empresa' => $this->get_empresa_data(),
                'cliente' => $this->get_client_data(),
                'detalle' => $this->get_detalle_data(),
                'tarjetaPagalo' => $this->get_credit_card_data()
            );
        }
		
        if( $this->version == '2' ) {
            $endpoint = '/v1/payments/transactions';
            $data = array (
                'method_payment'    => $this->method,
                'type_detail'       => $this->type_detailed,
                'total_amount'      => $this->current_total,
                'currency'          => $this->currency,
                'client'            => $this->client_data,
                'detail'            => $this->detalle_data,
                'card_payment'      => $this->credit_card_data
            );

			if( $this->method == "cybersource" ) {
				$data['deviceFinger'] = $this->deviceFinger;
			}
        }

        $response_body = $this->send_http_request($data, $endpoint);

        $payment = $this->process_response($response_body);

        $this->log( "make_payment end" );
        return $payment;
	}

	public function reverse_transaction() {
        $this->log("reverse_transaction init");

        if( $this->version == '1' ) {
            $data = array(
                'empresa' => $this->get_empresa_data(),
                "requestId" => json_encode( $this->transaction_id ),
            );
            $endpoint = '/integration/transactions/reverseTransaction';
        }

        if( $this->version == '2') {
            $data = array(
                'transactions_uuid' => $this->transaction_id
            );
            $endpoint = '/v1/payment/transaction/reverse';
        }


        $response_body = $this->send_http_request($data, $endpoint);

        $this->log( "reverse_transaction: " );

        $payment = $this->process_response($response_body);

        $this->log( "reverse_transaction end" );
        return $payment;
        
    }


	public function process_response($response_body) {
        $this->log( "process_response init" );
        $payment_result = false;
        $notification_client = "";
        $idTransaction = "";
        $requestToken = '';
        $reasonCode = '';
        if (is_array($response_body)) {
            $idTransaction = $this->get_transaction_id_from_response($response_body);

            if(array_key_exists("requestToken", $response_body)) {
                $requestToken = $response_body['requestToken'];    
            }
            if(array_key_exists("reasonCode", $response_body)) {
                $reasonCode = $response_body['reasonCode'];    
            }
            if( isset( $response_body['data']['reasonCode'] ) ) {
                $reasonCode = $response_body['data']['reasonCode'];
            }
            // 100 o 200 means the transaction was a success
            if ( $this->is_payment_successful( $response_body ) ) {
                // Payment successful
                $payment_result = true;
                $notification_client = __("Successful payment.", 'wp-pagalo-woocommerce') . " ";
            } else {
                //transiction fail
                $notification_client = __("Transaction has failed.", "wp-pagalo-woocommerce") . " ";
                if (array_key_exists("title",$response_body)) {
                    $notification_client .= _("Desition:", "wp-pagalo-woocommerce") . " " . strval($response_body['title']);
                }
                if (array_key_exists("mensaje",$response_body)) {
                    $notification_client .= strval($response_body['mensaje']);
                }
                if (array_key_exists("message",$response_body)) {
                    $notification_client .= strval($response_body['message']);
                }
                if (array_key_exists("responseText",$response_body)) {
                    if(is_array($response_body['responseText'])) {
                        foreach ($response_body['responseText'] as $err_msg) {
                            $notification_client .= $err_msg . '<br>';     
                        }                                                               
                    }
                    else {
                        $notification_client .= __("Description:", "wp-pagalo-woocommerce") . " " . strval($response_body['responseText']);
                    }

                }
                if (array_key_exists("reasonCode",$response_body)) {
                    $notification_client .= $this->get_the_error_message($response_body);
                }
                if( array_key_exists("data", $response_body) && array_key_exists("error_code", $response_body['data']) && is_array($response_body['data']['error_code']) && array_key_exists("description", $response_body['data']['error_code'][0]) ) {
                    $notification_client .= $response_body['data']['error_code'][0]['description'];

                }
            }
        }
        else {
            $payment_result = false;
            $notification_client = __("The conection with the paypemnt provider can not be established. Please try again latter.", "wp-pagalo-woocommerce");
        }

        $payment = array(
            'result'        => $payment_result,
            "message"       => $notification_client,
            'transaction_id' => $idTransaction,
            'requestToken'  => $requestToken,
            'reasonCode'    => $reasonCode,  
            'fullResponse'  => json_encode( $response_body ),
        );
        $this->log("process_response end");
        return $payment;
    }

    private function is_payment_successful($response_body) {
        //API V1
        if(  isset ( $response_body['reasonCode'] ) && ($response_body['reasonCode'] == '100' || $response_body['reasonCode'] == '00') ) {
            return true;
        }
        //API V2
        if ( isset( $response_body['data']['reasonCode'] ) && ($response_body['data']['reasonCode'] == '100' || $response_body['data']['reasonCode'] == '00') ) {
            return true;
        }
        return false;
    }

	private function get_transaction_id_from_response( $response_body ) {
        $transaction_id = '';
        if(array_key_exists("requestID", $response_body)) {
            $transaction_id = $response_body['requestID'];    
        } elseif(array_key_exists("requestIDReverse", $response_body)) {
            $transaction_id = $response_body['requestIDReverse'];    
        } elseif(array_key_exists("idTransaction", $response_body)) {
            $transaction_id = $response_body['idTransaction'];    
        } elseif (array_key_exists("transaccion", $response_body)) {
            $transaction_id = $response_body['transaccion']; 
        } elseif (array_key_exists("id_transaccion", $response_body)) {
            $transaction_id = $response_body['id_transaccion']; 
        }

        if( isset( $response_body['data']['uuid_transactions'] ) ) {
            $transaction_id = $response_body['data']['uuid_transactions'];
        }
        return $transaction_id;
    }


	public function get_error_notification_message($response_body) {
		$output = '';
		if (array_key_exists("title",$response_body)) {
			$output .= "Desición: " . strval($response_body['title']);
		}
		if (array_key_exists("mensaje",$response_body)) {
			$output .= strval($response_body['mensaje']);
		}
		if (array_key_exists("responseText",$response_body)) {
			if(is_array($response_body['responseText'])) {
				foreach ($response_body['responseText'] as $err_msg) {
					$output .= $err_msg .'<br>';     
				}                                                               
			}
			else {
				$output .= "Descripción: " . strval($response_body['responseText']);
			}

		}
		if (array_key_exists("descripcion",$response_body)) { 
			$output .= "Descripción: " . strval($response_body['descripcion']);

		}
		if (array_key_exists("reasonCode",$response_body)) {
			$output .= $this->get_the_error_message($response_body);
		}

		return $output;
	}

	/**
	 * get_response_body function
	 * 
	 * @return string
	 */
	public function get_response_body( $response ) {

		// get body response while get not error
		$response_body = wp_remote_retrieve_body( $response );

		foreach ( preg_split( "/\r?\n/", $response_body ) as $line ) {
			$resp = explode( "|", $line );
		}

		// values get
		$r = json_decode( $resp[0], true );

		return $r;
	}

	/**
	 * get_the_user_ip function
	 * 
	 * 
	 * @return string
	 */
	public function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * get_the_error_message function
	 * 
	 * @param string $response_body
	 * 
	 * @return string
	 */
	public function get_the_error_message($response_body) {
		$error_response_message = ' ';
		switch ($response_body['reasonCode']) {
			// Cybersource Response Codes
	    case 101:
        $error_response_message .= __('Required fields are missing.', 'wp-pagalo-woocommerce');
        break;
	    case 102:
        $error_response_message .= __('One ore more fields contains invalid data.', 'wp-pagalo-woocommerce');
        break;
        case 104:
        $error_response_message .= __('Please try again.', 'wp-pagalo-woocommerce');
        break;
        case 110:
        $error_response_message .= __('Transaction was not approved. Please try again.', 'wp-pagalo-woocommerce');
        break;
        case 150:
        $error_response_message .= __('Invalid transaction. Please contact support.', 'wp-pagalo-woocommerce');
        break;
        case 151:
        $error_response_message .= __('Time out.', 'wp-pagalo-woocommerce');
        break;
        case 152:
        $error_response_message .= __('Time out. Please contact support', 'wp-pagalo-woocommerce');
        break;
      	case 200:
        $error_response_message .= __('The authorization request was approved by the issuing bank but declined by CyberSource
because it did not pass the Address Verification Service (AVS) check.', 'wp-pagalo-woocommerce');
        break;
	    case 202:
        $error_response_message .= __('Your card has expired.', 'wp-pagalo-woocommerce');
        break;
	    case 204:
        $error_response_message .= __('There are not enough funds in the account.', 'wp-pagalo-woocommerce') . __('Try with a different credit card or contact your bank if you think this is a mistake.', 'wp-pagalo-woocommerce');
        break;
	    case 208:
        $error_response_message .= __('Tarjeta inactiva o no autorizada.', 'wp-pagalo-woocommerce') . __('Try with a different credit card or contact your bank if you think this is a mistake.', 'wp-pagalo-woocommerce');
        break;
	    case 209:
        $error_response_message .= __("CVV doesn't match.", 'wp-pagalo-woocommerce');
        break;
	    case 210:
        $error_response_message .= __("The card has reached it's credit limit.", 'wp-pagalo-woocommerce');
        break;
	    case 211:
        $error_response_message .= __("CVV is incorrect.", 'wp-pagalo-woocommerce');
        break;
	    case 230:
        $error_response_message .= __("Denied for not passing the CVV control.", 'wp-pagalo-woocommerce');
        break;
	    case 231:
        $error_response_message .= __("The credit card number is invalid.", 'wp-pagalo-woocommerce');
        break;
	    case 232:
        $error_response_message .= __("Ups! It looks that there is an error in the credit card", 'wp-pagalo-woocommerce');
	    case 233:
        $error_response_message .= __("The payment processor doesn't accept that type of card. Make sure you are using a Visa or MasterCard credit card.", 'wp-pagalo-woocommerce');
        break;
	    case 234:
        $error_response_message .= __("Antifraud system error.", 'wp-pagalo-woocommerce');
        break;
	    case 236:
        $error_response_message .= __("There was an error with the payment processor. Wait a few minutes and try again. If this error persists, contact the store owner for more information or alternative ways to pay.", 'wp-pagalo-woocommerce');
        break;
        case 251:
        $error_response_message .= __("Insufficient information about your address.", 'wp-pagalo-woocommerce');
        break;
      case 481:
        $error_response_message .= __("The transaction was not approved. Wait a few minutes and try again. If this error persists, contact the store owner for more information or alternative ways to pay.", 'wp-pagalo-woocommerce');
        break;

      // EPAY
      case 01:
        $error_response_message .= __("Denied, please refer to the bank", 'wp-pagalo-woocommerce');
        break;
      case 02:
        $error_response_message .= __("Denied, please refer to the bank", 'wp-pagalo-woocommerce');
        break;
      case 03:
        $error_response_message .= __("Invalid Merchant", 'wp-pagalo-woocommerce');
        break;
      case 04:
        $error_response_message .= __("Call to the bank", 'wp-pagalo-woocommerce');
        break;
      case 05:
        $error_response_message .= __("Transaction not accepted", 'wp-pagalo-woocommerce');
        break;
      case 12:
        $error_response_message .= __("Invalid Transaction", 'wp-pagalo-woocommerce');
        break;
      case 13:
        $error_response_message .= __("Insufficient funds", 'wp-pagalo-woocommerce');
        break;
      case 14:
        $error_response_message .= __("Invalid Card", 'wp-pagalo-woocommerce');
        break;
      case 15:
        $error_response_message .= __("Merchant Invalid", 'wp-pagalo-woocommerce');
        break;
      case 19:
        $error_response_message .= __("Try Again", 'wp-pagalo-woocommerce');
        break;
      case 30:
        $error_response_message .= __("Somre required fields are missing, please contact support", 'wp-pagalo-woocommerce');
        break;
      case 31:
        $error_response_message .= __("Credit card not supported", 'wp-pagalo-woocommerce');
        break;
      case 35:
        $error_response_message .= __("Credit card number is invalid.", 'wp-pagalo-woocommerce');
        break;
      case 41:
        $error_response_message .= __("Credit card number is reported as lost or stolen.", 'wp-pagalo-woocommerce');
        break;
      case 43:
        $error_response_message .= __("Credit card number is reported as lost or stolen.", 'wp-pagalo-woocommerce');
        break;
      case 51:
        $error_response_message .= __("Denied, Insufficient founds", 'wp-pagalo-woocommerce');
        break;
      case 54:
        $error_response_message .= __("Denied, Credit card expired", 'wp-pagalo-woocommerce');
        break;
      case 61:
        $error_response_message .= __("Denied, Exceeded ammount", 'wp-pagalo-woocommerce');
        break;
      case 62:
        $error_response_message .= __("Credit card is not allowed to make purchases, please contact your issuing bank.", 'wp-pagalo-woocommerce');
        break;
      case 65:
        $error_response_message .= __("Denied, number of transactions exceeded", 'wp-pagalo-woocommerce');
        break;

    	default:
        	$error_response_message .= __('Please contact the store owner for more information or alternative ways to pay.', 'wp-pagalo-woocommerce');
	  	}

		$error_response_message .= ' ';


		return $error_response_message;
	}

	private function log($message, $filename = "" ) {
        $date = new \DateTime('now');
        $message = $date->format('D M d, Y G:i') . ": " . $message . "\n";
		if ( $filename != "" ) {
			error_log( $message , 3, $filename);
		} else {
			error_log( $message );
		}

    }



}


}
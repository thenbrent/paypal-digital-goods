<?php
/**
 * An interface for the PayPal Digital Goods with Express Checkout API with an emphasis on being friendly to humans.
 * 
 * License: GPLv2 see license.txt
 * URL: https://github.com/thenbrent/paypal-digital-goods
 * Copyright (C) 2011 Leonard's Ego Pty. Ltd.
 */
class PayPal_Digital_Goods {

	/**
	 * A array of name value pairs representing the seller's API Username, Password & Signature. 
	 * 
	 * These are passed to the class via the parameters in the constructor. 
	 */
	private $api_credentials;

	/**
	 * Details of this particular recurring payment profile, including price, period & frequency.
	 */
	private $subscription;

	/**
	 * Stores the token once it has been acquired from PayPal
	 */
	private $token;

	/**
	 * The PayPal API Version. 
	 * Must be 65.1 or newer for Digital Goods. Defaults to 69
	 */
	private $version;

	/**
	 * The PayPal API URL Defaults to https://api-3t.sandbox.paypal.com/nvp when in
	 * sandbox mode and https://api-3t.paypal.com/nvp when live.
	 */
	private $endpoint;

	/**
	 * The PayPal URL for initialing the popup payment process.
	 * 
	 * Defaults to https://www.sandbox.paypal.com/incontext?token=**** for sandbox mode
	 * and https://www.paypal.com/incontext?token=**** in live mode.
	 */
	private $checkout_url;

	/**
	 * The URL on your site that the purchaser is sent to upon completing checkout.
	 */
	private $return_url;

	/**
	 * The URL on your site that the purchaser is sent to when cancelling a payment during the checkout process.
	 */
	private $cancel_url;

	/**
	 * Creates a PayPal Digital Goods Object configured according to the parameters in the args associative array. 
	 *
	 * @param api_credentials, required, a name => value array containing your API username, password and signature.
	 * @param args, named parameters to customise the subscription and checkout process.
	 * 			cancel_url, string, required. The URL on your site that the purchaser is sent to when cancelling a payment during the checkout process.
	 * 			return_url, string, required. The URL on your site that the purchaser is sent to upon completing checkout.
	 * 			sandbox, boolean. Flag to indicate whether to use the PayPal Sandbox or live PayPal site for the transaction. Default true.
	 * 			version, string. The PayPal API version. Must be a minimum of 65.1. Default 69.0
	 * 			currency, string. The ISO 4217 currency code for the transaction. Default USD.
	 * 			subscription, array, details of the recurring payment profile to be created.
	 * 				start_date, date, default 24 hours in the future. The start date for the profile, defaults to one day in the future. Must take the form YYYY-MM-DDTHH:MM:SS and can not be in the past.
	 * 				description, string, Brief description of the subscription as shown to the subscriber in their PayPal account.
	 * 				amount, double, default 25.00. The price per period for the subscription.
	 * 				initial_amount, double, default 0. An optional sign up fee.
	 * 				average_amount, double, default 25.00. The average transaction amount, PayPal default is $25, only set higher if your monthly subscription value is higher
	 * 				period, Day|Week|Month|Semimonth, default Month. The unit of interval between billing.
	 * 				frequency, integer, default 1. How regularly to charge the amount. When period is Month, a frequency value of 1 would charge every month while a frequency value of 12 charges once every year.
	 * 				total_cycles, integer, default perpetuity. The total number of occasions the subscriber should be charged. When period is month and frequency is 1, 12 would continue the subscription for one year. The default value 0 will continue the payment for perpetuity.
	 * 				trial_amount, double, default 0. The price per trial period.
	 * 				trial_period, Day|Week|Month|Semimonth, default Month. The unit of interval between trial period billing.
	 * 				trial_frequency, integer, default 0. How regularly to charge the amount.
	 * 				trial_total_cycles, integer, default perpetuity. 
	 */
	function __construct( $api_credentials, $args = array() ){

		if( empty( $api_credentials['username'] ) || empty( $api_credentials['password'] ) || empty( $api_credentials['signature'] ) )
			exit( 'You must specify your PayPal API username, password & signature in the $api_credentials array. For details of how to ' );
		elseif( empty( $args['return_url'] ) || empty( $args['cancel_url'] ) )
			exit( 'You must specify a return_url & cancel_url.' );

		// Long form to show the required structure of the array
		$this->api_credentials = array(
			'username'  => $api_credentials['username'],
			'password'  => $api_credentials['password'],
			'signature' => $api_credentials['signature']
		);
		$this->api_credentials = (object)$this->api_credentials; // Readbility

		$default_subscription = array(
			'start_date'         => date( 'Y-m-d\TH:i:s', time() + ( 24 * 60 * 60 ) ),
			'description'        => 'Assorted Online Services Subscription',
			// Price
			'amount'             => '25.00',
			'initial_amount'     => '0.00',
			'average_amount'     => '25',
			// Temporal Details
			'period'             => 'Month',
			'frequency'          => '1',
			'total_cycles'       => '0',
			// Trial Period
			'trial_amount'       => '0.00',
			'trial_period'       => 'Month',
			'trial_frequency'    => '0',
			'trial_total_cycles' => '0'
		);

		$defaults = array(
			'sandbox'         => true,
			'version'         => '69.0',
			'currency'        => 'USD',
			'return_url'      => '',
			'cancel_url'      => '',
			'subscription'    => $default_subscription
		);

		$args = array_merge( $defaults, $args );

		$this->version      = $args['version'];

		$this->currency     = $args['currency'];
		$this->subscription = (object)$args['subscription'];

		$this->endpoint     = ( $args['sandbox'] ) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$this->checkout_url = ( $args['sandbox'] ) ? 'https://www.sandbox.paypal.com/incontext?token=' : 'https://www.paypal.com/incontext?token=';

		$this->return_url	= $args['return_url'];
		$this->cancel_url	= $args['cancel_url'];
	}

	/**
	 * Map this object's API credentials to the PayPal NVP format for posting to the API.
	 * 
	 * Abstracted from @see get_payment_details_url for readability. 
	 */
	function get_api_credentials_url(){

		return 'USER=' . urlencode( $this->api_credentials->username )
			 . '&PWD=' . urlencode( $this->api_credentials->password )
			 . '&SIGNATURE=' . urlencode( $this->api_credentials->signature )
			 . '&VERSION='.  urlencode( $this->version );
	}

	/**
	 * Map this object's transaction details to the PayPal NVP format for posting to PayPal.
	 */
	function get_payment_details_url( $action ){

		if( empty( $this->token ) && isset( $_GET['token'] ) )
			$this->token = $_GET['token'];

		// Setup the Payment Details
		$api_request = $this->get_api_credentials_url();

		// Parameters to Request Recurring Payment Token
		if( 'SetExpressCheckout' == $action ) {

			$api_request  .=  '&METHOD=SetExpressCheckout'
							. '&RETURNURL=' . urlencode( $this->return_url )
							. '&CANCELURL=' . urlencode( $this->cancel_url )
							. '&BILLINGTYPE=RecurringPayments'
							. '&BILLINGAGREEMENTDESCRIPTION=' . urlencode( $this->subscription->description )
							. '&CURRENCYCODE=' . urlencode( $this->currency )
							. '&MAXAMT=' . urlencode( $this->subscription->average_amount );

		} elseif ( 'CreateRecurringPaymentsProfile' == $action ) {

			$api_request  .=  '&METHOD=CreateRecurringPaymentsProfile' 
							. '&TOKEN=' . $this->token
							// Details
							. '&DESC=' . urlencode( $this->subscription->description )
							. '&CURRENCYCODE=' . urlencode( $this->currency )
							. '&PROFILESTARTDATE=' . urlencode( $this->subscription->start_date )
							// Price
							. '&AMT=' . urlencode( $this->subscription->amount )
							. '&INITAMT=' . urlencode( $this->subscription->initial_amount )
							// Period
							. '&BILLINGPERIOD=' . urlencode( $this->subscription->period )
							. '&BILLINGFREQUENCY=' . urlencode( $this->subscription->frequency )
							. '&TOTALBILLINGCYCLES=' . urlencode( $this->subscription->total_cycles )
							// Specify Digital Good Payment
							. "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital" // Best rates for Digital Goods sale
							. "&L_PAYMENTREQUEST_0_NAME0=" . urlencode( $this->subscription->description )
							. "&L_PAYMENTREQUEST_0_AMT0=" . urlencode( $this->subscription->amount )
							. "&L_PAYMENTREQUEST_0_QTY0=1";

			// Maybe add a trial period
			if( $this->subscription->trial_frequency > 0 || $this->subscription->trial_total_cycles > 0 ) {
			$api_request  .=  '&TRIALAMT=' . urlencode( $this->subscription->trial_amount )
							. '&TRIALBILLINGPERIOD=' . urlencode( $this->subscription->trial_period )
							. '&TRIALBILLINGFREQUENCY=' . urlencode( $this->subscription->trial_frequency )
							. '&TRIALTOTALBILLINGCYCLES=' . urlencode( $this->subscription->trial_total_cycles );
			}

		} elseif ( 'GetExpressCheckoutDetails' == $action ) {

			$api_request .= '&METHOD=GetExpressCheckoutDetails'
						  . '&TOKEN=' . $this->token;

		} elseif ( 'GetRecurringPaymentsProfileDetails' == $action ) {

			$api_request .= '&METHOD=GetRecurringPaymentsProfileDetails'
						  . '&ProfileID=' . 'I%2d17ERNH4GT97W'; // testing

		}

		return $api_request;
	}


	/**
	 * Creates a payment profile with PayPal represented by the token it returns.
	 * 
	 * When a buyer clicks the Pay with PayPal button, this function calls the PayPal SetExpressCheckout API operation.
	 *
	 * It passes payment details of the items purchased and therefore, set_payment_details() must have been called
	 * before this function.
	 *
	 * Return:
	 * 
	 */
	function request_checkout_token(){
		$response = $this->call_paypal( 'SetExpressCheckout' );

		$this->token = $response['TOKEN'];

		return $response;
	}


	/**
	 * Creates a subscription by calling the PayPal CreateRecurringPaymentsProfile API operation.
	 * 
	 * After a billing agreement has been created in request_checkout_token, this function can
	 * be called to start the subscription.
	 * 
	 * This function returned the response from PayPal, which includes a profile ID (PROFILEID). You should
	 * save this profile ID to aid with changing and 
	 *
	 * IMPORTANT: PayPal does not create the recurring payments profile until you receive a success response 
	 * from the CreateRecurringPaymentsProfile call.
	 * 
	 * Return: array(
	 * 	[PROFILEID] => I%2d537HXDRJCH67
	 * 	[PROFILESTATUS] => PendingProfile
	 * 	[TIMESTAMP] => 2011%2d06%2d25T09%3a18%3a19Z
	 * 	[CORRELATIONID] => beba80198304d
	 * 	[ACK] => Success
	 * 	[VERSION] => URL Encoded API Version, eg 
	 * 	[BUILD] => 1907759
	 * 
	 */
	function start_subscription(){
		return $this->call_paypal( 'CreateRecurringPaymentsProfile' );
	}


	/**
	 * Returns information about a subscription by calling the PayPal GetRecurringPaymentsProfileDetails API method.
	 * 
	 * @param $from, string, default PayPal. The Subscription details can be sourced from the internal object properties or from PayPal
	 * 
	 */
	function get_subscription_details( $from = 'paypal' ){
		if( $from == 'class-properties' ){
			return $this->subscription;
		} else {
			//GetRecurringPaymentsProfileDetails
			return $this->call_paypal( 'GetRecurringPaymentsProfileDetails' );
		}
	}


	/**
	 * Calls the PayPal GetExpressCheckoutDetails methods and returns a more nicely formatted response
	 * 
	 * Called internally on return from set_express_checkout(). Can be called anytime to get details of 
	 * a transaction for which you have the Token.
	 */
	function get_checkout_details(){
		return $this->call_paypal( 'GetExpressCheckoutDetails' );
	}


	/**
	 * Post to PayPal
	 * 
	 * Makes an API call using an NVP String and an Endpoint. Based on code available here: https://www.x.com/blogs/Nate/2011/01/07/digital-goods-with-express-checkout-in-php
	 * 
	 * @param action, string, required. The API operation to be performed, eg. GetExpressCheckoutDetails. The action is abstracted from you (the developer) by the appropriate helper function eg. GetExpressCheckoutDetails via get_checkout_details()
	 */
	function call_paypal( $action ){

		// Use the one function for all PayPal API operations
		$api_parameters = $this->get_payment_details_url( $action );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->endpoint );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

		// Turn off server and peer verification
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Set the API parameters for this transaction
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_parameters );

		// Request response from PayPal
		$response = curl_exec( $ch );

		// If no response was received from PayPal there is no point parsing the response
		if( ! $response ) {
			$response = $action . " failed: " . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')';
			return $response;
		}

		// An associative array is more usable than a parameter string
		$response        = explode( '&', $response );
		$parsed_response = array();
		foreach ( $response as $value ) {
			$temp = explode( '=', $value );
			if( sizeof( $temp ) > 1 ) {
				$parsed_response[$temp[0]] = $temp[1];
			}
		}

		if( ( 0 == sizeof( $parsed_response ) ) || ! array_key_exists( 'ACK', $parsed_response ) ) {
			$response = "Invalid HTTP Response for POST request($api_parameters) to " . $this->endpoint;
			return $response;
		}

		return $parsed_response;
	}


	/**
	 * The Javascript to invoke the digital goods checkout process.
	 * 
	 * No need to call this function manually, required scripts are automatically printed with @see print_buy_buttion(). 
	 * If you do print this script manually, print it after the button in the DOM to ensure the 
	 * click event is properly hooked.
	 */
	function get_script( $element_id = '' ){
		
		if( empty( $element_id ) )
			$element_id = 'paypal-submit';

		$dg_script  = '<script src ="https://www.paypalobjects.com/js/external/dg.js" type="text/javascript"></script>'
					. '<script>'
					. 'var dg = new PAYPAL.apps.DGFlow({'
					. 'trigger: "' . $element_id . '"' // the ID of the HTML element which calls setExpressCheckout
					. '}); </script>';
		return $dg_script;
	}


	/**
	 * Create and return the Buy (or Subscribe) button for your page. 
	 * 
	 * PayPal requires a token for checkout, so this function takes care of requesting the token. 
	 */
	function get_buy_button(){
		if( empty( $this->token ) ) {
			$this->request_checkout_token();
		}

		return '<a href="' . $this->checkout_url . $this->token . '" id="paypal-submit"><img src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif" border="0" /></a>';
	}


	/**
	 * Print the Buy (or Subscribe) button for this API object as well as the scripts 
	 * required by the button.
	 * 
	 * If you want to manually insert the script at a different position in your page,
	 * @see get_buy_button().
	 * 
	 * @uses get_buy_button()
	 * @uses get_script()
	 */
	function print_buy_button(){
		echo $this->get_buy_button();
		echo $this->get_script();
	}


	/**
	 * Takes a PayPal API Key and maps it to a more human friendly term. 
	 * 
	 * Has varying degrees of usefulness, keys like TOKEN are intelligible, 
	 * while keys like AMT are ambiguous.
	 */
	function map_for_human( $key ){

		switch( $key ) {
			case 'AMT':
				$key = 'amount';
				break;
		}

		return $key;
	}

}

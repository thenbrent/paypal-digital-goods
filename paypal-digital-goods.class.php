<?php
/**
 * An interface for the PayPal Digital Goods for Express Checkout API with an emphasis on being friendly to humans.
 * 
 * Supported PayPal API Operations:
 * 	- SetExpressCheckout via request_checkout_token()
 * 	- GetExpressCheckoutDetails via get_subscription_details()
 * 	- CreateRecurringPaymentsProfile via start_subscription()
 *
 * Glossary:
 * If you are fluent in the verbose PayPal lexicon, you will find some of the terms in this library differ to those used
 * in PayPal's documentation. This class translates the following PayPal-isms to the human vernacular.
 * 	- Recurring Payment Profile is referred to as a subscription
 * 	- Digital Goods for Express Checkout is referred to as checkout
 *
 * Limitations:
 * 	The class currently only supports recurring payments as this is all that I required. Future versions may include 
 * 	purchase of digital goods as well as recurring payments.
 * 	The class also only support creating one recurring payments profile, where as PayPal docs hypothesise that is possible
 * 	to create up to 10 different profiles in one transaction. 
 * 
 * Roadmap:
 * 	Future versions will include:
 * 	- Payments for purchasing items.
 * 	- Parsed responses from API calls with urldecoded values and keys translated into more human friendly terms.
 * 
 * License: GPL v2 see license.txt
 * URL: https://github.com/thenbrent/paypal-digital-goods
 * Copyright (C) 2011 Leonard's Ego Pty. Ltd.
 */

class PayPal_Digital_Goods {

	/**
	 * A array of name value pairs representing the seller's API Username, Password & Signature. 
	 */
	private $api_credentials;

	/**
	 * Details of the recurring payment profile
	 */
	private $subscription;

	/**
	 * Stores the token once it has been acquired from PayPal
	 */
	private $token;

	/**
	 * The PayPal API Version. 
	 * Must be 65.1 or newer for Digitial Goods. Defaults to 65.1
	 */
	private $version;

	/**
	 * The URL 
	 */
	private $endpoint; //"https://api-3t.sandbox.paypal.com/nvp"

	/**
	 * The URL for redirecting the user to login and confirm the payment
	 */
	private $redirect_url;

	/**
	 * The URL to redirect to upon completion of a successful payment
	 */
	private $return_url;
	
	/**
	 * The URL to redirect to when a payment is canceled
	 */
	private $cancel_url;

	/**
	 * Create a PayPal Digital Goods Object. 
	 *
	 * @param api_credentials, required, a name => value array containing your API username, password and signature.
	 * @param args, optional but recommended
	 * 			sandbox, boolean, default true, flag to indicate whether to use the PayPal Sandbox or live PayPal site for the payment
	 * 			version, string, default 69.0
	 * 			currency, strign, default 
	 * 			subscription, array, details of the recurring payment profile to be created
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

		// API Credentials are required
		$this->api_credentials = array( // Long form to show the required structure of the array
			'username'  => $api_credentials['username'],
			'password'  => $api_credentials['password'],
			'signature' => $api_credentials['signature']
		);

		$this->api_credentials = (object)$this->api_credentials; // 'cause-it-looks-betta-ay

		// All other arguments are optional
		$defaults = array(
			'sandbox'         => true,
			'version'         => '69.0',
			'currency'        => 'USD',
			'cancel_url'      => 'http://localhost/paypal-digital-goods/examples/return.php?return=cancel',
			'return_url'      => 'http://localhost/paypal-digital-goods/examples/return.php?return=paid',
			'subscription'    => array(
				'start_date'         => date( 'Y-m-d\TH:i:s', time() + ( 24 * 60 * 60 ) ),
				'description'        => 'Assorted Online Services Subscription',
				// Price of the Subscription
				'amount'             => '25.00',
				'initial_amount'     => '0.00',
				'average_amount'     => '25',
				// Temporal Details of the Subscription
				'period'             => 'Month',
				'frequency'          => '1',
				'total_cycles'       => '0',
				// Trial Period details
				'trial_amount'       => '0.00',
				'trial_period'       => 'Month',
				'trial_frequency'    => '0',
				'trial_total_cycles' => '0'
			)
		);

		$args = array_merge( $defaults, $args );

		$this->version      = $args['version'];

		$this->currency     = $args['currency'];
		$this->subscription = (object)$args['subscription'];

		$this->endpoint     = ( $args['sandbox'] ) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$this->redirect_url = ( $args['sandbox'] ) ? 'https://www.sandbox.paypal.com/incontext?token=' : 'https://www.paypal.com/incontext?token=';

		$this->return_url	= $args['return_url'];
		$this->cancel_url	= $args['cancel_url'];
	}

	/**
	 * Map this object's API credentials to the PayPal NVP format for posting to the API.
	 */
	function get_api_credentials_url(){

		return 'USER=' . urlencode( $this->api_credentials->username )
			 . '&PWD=' . urlencode( $this->api_credentials->password )
			 . '&SIGNATURE=' . urlencode( $this->api_credentials->signature )
			 . '&VERSION='.  urlencode( $this->version );
	}

	/**
	 * Map this object's payment details to the PayPal NVP format for posting to the API.
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
	 * Makes an API call using an NVP String and an Endpoint
	 * 
	 * Based on code available here: https://www.x.com/blogs/Nate/2011/01/07/digital-goods-with-express-checkout-in-php
	 */
	function call_paypal( $action ){

		// Create the API string according to the specified action
		$api_parameters = $this->get_payment_details_url( $action );

		// Create a CURL object for the PayPal API endpoint
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->endpoint );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

		// Turn off server and peer verification
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Set the API parameters to post for this transaction
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_parameters );

		// Request response from PayPal
		$response = curl_exec( $ch );

		// Return immediately if no response was received from PayPal
		if( ! $response ) {
			$response = $action . " failed: " . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')';
			return $response;
		}

		// Convert the response into a more usable associative array
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
	 * The Javascript for invoking the digital goods payments flow.
	 * 
	 * No need to call this function manually, required scripts are automatically printed with @see print_buy_buttion(). 
	 * In fact, you're better off not to print this script manually, because if it is printed before the button, the
	 * iframe won't be properly linked.
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
	 * The Buy (or Subscribe) button for your page. 
	 * 
	 * Before printing the button, this function 
	 */
	function get_buy_button(){
		if( empty( $this->token ) ) {
			$this->request_checkout_token();
		}

		return '<a href="' . $this->redirect_url . $this->token . '" id="paypal-submit"><img src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif" border="0" /></a>';
	}


	/**
	 * Print the Buy (or Subscribe) button for this API object as well as the required script for the button to be used.
	 * 
	 * If you want to manually insert the script, @see get_buy_button() instead.
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
	 * Has varying degrees of usefulness, keys like TOKEN are intelligible, while keys like 
	 * 
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

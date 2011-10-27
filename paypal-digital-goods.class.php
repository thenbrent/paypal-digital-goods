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
	 * Must be 65.1 or newer for Digital Goods. Defaults to 76
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
	public $return_url;

	/**
	 * The URL on your site that the purchaser is sent to when cancelling a payment during the checkout process.
	 */
	public $cancel_url;

	/**
	 * Creates a PayPal Digital Goods Object configured according to the parameters in the args associative array. 
	 * 
	 * Available $args parameters:
	 * - cancel_url, string, required. The URL on your site that the purchaser is sent to when cancelling a payment during the checkout process.
	 * - return_url, string, required. The URL on your site that the purchaser is sent to upon completing checkout.
	 * - sandbox, boolean. Flag to indicate whether to use the PayPal Sandbox or live PayPal site. Default true - use sandbox.
	 * - currency, string. The ISO 4217 currency code for the transaction. Default USD.
	 * - callback, string. URL to which the callback request from PayPal is sent. It must start with HTTPS for production integration. It can start with HTTPS or HTTP for sandbox testing
	 * - business_name, string. A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages.
	 * - subscription, array, details of the recurring payment profile to be created.
	 * 		- description, string, Brief description of the subscription as shown to the subscriber in their PayPal account.
	 * 		Subscription price parameters (default: $25 per month):
	 * 		- amount, double, default 25.00. The price per period for the subscription.
	 * 		- initial_amount, double, default 0. An optional sign up fee.
	 * 		- average_amount, double, default 25.00. The average transaction amount, PayPal default is $25, only set higher if your monthly subscription value is higher
	 * 		Subscription temporal parameters (default: bill once per month forever):
	 * 		- start_date, date, default 24 hours in the future. The start date for the profile, defaults to one day in the future. Must take the form YYYY-MM-DDTHH:MM:SS and can not be in the past.
	 * 		- period, Day|Week|Month|Semimonth, default Month. The unit of interval between billing.
	 * 		- frequency, integer, default 1. How regularly to charge the amount. When period is Month, a frequency value of 1 would charge every month while a frequency value of 12 charges once every year.
	 * 		- total_cycles, integer, default perpetuity. The total number of occasions the subscriber should be charged. When period is month and frequency is 1, 12 would continue the subscription for one year. The default value 0 will continue the payment for perpetuity.
	 * 		Subscription trail period parameters (default: no trial period):
	 * 		- trial_amount, double, default 0. The price per trial period.
	 * 		- trial_period, Day|Week|Month|Semimonth, default Month. The unit of interval between trial period billing.
	 * 		- trial_frequency, integer, default 0. How regularly to charge the amount.
	 * 		- trial_total_cycles, integer, default perpetuity. 
	 * - version, string. The PayPal API version. Must be a minimum of 65.1. Default 76.0
	 * 
	 * @param api_credentials, required, a name => value array containing your API username, password and signature.
	 * @param args, named parameters to customise the subscription and checkout process. See description for available parameters.
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

		$defaults = array(
			'sandbox'         => true,
			'version'         => '76.0',
			'currency'        => 'USD',
			'callback'        => '',
			'business_name'   => '',
			'subscription'    => array(
				'description'        => 'Digital Goods Subscription',
				// Price
				'amount'             => '25.00',
				'initial_amount'     => '0.00',
				'average_amount'     => '25',
				// Temporal Details
				'start_date'         => date( 'Y-m-d\TH:i:s', time() + ( 24 * 60 * 60 ) ),
				'period'             => 'Month',
				'frequency'          => '1',
				'total_cycles'       => '0',
				// Trial Period
				'trial_amount'       => '0.00',
				'trial_period'       => 'Month',
				'trial_frequency'    => '0',
				'trial_total_cycles' => '0',
				// Miscellaneous
				'add_to_next_bill'  => true,
			)
		);

		$args['subscription'] = array_merge( $defaults['subscription'], $args['subscription'] );
		$args = array_merge( $defaults, $args );

		$this->version       = $args['version'];
		$this->currency      = $args['currency'];
		$this->callback      = $args['callback'];
		$this->business_name = $args['business_name'];

		$this->subscription  = (object)$args['subscription'];

		$this->endpoint      = ( $args['sandbox'] ) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		$this->checkout_url  = ( $args['sandbox'] ) ? 'https://www.sandbox.paypal.com/incontext?token=' : 'https://www.paypal.com/incontext?token=';

		$this->return_url    = $args['return_url'];
		$this->cancel_url    = $args['cancel_url'];
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
	function get_payment_details_url( $action, $profile_id = '' ){

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

			if( ! empty( $this->callback ) )
				$api_request  .=  '&CALLBACK=' . urlencode( $this->callback );

			if( ! empty( $this->business_name ) )
				$api_request  .=  '&BRANDNAME=' . urlencode( $this->business_name );

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

			if( $this->subscription->add_to_next_bill == true )
				$api_request  .= '&AUTOBILLOUTAMT=AddToNextBilling';

		} elseif ( 'GetExpressCheckoutDetails' == $action ) {

			$api_request .= '&METHOD=GetExpressCheckoutDetails'
						  . '&TOKEN=' . $this->token;

		} elseif ( 'GetRecurringPaymentsProfileDetails' == $action ) {

			$api_request .= '&METHOD=GetRecurringPaymentsProfileDetails'
						  . '&ProfileID=' . urlencode( $profile_id );

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
	 * @param $from, string, default PayPal. The Subscription details can be sourced from the object's properties if you know they will be already set or from PayPal (default).
	 */
	function get_profile_details( $profile_id ){

		return $this->call_paypal( 'GetRecurringPaymentsProfileDetails', $profile_id );
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
	function call_paypal( $action, $profile_id = '' ){

		// Use the one function for all PayPal API operations
		$api_parameters = $this->get_payment_details_url( $action, $profile_id );

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
		if( ! $response )
			die($action . ' failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')');

		curl_close($ch);

		// An associative array is more usable than a parameter string
		$response        = explode( '&', $response );
		$parsed_response = array();
		foreach ( $response as $value ) {
			$temp = explode( '=', $value );
			if( sizeof( $temp ) > 1 ) {
				$parsed_response[$temp[0]] = urldecode( $temp[1] );
			}
		}

		if( ( 0 == sizeof( $parsed_response ) ) || ! array_key_exists( 'ACK', $parsed_response ) )
			die("Invalid HTTP Response for POST request($api_parameters) to " . $this->endpoint);

		if( $parsed_response['ACK'] == 'Failure' )
			die( "Calling PayPal with action $action has Failed: " . $parsed_response['L_LONGMESSAGE0'] );

		return $parsed_response;
	}


	/**
	 * Returns this instance of the class's token.
	 */
	function token(){
		if( empty( $this->token ) ) {
			$this->request_checkout_token();
		}

		return $this->token;
	}


	/**
	 * The Javascript to invoke the digital goods checkout process.
	 * 
	 * No need to call this function manually, required scripts are automatically printed with @see print_buy_buttion(). 
	 * If you do print this script manually, print it after the button in the DOM to ensure the 
	 * click event is properly hooked.
	 */
	function get_script( $args = array() ){
		
		if( empty( $args['element_id'] ) )
			$args['element_id'] = 'paypal-submit';

		$dg_script  = '<script src ="https://www.paypalobjects.com/js/external/dg.js" type="text/javascript"></script>'
					. '<script>'
					. 'var dg = new PAYPAL.apps.DGFlow({'
					. 'trigger: "' . $args['element_id'] . '"' // the ID of the HTML element which calls setExpressCheckout
					. '}); </script>';

		return $dg_script;
	}


	/**
	 * Create and return the Buy (or Subscribe) button for your page. 
	 * 
	 * The button can be output either as a link to a image submit button, link to a page
	 * or link directly to PayPal (default). 
	 * 
	 * The simplest method is to pass no parameters and have the button be a link directly to
	 * PayPal; however, the drawback of this approach is a slower load time for the page on which 
	 * the button is included.
	 * 
	 * @param args array. Name => value parameters to customise the buy button. 
	 * 			'id' string. The id of the submit element. Defaults to 'paypal-submit'. 
	 * 			'element' string. The type of element to use as the button. Either anchor or submit. Default 'anchor'.
	 * 			'href' string. The URL for 'anchor' tag. Ignored when 'element' is 'submit'. Default $this->checkout_url. 
	 * 			'get_token' boolean. Whether to include a token with the href. Overridden by 'element' when it is 'submit'.
	 */
	function get_buy_button( $args = array() ){

		$defaults = array(  'id'        => 'paypal-submit',
							'type'      => 'anchor',
							'href'      => $this->checkout_url,
							'alt'       => 'Submit',
							'get_token' => true
					);

		$args = array_merge( $defaults, $args );

		if( $args['type'] == 'anchor' ) {
			if( $args['get_token'] == true && empty( $this->token ) )
				$this->request_checkout_token();

			// Include the token in the href if the default href is not overridden
			if( $args['href'] == $this->checkout_url )
				$args['href'] .= $this->token;

			$button = '<a href="' . $args['href'] . '" id="' . $args['id'] . '" alt="' . $args['alt'] . '"><img src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif" border="0" /></a>';
		} else {
			$button = '<input type="image" id="' . $args['id'] . '" alt="' . $args['alt'] . '" src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif">';
		}

		return $button;
	}


	/**
	 * Print the Buy (or Subscribe) button for this API object as well as the scripts 
	 * required by the button.
	 * 
	 * If you want to manually insert the script at a different position in your page,
	 * you can manually call @see get_buy_button() & @see get_script().
	 * 
	 * @uses get_buy_button()
	 * @uses get_script()
	 */
	function print_buy_button( $args = array() ){
		echo $this->get_buy_button( $args );
		echo $this->get_script( $args );
	}


	/**
	 * Returns the Checkout URL including a token for this transaction. 
	 */
	function get_checkout_url() {
		if( empty( $this->token ) )
			$this->request_checkout_token();

		// Include the token in the href if the default href is not overridden
		return $this->checkout_url . $this->token;
	}

	/**
	 * Get the description for this subscription
	 */
	function get_description(){
		return $this->subscription->description;
	}


	/**
	 * Returns a string representing the details of the subscription. 
	 * 
	 * For example "$10 sign-up fee then $20 per Month for 3 Months". 
	 * 
	 * @param $echo bool, Optionally print the string before returning it.
	 */
	function get_subscription_string( $echo = false ){

		$subscription_details = '';

		if( $this->subscription->initial_amount != '0.00' )
			$subscription_details .= sprintf( '%s%s sign-up fee then', $this->get_currency_symbol(), $this->subscription->initial_amount );

		if( $this->subscription->trial_frequency > 0 || $this->subscription->trial_total_cycles > 0 ) {
			$subscription_details .= sprintf( ' %s %s', $this->subscription->trial_total_cycles, strtolower( $this->subscription->trial_period ) );
			if( $this->subscription->trial_amount > '0.00' ) {
				if( $this->subscription->trial_frequency > 1 )
					$subscription_details .= sprintf( ' trial period charged at %s%s every %s %ss followed by', $this->get_currency_symbol(), $this->subscription->trial_amount, $this->subscription->trial_frequency, strtolower( $this->subscription->trial_period ) );
				else
					$subscription_details .= sprintf( ' trial period charged at %s%s per %s followed by', $this->get_currency_symbol(), $this->subscription->trial_amount, strtolower( $this->subscription->trial_period ) );
			} else {
					$subscription_details .= sprintf( ' free trial period followed by' );
			}
		}

		if( $this->subscription->frequency > 1 )
			$subscription_details .= sprintf( ' %s%s every %s %ss', $this->get_currency_symbol(), $this->subscription->amount, $this->subscription->frequency, strtolower( $this->subscription->period ) );
		else
			$subscription_details .= sprintf( ' %s%s per %s', $this->get_currency_symbol(), $this->subscription->amount, strtolower( $this->subscription->period ) );

		if( $this->subscription->total_cycles != 0 )
			$subscription_details .= sprintf( ' for %s %ss', $this->subscription->total_cycles, strtolower( $this->subscription->period ) );

		return $subscription_details;
	}


	/**
	 * Get the symbol associated with a currency, optionally specified with '$currency_code' parameter. 
	 * 
	 * Will always return the symbol and can optionally also print the symbol.
	 * 
	 * @param $currency_code, string, optional, the ISO 4217 Code of the currency for which you want the Symbol, default the currency code of this object
	 * @param $echo bool, Optionally print the symbol before returning it.
	 **/
	function get_currency_symbol( $currency_code = '', $echo = false ){

		if( empty( $currency_code ) )
			$currency_code = $this->currency;

		switch( $currency_code ) {
			case 'AUD' :
			case 'CAD' :
			case 'NZD' :
			case 'SGD' :
			case 'HKD' :
			case 'TWD' :
			case 'USD' :
				$currency_symbol = '$';
				break;
			case 'DKK' :
			case 'NOK' :
			case 'SEK' :
				$currency_symbol = 'kr';
				break;
			case 'EUR' :
				$currency_symbol = '&euro;';
				break;
			case 'GBP' :
				$currency_symbol = '&pound;';
				break;
			case 'JPY' :
				$currency_symbol = '&yen;';
				break;
			case 'CZK' :
				$currency_symbol = 'Kč';
				break;
			case 'HUF' :
				$currency_symbol = 'Ft';
				break;
			case 'PLN' :
				$currency_symbol = 'zł';
				break;
			case 'CHF' :
				$currency_symbol = 'CHF';
				break;
		}

		if( $echo )
			echo $currency_symbol;

		return $currency_symbol;
	}


	/**
	 * Get the value of a given subscription detail, eg. amount
	 * 
	 * For a list of the available values of $key, see the $defaults array in the constructor.
	 */
	function get_subscription_detail( $key ){

		if( isset( $this->$key ) )
			$value = $this->$key;
		elseif( isset( $this->subscription->$key ) )
			$value = $this->subscription->$key;
		else
			$value = false;

		return $value;
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

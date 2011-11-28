<?php
/**
 * An interface for the PayPal Digital Goods with Express Checkout API with an emphasis on being friendly to humans.
 * 
 * @package    PayPal
 * 
 * @license    GPLv3 see license.txt
 * @copyright  2011 Leonard's Ego Pty. Ltd.
 */

require_once( 'paypal-configuration.class.php' );

abstract class PayPal_Digital_Goods {

	/**
	 * A array of name value pairs representing the seller's API Username, Password & Signature. 
	 * 
	 * These are passed to the class via the parameters in the constructor. 
	 */
	private $api_credentials;

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
	 * - version, string. The PayPal API version. Must be a minimum of 65.1. Default 76.0
	 * 
	 * @param api_credentials, required, a name => value array containing your API username, password and signature.
	 * @param args, named parameters to customise the subscription and checkout process. See description for available parameters.
	 */
	function __construct( $args = array() ){

		if( '' == PayPal_Digital_Goods_Configuration::username() || '' == PayPal_Digital_Goods_Configuration::password() || '' == PayPal_Digital_Goods_Configuration::signature() )
			exit( 'You must specify your PayPal API username, password & signature in the $api_credentials array. For details of how to ' );
		elseif( ( empty( $args['return_url'] ) && '' == PayPal_Digital_Goods_Configuration::username() ) || ( empty( $args['cancel_url'] ) && '' == PayPal_Digital_Goods_Configuration::cancel_url() ) )
			exit( 'You must specify a return_url & cancel_url.' );

		$defaults = array(
			'sandbox'       => true,
			'version'       => '76.0',
			'currency'      => 'USD',
			'callback'      => '',
			'business_name' => '',
			'return_url'    => PayPal_Digital_Goods_Configuration::return_url(),
			'cancel_url'    => PayPal_Digital_Goods_Configuration::cancel_url()
		);

		$args = array_merge( $defaults, $args );

		$this->callback      = $args['callback'];
		$this->business_name = $args['business_name'];

		$this->return_url    = $args['return_url'];
		$this->cancel_url    = $args['cancel_url'];
	}

	/**
	 * Map this object's API credentials to the PayPal NVP format for posting to the API.
	 * 
	 * Abstracted from @see get_payment_details_url for readability. 
	 */
	function get_api_credentials_url(){

		return 'USER=' . urlencode( PayPal_Digital_Goods_Configuration::username() )
			 . '&PWD=' . urlencode( PayPal_Digital_Goods_Configuration::password() )
			 . '&SIGNATURE=' . urlencode( PayPal_Digital_Goods_Configuration::signature() )
			 . '&VERSION='.  urlencode( PayPal_Digital_Goods_Configuration::version() );
	}

	/**
	 * Map this object's transaction details to the PayPal NVP format for posting to PayPal.
	 * 
	 * @param $action, string. The PayPal NVP API action to create the URL for. One of SetExpressCheckout, CreateRecurringPaymentsProfile or GetRecurringPaymentsProfileDetails.
	 * @param $profile_id, (optional) string. A PayPal Recurrent Payment Profile ID, required for GetRecurringPaymentsProfileDetails operation. 
	 * @return string A URL which can be called with the @see call_paypal() method to perform the appropriate API operation.
	 */
	function get_payment_details_url( $action, $profile_or_transaction_id = '' ){

		if( empty( $this->token ) && isset( $_GET['token'] ) )
			$this->token = $_GET['token'];

		// Setup the Payment Details
		$api_request = $this->get_api_credentials_url();

		// Parameters to Request Recurring Payment Token
		if( 'SetExpressCheckout' == $action ) {

			$api_request .= '&METHOD=SetExpressCheckout'
						 .  '&RETURNURL=' . urlencode( $this->return_url )
						 .  '&CANCELURL=' . urlencode( $this->cancel_url );

			if( ! empty( $this->callback ) )
				$api_request  .=  '&CALLBACK=' . urlencode( $this->callback );

			if( ! empty( $this->business_name ) )
				$api_request  .=  '&BRANDNAME=' . urlencode( $this->business_name );

		} elseif ( 'GetExpressCheckoutDetails' == $action ) {

			$api_request .= '&METHOD=GetExpressCheckoutDetails'
						  . '&TOKEN=' . $this->token;

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
	 * )
	 */
	function start_subscription(){
		return $this->call_paypal( 'CreateRecurringPaymentsProfile' );
	}


	/**
	 * Makes a payment by calling the PayPal DoExpressCheckoutPayment API operation.
	 * 
	 * After an express checkout transaction has been created in request_checkout_token, this function can
	 * be called to complete the transaction.
	 * 
	 * This function returned the response from PayPal, which includes a profile ID (PROFILEID). You should
	 * save this profile ID to aid with changing and 
	 * 
	 * @return array(
	 * 	[TOKEN] => EC-XXXX
	 * 	[SUCCESSPAGEREDIRECTREQUESTED] => false
	 * 	[TIMESTAMP] => YYYY-MM-DDTHH:MM:SSZ
	 * 	[CORRELATIONID] => XXXX
	 * 	[ACK] => Success
	 * 	[VERSION] => 76.0
	 * 	[BUILD] => 2271164
	 * 	[INSURANCEOPTIONSELECTED] => false
	 * 	[SHIPPINGOPTIONISDEFAULT] => false
	 * 	[PAYMENTINFO_0_TRANSACTIONID] => XXXX
	 * 	[PAYMENTINFO_0_TRANSACTIONTYPE] => cart
	 * 	[PAYMENTINFO_0_PAYMENTTYPE] => instant
	 * 	[PAYMENTINFO_0_ORDERTIME] => YYYY-MM-DDTHH:MM:SSZ
	 * 	[PAYMENTINFO_0_AMT] => XX.00
	 * 	[PAYMENTINFO_0_FEEAMT] => 0.XX
	 * 	[PAYMENTINFO_0_TAXAMT] => 0.00
	 * 	[PAYMENTINFO_0_CURRENCYCODE] => USD
	 * 	[PAYMENTINFO_0_PAYMENTSTATUS] => Completed
	 * 	[PAYMENTINFO_0_PENDINGREASON] => None
	 * 	[PAYMENTINFO_0_REASONCODE] => None
	 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITY] => Ineligible
	 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE] => None
	 * 	[PAYMENTINFO_0_SECUREMERCHANTACCOUNTID] => XXXX
	 * 	[PAYMENTINFO_0_ERRORCODE] => 0
	 * 	[PAYMENTINFO_0_ACK] => Success
	 * )
	 */
	function process_payment(){
		return $this->call_paypal( 'DoExpressCheckoutPayment' );
	}


	/**
	 * Returns information about a subscription by calling the PayPal GetRecurringPaymentsProfileDetails API method.
	 * 
	 * @param $profile_id, string. The profile ID of the subscription for which the details should be looked up.
	 * @return array (
	 * 	[PROFILEID] => I-M0WXE0SLRVRY
	 * 	[STATUS] => Active
	 * 	[AUTOBILLOUTAMT] => AddToNextBilling
	 * 	[DESC] => Digital Goods Subscription. $10.00 sign-up fee then $2.00 per week for 4 weeks
	 * 	[MAXFAILEDPAYMENTS] => 0
	 * 	[SUBSCRIBERNAME] => Test User
	 * 	[PROFILESTARTDATE] => 2011-11-23T08:00:00Z
	 * 	[NEXTBILLINGDATE] => 2011-11-23T10:00:00Z
	 * 	[NUMCYCLESCOMPLETED] => 0
	 * 	[NUMCYCLESREMAINING] => 4
	 * 	[OUTSTANDINGBALANCE] => 0.00
	 * 	[FAILEDPAYMENTCOUNT] => 0
	 * 	[LASTPAYMENTDATE] => 2011-11-22T06:54:22Z
	 * 	[LASTPAYMENTAMT] => 10.00
	 * 	[TRIALAMTPAID] => 0.00
	 * 	[REGULARAMTPAID] => 0.00
	 * 	[AGGREGATEAMT] => 0.00
	 * 	[AGGREGATEOPTIONALAMT] => 10.00
	 * 	[FINALPAYMENTDUEDATE] => 2011-12-14T10:00:00Z
	 * 	[TIMESTAMP] => 2011-11-22T06:54:29Z
	 * 	[CORRELATIONID] => c0e3666366c96
	 * 	[ACK] => Success
	 * 	[VERSION] => 76.0
	 * 	[BUILD] => 2230381
	 * 	[BILLINGPERIOD] => Week
	 * 	[BILLINGFREQUENCY] => 1
	 * 	[TOTALBILLINGCYCLES] => 4
	 * 	[CURRENCYCODE] => USD
	 * 	[AMT] => 2.00
	 * 	[SHIPPINGAMT] => 0.00
	 * 	[TAXAMT] => 0.00
	 * 	[REGULARBILLINGPERIOD] => Week
	 * 	[REGULARBILLINGFREQUENCY] => 1
	 * 	[REGULARTOTALBILLINGCYCLES] => 4
	 * 	[REGULARCURRENCYCODE] => USD
	 * 	[REGULARAMT] => 2.00
	 * 	[REGULARSHIPPINGAMT] => 0.00
	 * 	[REGULARTAXAMT] => 0.00
	 * )
	 */
	function get_profile_details( $profile_id ){

		return $this->call_paypal( 'GetRecurringPaymentsProfileDetails', $profile_id );
	}


	/**
	 * Returns information about a purchase transaction by calling the PayPal GetTransactionDetails API method.
	 * 
	 * @param $profile_id, string. The profile ID of the subscription for which the details should be looked up.
	 * @return array (
	 * 	[RECEIVEREMAIL] => recipient@example.com
	 * 	[RECEIVERID] => XXXX
	 * 	[EMAIL] => payer@example.com
	 * 	[PAYERID] => XXXX
	 * 	[PAYERSTATUS] => verified
	 * 	[COUNTRYCODE] => US
	 * 	[ADDRESSOWNER] => PayPal
	 * 	[ADDRESSSTATUS] => None
	 * 	[SALESTAX] => 0.00
	 * 	[SUBJECT] => Example Digital Good Purchase
	 * 	[TIMESTAMP] => YYYY-MM-DDTHH:MM:SSZ
	 * 	[CORRELATIONID] => XXXX
	 * 	[ACK] => Success
	 * 	[VERSION] => 76.0
	 * 	[BUILD] => 2230381
	 * 	[FIRSTNAME] => Test
	 * 	[LASTNAME] => User
	 * 	[TRANSACTIONID] => XXXX
	 * 	[TRANSACTIONTYPE] => cart
	 * 	[PAYMENTTYPE] => instant
	 * 	[ORDERTIME] => YYYY-MM-DDTHH:MM:SSZ
	 * 	[AMT] => XX.00
	 * 	[FEEAMT] => 0.XX
	 * 	[TAXAMT] => 0.00
	 * 	[SHIPPINGAMT] => 0.00
	 * 	[HANDLINGAMT] => 0.00
	 * 	[CURRENCYCODE] => USD
	 * 	[PAYMENTSTATUS] => Completed
	 * 	[PENDINGREASON] => None
	 * 	[REASONCODE] => None
	 * 	[PROTECTIONELIGIBILITY] => Ineligible
	 * 	[PROTECTIONELIGIBILITYTYPE] => None
	 * 	[L_NAME0] => Digital Good Example
	 * 	[L_QTY0] => 1
	 * 	[L_SHIPPINGAMT0] => 0.00
	 * 	[L_HANDLINGAMT0] => 0.00
	 * 	[L_CURRENCYCODE0] => USD
	 * 	[L_AMT0] => XX.00
	 * )
	 */
	function get_transaction_details( $transaction_id ){

		return $this->call_paypal( 'GetTransactionDetails', $transaction_id );
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
		curl_setopt( $ch, CURLOPT_URL, PayPal_Digital_Goods_Configuration::endpoint() );
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
			die( $action . ' failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );

		curl_close( $ch );

		// An associative array is more usable than a parameter string
		parse_str( $response, $parsed_response );

		if( ( 0 == sizeof( $parsed_response ) ) || ! array_key_exists( 'ACK', $parsed_response ) )
			die( "Invalid HTTP Response for POST request($api_parameters) to " . PayPal_Digital_Goods_Configuration::endpoint() );

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
	 * 			'type' string. Type of element to output, either anchor or image/submit. Defaults to 'anchor'. 
	 */
	function get_buy_button( $args = array() ){

		$defaults = array(  'id'        => 'paypal-submit',
							'type'      => 'anchor',
							'href'      => PayPal_Digital_Goods_Configuration::checkout_url(),
							'alt'       => 'Submit',
							'get_token' => true
					);

		$args = array_merge( $defaults, $args );

		if( $args['type'] == 'anchor' ) {
			if( $args['get_token'] == true && empty( $this->token ) )
				$this->request_checkout_token();

			// Include the token in the href if the default href is not overridden
			if( $args['href'] == PayPal_Digital_Goods_Configuration::checkout_url() )
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
		return PayPal_Digital_Goods_Configuration::checkout_url() . $this->token;
	}

	/**
	 * Get the description for this subscription
	 */
	function get_description(){
		if( ! empty( $this->subscription ) )
			return $this->subscription->description;
		else
			return $this->purchase->description;
	}


	/**
	 * Returns a string representing the details of the subscription. 
	 * 
	 * For example "$10 sign-up fee then $20 per Month for 3 Months". 
	 */
	function get_subscription_string(){

		if( empty( $this->subscription ) )
			return 'No subscription set.';

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
	 * Returns a string representing the price for the purchase, including currency code. For example "$10". 
	 */
	function get_purchase_price() {
		if( ! empty( $this->subscription ) )
			return 'No purchase price, this is a subscription.';
		else
			return $this->get_currency_symbol() . $this->purchase->amount;
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
			$currency_code = PayPal_Digital_Goods_Configuration::currency();

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

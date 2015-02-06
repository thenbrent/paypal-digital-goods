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
	 * Stores the token once it has been acquired from PayPal
	 */
	protected $token;

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
	 * - notify_url, string, optional. The URL for receiving Instant Payment Notification (IPN) about this transaction. 
	 * - solution_type, string, optional. Type of checkout flow. It is one of Sole (default, buyer does not need to create a PayPal account to check out) or Mark (buyer must have a PayPal account to check out)
	 * - sandbox, boolean. Flag to indicate whether to use the PayPal Sandbox or live PayPal site. Default true - use sandbox.
	 * - business_name, string. A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages.
	 * 
	 * @param api_credentials, required, a name => value array containing your API username, password and signature.
	 * @param args, named parameters to customise the subscription and checkout process. See description for available parameters.
	 */
	public function __construct( $args = array() ){

		if( '' == PayPal_Digital_Goods_Configuration::username() || '' == PayPal_Digital_Goods_Configuration::password() || '' == PayPal_Digital_Goods_Configuration::signature() )
			throw new Exception( 'You must specify your PayPal API username, password & signature in the $api_credentials array.' );
		elseif( ( empty( $args['return_url'] ) && '' == PayPal_Digital_Goods_Configuration::username() ) || ( empty( $args['cancel_url'] ) && '' == PayPal_Digital_Goods_Configuration::cancel_url() ) )
			throw new Exception( 'You must specify a return_url & cancel_url.' );

		$defaults = array(
			'sandbox'       => true,
			'business_name' => '',
			'solution_type' => 'Sole',
			'return_url'    => PayPal_Digital_Goods_Configuration::return_url(),
			'cancel_url'    => PayPal_Digital_Goods_Configuration::cancel_url(),
			'notify_url'    => PayPal_Digital_Goods_Configuration::notify_url(),
			'locale_code'   => PayPal_Digital_Goods_Configuration::locale_code(), // Defaults to 'US'
		);

		$args = array_merge( $defaults, $args );

		$this->currency      = PayPal_Digital_Goods_Configuration::currency();
		$this->business_name = $args['business_name'];

		$this->return_url    = $args['return_url'];
		$this->cancel_url    = $args['cancel_url'];
		$this->notify_url    = $args['notify_url'];
		$this->solution_type = $args['solution_type'];
		$this->locale_code   = $args['locale_code'];
	}

	/**
	 * Map this object's API credentials to the PayPal NVP format for posting to the API.
	 * 
	 * Abstracted from @see get_payment_details_url for readability. 
	 */
	protected function get_api_credentials_url(){

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
	protected function get_payment_details_url( $action, $profile_or_transaction_id = '' ){

		if( empty( $this->token ) && isset( $_GET['token'] ) )
			$this->token = $_GET['token'];

		// Setup the Payment Details
		$api_request = $this->get_api_credentials_url();

		// Parameters to Request Recurring Payment Token
		if( 'SetExpressCheckout' == $action ) {

			$api_request .= '&METHOD=SetExpressCheckout'
						 .  '&RETURNURL=' . urlencode( $this->return_url )
						 .  '&SOLUTIONTYPE=' . urlencode( $this->solution_type )
						 .  '&LOCALECODE=' . urlencode( $this->locale_code )
						 .  '&CANCELURL=' . urlencode( $this->cancel_url );

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
	public function request_checkout_token(){

		$response = $this->call_paypal( 'SetExpressCheckout' );

		$this->token = $response['TOKEN'];

		return $response;
	}


	/**
	 * Calls the PayPal GetExpressCheckoutDetails methods and returns a more nicely formatted response
	 * 
	 * Called internally on return from set_express_checkout(). Can be called anytime to get details of 
	 * a transaction for which you have the Token.
	 */
	public function get_checkout_details(){
		return $this->call_paypal( 'GetExpressCheckoutDetails' );
	}


	/**
	 * Post to PayPal
	 * 
	 * Makes an API call using an NVP String and an Endpoint. Based on code available here: https://www.x.com/blogs/Nate/2011/01/07/digital-goods-with-express-checkout-in-php
	 * 
	 * @param action, string, required. The API operation to be performed, eg. GetExpressCheckoutDetails. The action is abstracted from you (the developer) by the appropriate helper function eg. GetExpressCheckoutDetails via get_checkout_details()
	 */
	protected function call_paypal( $action, $profile_id = '', $status = '', $args = array() ){

		// Use the one function for all PayPal API operations
		$api_parameters = $this->get_payment_details_url( $action, $profile_id, $status, $args );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, PayPal_Digital_Goods_Configuration::endpoint() );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

		// Make sure we use TLS as PayPal no longer supports SSLv3: https://ppmts.custhelp.com/app/answers/detail/a_id/1191/session/L2F2LzEvdGltZS8xNDE2MzUyMTgwL3NpZC8tVzFLaU03bQ%3D%3D
		curl_setopt( $ch, CURLOPT_SSLVERSION, 1 );

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
		if ( ! $response ) {
			throw new Exception( $action . ' failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
		}

		curl_close( $ch );

		// An associative array is more usable than a parameter string
		parse_str( $response, $parsed_response );

		if ( ( 0 == sizeof( $parsed_response ) ) || ! array_key_exists( 'ACK', $parsed_response ) ) {
			throw new Exception( "Invalid HTTP Response for POST request($api_parameters) to " . PayPal_Digital_Goods_Configuration::endpoint() );
		}

		if ( $parsed_response['ACK'] == 'Failure' ) {
			throw new Exception( "Calling PayPal with action $action has Failed: " . $parsed_response['L_LONGMESSAGE0'], $parsed_response['L_ERRORCODE0'] );
		}

		return $parsed_response;
	}


	/**
	 * Returns this instance of the class's token.
	 */
	public function token(){

		if ( empty( $this->token ) ) {
			$this->request_checkout_token();
		}

		return $this->token;
	}


	/**
	 * The Javascript to invoke the digital goods in context checkout process.
	 * 
	 * No need to call this function manually, required scripts are automatically printed with @see print_buy_buttion(). 
	 * If you do print this script manually, print it after the button in the DOM to ensure the 
	 * click event is properly hooked.
	 */
	public function get_script( $args = array() ){
		
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
	public function get_buy_button( $args = array() ){

		$defaults = array(
			'id'        => 'paypal-submit',
			'type'      => 'anchor',
			'href'      => PayPal_Digital_Goods_Configuration::checkout_url(),
			'alt'       => 'Submit',
			'get_token' => true,
			'locale'    => 'en_US'
		);

		$args = array_merge( $defaults, $args );

		if( $args['type'] == 'anchor' ) {
			if( $args['get_token'] == true && empty( $this->token ) )
				$this->request_checkout_token();

			// Include the token in the href if the default href is not overridden
			if( $args['href'] == PayPal_Digital_Goods_Configuration::checkout_url() )
				$args['href'] .= $this->token;

			$button = '<a href="' . $args['href'] . '" id="' . $args['id'] . '" alt="' . $args['alt'] . '"><img src="https://www.paypal.com/' . $args['locale'] . '/i/btn/btn_dg_pay_w_paypal.gif" border="0" /></a>';
		} else {
			$button = '<input type="image" id="' . $args['id'] . '" alt="' . $args['alt'] . '" src="https://www.paypal.com/' . $args['locale'] . '/i/btn/btn_dg_pay_w_paypal.gif">';
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
	public function print_buy_button( $args = array() ){
		echo $this->get_buy_button( $args );
		echo $this->get_script( $args );
	}


	/**
	 * Returns the Checkout URL including a token for this transaction. 
	 */
	public function get_checkout_url() {

		if ( empty( $this->token ) ) {
			$this->request_checkout_token();
		}

		// Include the token in the href if the default href is not overridden
		return PayPal_Digital_Goods_Configuration::checkout_url() . $this->token;
	}


	/**
	 * Get the symbol associated with a currency, optionally specified with '$currency_code' parameter. 
	 * 
	 * Will always return the symbol and can optionally also print the symbol.
	 * 
	 * @param $currency_code, string, optional, the ISO 4217 Code of the currency for which you want the Symbol, default the currency code of this object
	 * @param $echo bool, Optionally print the symbol before returning it.
	 **/
	public function get_currency_symbol( $currency_code = '', $echo = false ){

		if ( empty( $currency_code ) ) {
			$currency_code = PayPal_Digital_Goods_Configuration::currency();
		}

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
	 * Get the description of this payment
	 */
	abstract public function get_description();


	/**
	 * A unified API for processing a payment or subscription.
	 */
	abstract public function process();


	/**
	 * A unified API for getting the details of a purchase or subscription.
	 */
	abstract public function get_details( $transaction_or_profile_id );
}

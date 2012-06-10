<?php
/**
 *
 * Configuration registry
 * 
 * Inspired by the Braintree PHP Library: https://github.com/braintree/braintree_php
 * 
 * @package    PayPal
 * @subpackage Configuration
 * 
 * @license    GPLv3 see license.txt
 * @copyright  2011 Leonard's Ego Pty. Ltd.
 */

/**
 * Create a registry for config data of all PayPal Digital Goods Object.
 *
 * @package    PayPal
 * @subpackage Configuration
 */
class PayPal_Digital_Goods_Configuration {

	/**
	 * PayPay API version to use
	 * 
	 * @access private
	 */
	const API_VERSION =  '76.0';

	/**
	 * @var array array of config properties
	 *
	 * @access private
	 * @static
	 */
	private static $_cache = array(
		'environment'   => 'sandbox',
		'business_name' => '',
		'cancel_url'    => '',
		'return_url'    => '',
		'notify_url'    => '',
		'currency'      => 'USD',
		'username'      => '',
		'password'      => '',
		'signature'     => ''
		);

	/**
	 * @var Valid environments, used for validation
	 *
	 * @access private
	 * @static
	 */
	private static $_validEnvironments = array( 
		'live', 
		'development', 
		'sandbox' 
		);

	/**
	 * Reset the configuration to default settings
	 *
	 * @access public
	 * @static
	 */
	public static function reset() {
		self::$_cache = array (
			'environment'   => 'sandbox',
			'business_name' => '',
			'cancel_url'    => '',
			'return_url'    => '',
			'notify_url'    => '',
			'currency'      => 'USD',
			'username'      => '',
			'password'      => '',
			'signature'     => ''
			);
	}

	/**
	 * Performs sanity checks on config settings. Called when settings are set.
	 *
	 * @access protected
	 * @param string $key name of config setting
	 * @param string $value value to set
	 * @throws InvalidArgumentException
	 * @static
	 * @return boolean
	 */
	private static function validate( $key = null, $value = null ) {
		if ( empty( $key ) && empty( $value ) ) {
			throw new InvalidArgumentException('nothing to validate');
		}

		if ( $key === 'environment' && ! in_array( $value, self::$_validEnvironments ) ) {
			throw new Exception('"' . $value . '" is not a valid environment.');
		}

		if ( ! isset( self::$_cache[ $key ] ) ) {
			throw new Exception( $key . ' is not a valid configuration setting.' );
		}

		if ( empty( $value ) ) {
			throw new InvalidArgumentException( $key . ' cannot be empty.' );
		}

		return true;
	}


	/**
	 * Sets the value of a property specified with $key
	 * 
	 * @access private
	 * @static
	 * @param string $key The name of the property to set
	 * @param string $value The value to assign to the property
	 * @return none
	 */
	private static function set( $key, $value ) {
		// this method will raise an exception on invalid data
		self::validate( $key, $value );
		// set the value in the cache
		self::$_cache[$key] = $value;
	}


	/**
	 * Returns the value of a property specified with $key
	 * 
	 * @access private
	 * @static
	 * @param string $key The name of the property to set
	 * @return mixed returns value of the property or null if the property does not exist
	 */
	private static function get( $key ) {

		// Throw an exception if the value hasn't been set
		if ( isset( self::$_cache[$key] ) && ( empty( self::$_cache[$key] ) ) )
			throw new Exception( $key . ' needs to be set' );

		if ( array_key_exists( $key, self::$_cache ) )
			return self::$_cache[$key];

		// Return null by default to prevent __set from overloading
		return null;
	}


	/**
	 * Sets or returns the property after validation
	 * 
	 * @access public
	 * @static
	 * @param string $value pass a string to set, empty to get
	 * @return mixed returns value or true on set
	 */
	public static function environment( $value = null ) {
		return self::set_or_get( __FUNCTION__, $value );
	}

	public static function username( $value = null ) {
		return self::set_or_get( __FUNCTION__, $value );
	}

	public static function password( $value = null ) {
		return self::set_or_get( __FUNCTION__, $value );
	}

	public static function signature( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function currency( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function cancel_url( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function return_url( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function notify_url( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function business_name( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function version() {
		return self::API_VERSION;
	}


	private static function set_or_get( $name, $value = null ) {

		if ( ! empty( $value ) && is_array( $value ) )
			$value = $value[0];

		if ( ! empty( $value ) )
			self::set( $name, $value );
		else
			return self::get( $name );

		return true;
	}


	/**
	 * Returns the full API Enpoint URL based on the environment config value.
	 *
	 * @access public
	 * @static
	 * @return string PayPal endpoint URL
	 */
	public static function endpoint() {
		return ( self::$_cache['environment'] == 'sandbox' ) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
	}


	/**
	 * Returns the base PayPal Digital Goods incontext checkout URL based on the environment config value.
	 *
	 * @access public
	 * @static
	 * @return string PayPal in context payment checkout URL
	 */
	public static function checkout_url() {
		return ( self::$_cache['environment'] == 'sandbox' ) ? 'https://www.sandbox.paypal.com/incontext?token=' : 'https://www.paypal.com/incontext?token=';
	}

}
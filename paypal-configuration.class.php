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
		'signature'     => '',
		'incontext_url' => 'yes',
		'mobile_url'    => 'no',
		'locale_code'   => 'US', // A special form of the locale for PayPal's mixed handling (i.e. expects 2 character for some locales and 5 for others. Full list here: https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/)
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
	 * @var Valid environments, used for validation
	 *
	 * @access private
	 * @static
	 */
	private static $_validLocales = array(
		'AU', //  Australia
		'AT', //  Austria
		'BE', //  Belgium
		'BR', //  Brazil
		'CA', //  Canada
		'CH', //  Switzerland
		'CN', //  China
		'DE', //  Germany
		'ES', //  Spain
		'GB', //  United Kingdom
		'FR', //  France
		'IT', //  Italy
		'NL', //  Netherlands
		'PL', //  Poland
		'PT', //  Portugal
		'RU', //  Russia
		'US', //  United States
		'da_DK', //  Danish (for Denmark only)
		'he_IL', //  Hebrew (all)
		'id_ID', //  Indonesian (for Indonesia only)
		'ja_JP', //  Japanese (for Japan only)
		'no_NO', //  Norwegian (for Norway only)
		'pt_BR', //  Brazilian Portuguese (for Portugal and Brazil only)
		'ru_RU', //  Russian (for Lithuania, Latvia, and Ukraine only)
		'sv_SE', //  Swedish (for Sweden only)
		'th_TH', //  Thai (for Thailand only)
		'tr_TR', //  Turkish (for Turkey only)
		'zh_CN', //  Simplified Chinese (for China only)
		'zh_HK', //  Traditional Chinese (for Hong Kong only)
		'zh_TW', //  Traditional Chinese (for Taiwan only)
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
			'signature'     => '',
			'incontext_url' => 'yes',
			'mobile_url'    => 'no',
			'locale_code'   => 'US', // A special form of the locale for PayPal's mixed handling (i.e. expects 2 character for some locales and 5 for others. Full list here: https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/)
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
			throw new Exception( '"' . $value . '" is not a valid environment.' );
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
		if ( isset( self::$_cache[$key] ) && ( empty( self::$_cache[$key] ) ) ) {
			throw new Exception( $key . ' needs to be set' );
		}

		if ( array_key_exists( $key, self::$_cache ) ) {
			return self::$_cache[$key];
		}

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

	public static function incontext_url( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function mobile_url( $value = null ) {
		return self::set_or_get( __FUNCTION__ , $value );
	}

	public static function locale_code( $value = null ) {

		if ( null !== $value ) {
			$value = self::map_locale( $value );
		}

		return self::set_or_get( __FUNCTION__, $value );
	}

	public static function version() {
		return self::API_VERSION;
	}


	private static function set_or_get( $name, $value = null ) {

		if ( ! empty( $value ) && is_array( $value ) ) {
			$value = $value[0];
		}

		if ( ! empty( $value ) ) {
			self::set( $name, $value );
		} else {
			return self::get( $name );
		}

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
	 * Returns the base PayPal Digital Goods checkout URL based on the environment config value.
	 *
	 * @access public
	 * @static
	 * @return string PayPal in context/webscr payment checkout URL
	 */
	public static function checkout_url() {

		if ( self::$_cache['environment'] == 'sandbox' ) {
			if ( self::$_cache['mobile_url'] == 'yes' ) {
				$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&token=';
			} elseif( self::$_cache['incontext_url'] == 'yes' ) {
				$url = 'https://www.sandbox.paypal.com/incontext?token=';
			} else {
				$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
			}
		} else {
			if ( self::$_cache['mobile_url'] == 'yes' ) {
				$url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&token=';
			} elseif( self::$_cache['incontext_url'] == 'yes' ) {
				$url = 'https://www.paypal.com/incontext?token=';
			} else {
				$url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
			}
		}

		return $url;
	}

	/**
	 * For some locales, PayPal accept only a 2 letter country code. For others, they require a 5 character code.
	 * To account for this, we map full 5 digit locale's here to their two digit counter part.
	 *
	 * For the full list of locales, see the `LOCALECODE` section of the PayPal NVP API documentation:
	 * https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
	 *
	 * @access public
	 * @static
	 * @param $locale The 5 character locale.
	 * @return string A 2 character locale
	 */
	public static function map_locale( $locale ) {

		if ( in_array( $locale, self::$_validLocales ) ) {

			// Already a valid locale
			$paypal_friendly_locale = $locale;

		} else {

			// We need to map it based on the last two characters
			$shortened_locale = substr( $locale, -2 );

			if ( in_array( $shortened_locale, self::$_validLocales ) ) {

				// Already a valid locale
				$paypal_friendly_locale = $shortened_locale;

			} else {

				$paypal_friendly_locale = 'US';

			}

		}

		return $paypal_friendly_locale;
	}

}
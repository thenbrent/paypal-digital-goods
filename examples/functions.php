<?php
/**
 * A global variable for storing our PayPal Digital Goods Object
 */
global $paypal;

require_once( '../paypal-digital-goods.class.php' );
require_once( '../paypal-subscription.class.php' );
require_once( '../paypal-purchase.class.php' );


/**
 * Used to create a central function for getting credentials for both Subscription & Purchase objects
 */
function get_credentials() {
	// Create our PayPal Object
	$credentials = array(
	/*
		'username'  => 'your_api_username',  // For quick and once off sandbox tests, use 'digita_1308916325_biz_api1.gmail.com',
		'password'  => 'your_api_password',  // '1308916362',
		'signature' => 'your_api_signature', // 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
	*/
		'username'  => 'digita_1308916325_biz_api1.gmail.com',
		'password'  => '1308916362',
		'signature' => 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
	);

	if( $credentials['username'] == 'your_api_username' || $credentials['password'] == 'your_api_password' || $credentials['signature'] == 'your_api_signature' )
		exit( 'You must set your API credentials in ' . __FILE__ . ' for this example to work.' );

	return $credentials;
}

/**
 * Creates a PayPal Digital Goods Purchase Object
 */
function create_example_purchase() {

	$args = array(
		'return_url'    => get_script_uri( 'return.php?paypal=paid' ),
		'cancel_url'    => get_script_uri( 'return.php?paypal=cancel' ),
		'business_name' => 'Demo Store',
		'purchase'  => array(
			'item_name'   => 'Digital Good Example',
			'description' => 'Example Digital Good Purchase',
			// Price
			'amount'      => '12.00',
		)
	);

	//return new PayPal_Digital_Goods( get_credentials(), $args );
	return new PayPal_Purchase( $args['purchase'] );
}



/**
 * Creates a PayPal Subscription Object
 */
function create_example_subscription() {

	$args = array(
		'return_url'    => get_script_uri( 'return.php?paypal=paid' ),
		'cancel_url'    => get_script_uri( 'return.php?paypal=cancel' ),
		'business_name' => 'Demo Store',
		'subscription'  => array(
			'initial_amount'     => '10.00',
			'amount'             => '2.00',
			'period'             => 'Week',
			'frequency'          => '1', 
			'total_cycles'       => '4',
		)
	);

	return new PayPal_Digital_Goods( get_credentials(), $args );
}


function get_script_uri( $script = 'index.php' ){
	// IIS Fix
	if( empty( $_SERVER['REQUEST_URI'] ) )
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

	// Strip off query string
	$url = preg_replace( '/\?.*$/', '', $_SERVER['REQUEST_URI'] );
	//$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '/').'/';
	$url = 'http://'.$_SERVER['HTTP_HOST'].implode( '/', ( explode( '/', $_SERVER['REQUEST_URI'], -1 ) ) ) . '/';

	return $url . $script;
}
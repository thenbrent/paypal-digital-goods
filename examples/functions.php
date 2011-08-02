<?php
/**
 * A global variable for storing our PayPal Digital Goods Object
 */
global $paypal;

require_once('../paypal-digital-goods.class.php');

// Create our PayPal Object
$credentials = array(
	'username'  => 'digita_1308916325_biz_api1.gmail.com',
	'password'  => '1308916362',
	'signature' => 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
);

if( $credentials['username'] == 'your_api_username' || $credentials['password'] == 'your_api_password' || $credentials['signature'] == 'your_api_signature' )
	exit( 'You must set your API credentials in /examples/functions.php for this example to work.' );

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

$paypal = new PayPal_Digital_Goods( $credentials, $args );


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
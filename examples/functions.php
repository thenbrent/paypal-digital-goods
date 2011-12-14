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
function set_credentials() {

	/*
	PayPal_Digital_Goods_Configuration::username( 'your_api_username' );
	PayPal_Digital_Goods_Configuration::password( 'your_api_password' );
	PayPal_Digital_Goods_Configuration::signature( 'your_api_signature' );
	*/

	PayPal_Digital_Goods_Configuration::username( 'digita_1308916325_biz_api1.gmail.com' );
	PayPal_Digital_Goods_Configuration::password( '1308916362' );
	PayPal_Digital_Goods_Configuration::signature( 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW' );

	PayPal_Digital_Goods_Configuration::return_url( get_script_uri( 'return.php?paypal=paid' ) );
	PayPal_Digital_Goods_Configuration::cancel_url( get_script_uri( 'return.php?paypal=cancel' ) );
	PayPal_Digital_Goods_Configuration::business_name( 'Demo Store' );

	if( PayPal_Digital_Goods_Configuration::username() == 'your_api_username' || PayPal_Digital_Goods_Configuration::password() == 'your_api_password' || PayPal_Digital_Goods_Configuration::signature() == 'your_api_signature' )
		exit( 'You must set your API credentials in ' . __FILE__ . ' for this example to work.' );
}

/**
 * Creates a PayPal Digital Goods Purchase Object
 */
function create_example_purchase() {

	set_credentials();

	$purchase_details = array(
		'name'   => 'Digital Good Purchase Example',
		'description' => 'Example Digital Good Purchase',
		'amount'      => '12.00',
		'items'       => array(
			array( // First item
				'item_name'        => 'First item name',
				'item_description' => 'This is a description of the first item in the cart, it costs $9.00',
				'item_amount'      => '9.00',
	//			'item_tax'         => '1.00',
				'item_quantity'    => 1,
				'item_number'      => 'XF100',
			),
			array( // Second item
				'item_name'        => 'Second Item',
				'item_description' => 'This is a description of the SECOND item in the cart, it costs $1.00 but there are 3 of them.',
				'item_amount'      => '1.00',
	//			'item_tax'         => '1.00',
				'item_quantity'    => 3,
				'item_number'      => 'XJ100',
			),
		)
	);

	return new PayPal_Purchase( $purchase_details );
}



/**
 * Creates a PayPal Subscription Object
 */
function create_example_subscription() {

	set_credentials();

	$subscription_details = array(
		'initial_amount'     => '10.00',
		'amount'             => '2.00',
		'period'             => 'Week',
		'frequency'          => '1', 
		'total_cycles'       => '4',
	);

	return new PayPal_Subscription( $subscription_details );
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
<?php
/**
 * A global variable for storing our PayPal Digital Goods Object
 */
global $paypal;

require_once('../paypal-digital-goods.class.php');

$credentials = array(
	'username'  => 'your_api_username',  // For quick and dirty tests, use 'digita_1308916325_biz_api1.gmail.com',
	'password'  => 'your_api_password',  // '1308916362',
	'signature' => 'your_api_signature', // 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
);

if( $credentials['username'] == 'your_api_username' || $credentials['password'] == 'your_api_password' || $credentials['signature'] == 'your_api_signature' )
	exit( 'You must set your API credentials in /examples/functions.php for this example to work.' );

$urls = array(
	'return_url' => 'http://localhost/paypal-digital-goods/examples/return.php?return=paid',
	'cancel_url' => 'http://localhost/paypal-digital-goods/examples/return.php?return=cancel',
);

$paypal = new PayPal_Digital_Goods( $credentials, $urls );


<?php

include('../PayPal-Digital-Goods.class.php');

$credentials = array(
	'username'  => 'your_api_username',  // For quick and dirty tests, use 'digita_1308916325_biz_api1.gmail.com',
	'password'  => 'your_api_password',  // '1308916362',
	'signature' => 'your_api_signature', // 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
);

global $paypal;

$paypal = new PayPal_Digital_Goods( $credentials );


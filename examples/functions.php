<?php

include('../PayPal-Digital-Goods.class.php');

$credentials = array(
	'username'  => 'digita_1308916325_biz_api1.gmail.com',
	'password'  => '1308916362',
	'signature' => 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW'
);

global $paypal;

$paypal = new PayPal_Digital_Goods( $credentials );


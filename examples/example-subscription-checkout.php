<?php
/**
 * Example: Separate Checkout Page
 * 
 * This example shows how to use a buy button linking to a separate checkout page on your site. 
 * 
 * This principle advantage of this method is to only request a checkout token from PayPal when
 * a user has committed to buying (rather than every time the buy button is loaded). This method
 * massively improves page load time compared to the Simple Payment example.
 * 
 * The only difference between this example and the Simple Example is that the print_buy_button()
 * function is passed a array containing the href parameter of the custom checkout page & 'get_token'
 * flag is set to false. 
 */

require_once( 'functions.php' );

$paypal = create_example_subscription();

?>
<html>
<head>
	<title>PayPal - Digital Goods for Express Checkout Example</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div class="container">
		<h2>PayPal Recurring Payments Demo</h2>
		<p><b>Description:</b> <?php echo $paypal->get_description(); ?></p>
		<p><b>Subscription details:</b> <?php echo $paypal->get_subscription_string(); ?></p>
		<?php $paypal->print_buy_button( array( 'href' => 'checkout.php', 'get_token' => false ) ); ?>
	</div>
</body>
</html>

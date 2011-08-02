<?php
/**
 * Example: Simple Payment
 * 
 * This example shows the simplest method of accepting a payment.
 */

require_once( 'functions.php' );
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
		<p><b>Subscription details:</b> <?php echo $paypal->get_subscription_string(); ?>.</p>
		<?php $paypal->print_buy_button(); ?>
	</div>
</body>
</html>

<?php
/**
 * Example: Simple Payment
 * 
 * This example shows the simplest method of accepting a payment.
 */

?>
<html>
<head>
	<title>PayPal - Digital Goods for Express Checkout Examples</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div class="container">
		<h1>Thanks for Dropping by!</h1>
		<p>This library includes two examples: <a href="example-simple.php">Simple</a> and <a href="example-checkout.php">Separate Checkout</a>.</p>

		<h3>Simple Example</h3>
		<p>As the name suggests, this is the example demonstrates the simplest method for using the library to accept Digital Goods Subscriptions.</p>
		<p>Although simple to integrate, the payment page is slow to load as it requests a checkout token on page load.</p>
		<p><a href="example-simple.php">Try the Simple Example &raquo;</a></p>

		<h3>Separate Checkout</h3>
		<p>This example shows how to use a separate checkout page in the payment flow. Doing so greatly reduces the page load of your payment page by requesting a checkout token from PayPal only after a visitor has committed to payment.</p>
		<p><a href="example-checkout.php">Try the Separate Checkout Example &raquo;</a></p>
	</div>
</body>
</html>

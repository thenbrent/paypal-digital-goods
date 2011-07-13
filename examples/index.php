<?php
// Create our PayPal Object
require_once('functions.php');
?>
<html>
<head>
	<title>PayPal - Digital Goods with Express Checkout</title>
</head>
<body>
	<p><?php echo $paypal->get_description(); ?></p>
	<p>Subscriptions are only <?php echo $paypal->get_price_details(); ?>.</p>
	<?php $paypal->print_buy_button(); // That's it, the class will take care of requesting the token, print the scripts etc. ?>
</body>
</html>

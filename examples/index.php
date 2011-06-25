<?php
include('functions.php');
?>
<html>
<head>
	<title>PayPal - Digital Goods with Express Checkout</title>
</head>
<body>
	<p>At this point you would have had the user add everything to the cart, then you would do the SetEC Call and display this button</p>
	<?php $paypal->print_buy_button(); // That's it, the class will take care of requesting the token, print the scripts etc. ?>
</body>
</html>

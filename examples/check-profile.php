<?php
require_once('functions.php');
$response = $paypal->get_subscription_details();

error_log('Subscription Details = ' . print_r( $response, true ) );

?>
<html>
<head>
	<title>PayPal Profile Checked</title>
</head>
<body>
	<p>Check your error log, narf.</p>
</body>
</html>

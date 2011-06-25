<?php
include('functions.php');
$response = $paypal->get_subscription_details();

error_log('get profile response = ' . print_r( $response, true ) );

?>
<html>
<head>
	<title>PayPal Profile Checked</title>
</head>
<body>
	<p>Check your error log, narf.</p>
</body>
</html>

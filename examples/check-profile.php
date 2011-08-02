<?php
require_once( 'functions.php' );

if( ! isset( $_GET['profile_id'] ) )
	die('Check Profile Requires a profile_id specified in the URL ($_GET)');

?>
<html>
<head>
	<title>PayPal Recurring Payment Profile</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div class="container" style="width: 550px;">
		<h2>PayPal Subscription Details</h2>
		<pre>
$paypal->get_subscription_details( $_GET['profile_id'] ) ); = 
<? print_r( $paypal->get_subscription_details( $_GET['profile_id'] ) ); ?>
		</pre>
		<p><a href="<?php echo get_script_uri(); ?>" target="_top">Return to Examples Overview &raquo;</a></p>
	<div>
</body>
</html>

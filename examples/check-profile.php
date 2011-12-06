<?php
require_once( 'functions.php' );

if( ! isset( $_GET['profile_id'] ) && ! isset( $_GET['transaction_id'] ) )
	die('Check Profile Requires a profile_id or transaction_id specified in the URL ($_GET)');

?>
<html>
<head>
	<title>PayPal Recurring Payment Profile</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div class="container" style="width: 550px;">

	<?php if( isset( $_GET['profile_id'] ) ) : ?>
		<?php $paypal = create_example_subscription(); ?>

		<h2>PayPal Subscription Details</h2>
		<pre>
$paypal->get_profile_details( $_GET['profile_id'] ) ) = 
<? print_r( $paypal->get_profile_details( $_GET['profile_id'] ) ); ?>
		</pre>

	<?php else : ?>
		<?php $paypal = create_example_purchase(); ?>

		<h2>PayPal Transaction Details</h2>
		<pre>
$paypal->get_transaction_details( $_GET['transaction_id'] ) ); = 
<? print_r( $paypal->get_transaction_details( $_GET['transaction_id'] ) ); ?>
		</pre>

	<?php endif; ?>

		<p><a href="<?php echo get_script_uri(); ?>" target="_top">Return to Examples Overview &raquo;</a></p>
	<div>
</body>
</html>

<?php
require_once( 'functions.php' );

?>
<html>
<head>
	<title>PayPal Recurring Payments Return Page</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div class="container">
		<h2>PayPal Recurring Payments Demo</h2>

	<?php // Returning from PayPal & Payment Cancelled ?>
	<?php if( isset( $_GET['paypal'] ) && $_GET['paypal'] == 'cancel' ) : ?>

		<script>if (window!=top) {top.location.replace(document.location);}</script>
		<p>Your subscription has been cancelled. <a href="<?php echo get_script_uri(); ?>" target="_top">Try again? &raquo;</a></p>

	<?php // Returning from PayPal & Payment Authorised ?>
	<?php elseif( isset( $_GET['paypal'] ) && $_GET['paypal'] == 'paid' ) :

		// Process the payment or start the Subscription
		if( isset( $_GET['PayerID'] ) ) {
			$paypal = create_example_purchase();
			$response = $paypal->process_payment();
		} else { 
			$paypal = create_example_subscription();
			$response = $paypal->start_subscription();
		}
		?>

		<h3>Payment Complete!</h3>
		<?php if( isset( $_GET['PayerID'] ) ) { ?>
			<p>Your Transaction ID is <?php echo $response['PAYMENTINFO_0_TRANSACTIONID']; ?></p>
			<p>You can use this Transaction ID to see the details of your subscription like so:</p>
			<pre><code>get_transaction_details( $response['PAYMENTINFO_0_TRANSACTIONID'] );</code></pre>
			<p><a href="<?php echo get_script_uri( 'check-profile.php?transaction_id=' . urlencode($response['PAYMENTINFO_0_TRANSACTIONID']) ) ?>" target="_top">View Transaction Details &raquo;</a></p>
		<?php } else { ?>
			<p>Your Payment Profile ID is <?php echo $response['PROFILEID']; ?></p>
			<p>You can use this Profile ID to see the details of your subscription like so:</p>
			<pre><code>get_profile_details('<?php echo $response['PROFILEID']; ?>');</code></pre>
			<p><a href="<?php echo get_script_uri( 'check-profile.php?profile_id=' . urlencode($response['PROFILEID']) ) ?>" target="_top">Check Profile &raquo;</a></p>
		<?php } ?>

	<?php endif; ?>
	</div>
</body>
</html>

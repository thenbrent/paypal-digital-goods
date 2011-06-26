<?php
require_once('functions.php');
$response = $paypal->get_checkout_details();
$subscription = $paypal->get_subscription_details( 'class-properties' );
?>
<html>
<head>
	<title>Confirm your payment</title>
</head>
<body>
	<h2>Confirm Your Subscription</h2>
	<p>Total: <?php echo $subscription->amount . ' per ' . $subscription->period; ?></p>
	<form action='' method='post'>
		<input type='submit' name='confirm' value='Confirm' />
	</form>
	<?php 
	if( isset( $_POST['confirm'] ) && $_POST['confirm'] == "Confirm" ) {
		$doresponse = $paypal->start_subscription();

		// Check Response
		if($doresponse['ACK'] == "Success" || $doresponse['ACK'] == "SuccessWithWarning") {
			echo "<p>Your Payment Has Completed! click <a href='check-profile.php' target='_parent'>HERE</a> to check your profile.</p>";
			//place in logic to make digital goods available
		} else if($doresponse['ACK'] == "Failure" || $doresponse['ACK'] == "FailureWithWarning") {
			echo "<p>The API Call Failed: " . urldecode($doresponse['L_LONGMESSAGE0']) . "</p>";
		}
	}
	?>
</body>
</html>


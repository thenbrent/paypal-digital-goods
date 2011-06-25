# PayPal Digital Goods with Express Checkout Recurring Payments API PHP Library

*Take a deep breath*. PayPal Digital Goods with Express Checkout Recurring Payments API, *(take another breath)* is a wonderful payment gateway with horrible documentation. 

This class fills in the blanks in documentation while simulatenously offering a super easy library for using the Recurring Payments API.

## Comparison

For a quick comparison, of using this class vs. using PHP, let's compare one of the few examples of using the Digital Goods with Express Checkout found in this [blog post](https://www.x.com/blogs/Nate/2011/01/07/digital-goods-with-express-checkout-in-php). 

The index.php for this example is like so:

```
<?php
include('functions.php');

$APIUSERNAME  = "xxxxx_api1.paypal.com";
$APIPASSWORD  = "xxxx";
$APISIGNATURE = "xxxxx";
$ENDPOINT     = "https://api-3t.sandbox.paypal.com/nvp";
$VERSION      = "65.1"; //must be >= 65.1
$REDIRECTURL  = "https://www.sandbox.paypal.com/incontext?token=";

//Build the Credential String:
$cred_str = "USER=" . $APIUSERNAME . "&PWD=" . $APIPASSWORD . "&SIGNATURE=" . $APISIGNATURE . "&VERSION=" . $VERSION;
//For Testing this is hardcoded. You would want to set these variable values dynamically
$nvp_str  = "&METHOD=SetExpressCheckout" 
	. "&RETURNURL=https://www.yoursite.com/return.php" //set your Return URL here
. "&CANCELURL=https://www.yoursite.com/return.php" //set your Cancel URL here
. "&PAYMENTREQUEST_0_CURRENCYCODE=USD"
	. "&PAYMENTREQUEST_0_AMT=300"
	. "&PAYMENTREQUEST_0_ITEMAMT=200"
	. "&PAYMENTREQUEST_0_TAXAMT=100"
	. "&PAYMENTREQUEST_0_DESC=Movies"
	. "&PAYMENTREQUEST_0_PAYMENTACTION=Sale"
	. "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital"
	. "&L_PAYMENTREQUEST_0_ITEMCATEGORY1=Digital"
	. "&L_PAYMENTREQUEST_0_NAME0=Kitty Antics"
	. "&L_PAYMENTREQUEST_0_NAME1=All About Cats"
	. "&L_PAYMENTREQUEST_0_NUMBER0=101"
	. "&L_PAYMENTREQUEST_0_NUMBER1=102"
	. "&L_PAYMENTREQUEST_0_QTY0=1"
	. "&L_PAYMENTREQUEST_0_QTY1=1"
	. "&L_PAYMENTREQUEST_0_TAXAMT0=50"
	. "&L_PAYMENTREQUEST_0_TAXAMT1=50"
	. "&L_PAYMENTREQUEST_0_AMT0=100"
	. "&L_PAYMENTREQUEST_0_AMT1=100"
	. "&L_PAYMENTREQUEST_0_DESC0=Download"
	. "&L_PAYMENTREQUEST_0_DESC1=Download";

//combine the two strings and make the API Call
$req_str = $cred_str . $nvp_str;
$response = PPHttpPost($ENDPOINT, $req_str);
//check Response
if($response['ACK'] == "Success" || $response['ACK'] == "SuccessWithWarning")
{
	//setup redirect URL
	$redirect_url = $REDIRECTURL . urldecode($response['TOKEN']);
}
else if($response['ACK'] == "Failure" || $response['ACK'] == "FailureWithWarning")
{
	echo "The API Call Failed";
	print_r($response);
}
?>


<html>
<head>
	<title>PayPal - Digital Goods with Express Checkout</title>
	<script src ='https://www.paypalobjects.com/js/external/dg.js' type='text/javascript'></script>
</head>
<body>
	<p>At this point you would have had the user add everything to the cart, then you would do the SetEC Call and display this button</p>
	<?php echo "<a href=" . $redirect_url . " id='submitBtn'><img src='https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif' border='0' /></a>"; ?>
	<script>
	var dg = new PAYPAL.apps.DGFlow({
		// the HTML ID of the form submit button which calls setEC
		trigger: "submitBtn"
	});
	</script>
</body>
</html>
```

The equivalent with this library is:

```
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
```

## Examples

To test the library for yourself, create a sandbox business account and request it be set as a Digital Goods account in the [x.com forums here](https://www.x.com/thread/49892). 

Login to this account and get your API credentials from the [API Access](https://www.sandbox.paypal.com/us/cgi-bin/webscr?cmd=_profile-api-access) page.

Copy the API Credentials into /examples/functions.php

Load /index.php in your browser.

## Limitations:

The class currently only supports recurring payments as this is all that I required. 

Future versions may include 

* Purchase of digital goods as well as recurring payments.
* Method to create up to 10 different profiles in one transaction. 

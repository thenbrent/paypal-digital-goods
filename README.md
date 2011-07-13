# Recurring Payments with PayPal Digital Goods for Express Checkout PHP Library

Recurring Payments with PayPal Digital Goods for Express Checkout is a wonderful payment gateway with disjointed documentation (and an unfortunately verbose name). 

This class fills in the blanks in documentation while simultaneously offering a library for using the Recurring Payments API.


## Why Use a Class

Using a distinct class for interacting with PayPal provides all the advantages you've come to love of Object-Oriented programming:

* **Abstraction**: the class abstracts the complex details of the PayPal NVP API and provides simple function calls to perform common operations;
* **Encapsulation**: by using the class to interact with PayPal, you can update your application to use the most recent API version without changing your application's code (just update the library).


## Why Use _this_ Class

* **Eliminates drudgery**: operations are performed only once, regardless of the number of subscriptions you create. For example, requesting a token, printing JavaScript and printing the purchase button is all done with one function `print_buy_button()`;
* **Human friendly variable names**: To reduce request size, PayPal’s API uses shortened parameter names. As instances of the class are created server-side, it can afford to use longer, more human friendly names. For example, `'initial_amount'` refers to PayPal’s `INITAMT` parameter;
* **Abstracts PayPalisms**: PayPal loves verbiage, I don’t. The class attempts to simplify some of PayPal terms to more colloquial terms. For example, the `get_subscription_details()` function replaces PayPal's `GetRecurringPaymentsProfileDetails` method;
* **Don't Repeat Code**: we only need to create one instance of the class for each subscription in our application. The credential & NVP API strings for every request are then automatically built using simple function calls.


## Comparison

For a quick comparison, of using this class vs. using PHP, let's compare one of the few examples of using the Digital Goods with Express Checkout found in this [blog post](https://www.x.com/blogs/Nate/2011/01/07/digital-goods-with-express-checkout-in-php). 

The index.php for this example is like so:

```php
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

```php
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


# Usage

To test the library for yourself, copy the entire folder into `http://example.com/paypal-digital-goods/`.

Create a sandbox seller account and request it be set as a Digital Goods account in this [x.com forums topic](https://www.x.com/thread/49892).

Login to this account and get your API credentials from the [API Access](https://www.sandbox.paypal.com/us/cgi-bin/webscr?cmd=_profile-api-access) page.

Copy the API Credentials into `/examples/functions.php`.

Load `index.php` in your browser.


### Quick Example

The minimum code required for creating an instance of the class is to pass it your PayPal API Credentials and the return & cancel URLs.

```php
require_once('paypal-digital-goods.class.php');

$credentials = array(
	'username'  => 'digita_1308916325_biz_api1.gmail.com',
	'password'  => '1308916362',
	'signature' => 'AFnwAcqRkyW0yPYgkjqTkIGqPbSfAyVFbnFAjXCRltVZFzlJyi2.HbxW',
);

$args = array(
	'return_url' = 'http://example.com/paypal-digital-goods/examples/return.php?return=paid',
	'cancel_url' = 'http://example.com/paypal-digital-goods/examples/return.php?return=cancel',
);

$paypal = new PayPal_Digital_Goods( $credentials, $args );
```

This will create a $25/month subscription in the PayPal Sandbox.


### Customising your Subscription

The `$args` parameter accepted in the class constructor is an associative array of `name => value` pairs that you can use to customise your subscription.

#### Subscription Price

To change the subscription to be $49/Month with a $79 sign-up fee, the parameters in the `'subscription'` array must be set like so. 

```php
$args['subscription'] = array(
	'amount' = 49.00,
	'initial_amount' = 79.00,
	'average_amount' = 49.00 // default is 25.00 as our monthly subscription value is higher than that, we must set this
);
```

#### Subscription Duration & Frequency

To change the subscription to be billed every 2 weeks for a 6 week period, the parameters in the `'subscription'` array must be set like so. 

```php
$args['subscription'] = array(
	'period' = 'Week',
	'frequency' = 2,
	'total_cycles' = 6
);
```

#### Trial Period

You can add a trial period to your subscription. 

To add a 30 day free trial period the subscription, the parameters in the `'subscription'` array must be set like so. 

```php
$args['subscription'] = array(
	'trial_amount' = 0,
	'trial_period' = 'Day',
	'trial_frequency' = 30,
	'trial_total_cycles' = 1
);
```

#### Adding a Description

To set the description a new subscriber sees when confirming the subscription with PayPal, pass a description in the `'subscription'` parameter.

`$args['subscription'] = array( 'description' => 'Hacker Monthly magazine online subscription.' );`

#### From the Sandbox to the Live Environment

By default, the class uses the PayPal Sandbox. Switching from the Sandbox to the live PayPal site is easy, set the `'sandbox'` boolean flag in the `$args` array to false.

`$args['sandbox'] = false;`


## Glossary

If you are fluent in the verbose PayPal lexicon, you will find some of the terms in this library differ to those used in PayPal's documentation. 

This class translates the following PayPalisms to the human vernacular.

* *Recurring Payment Profile* is referred to as a subscription
* *Digital Goods for Express Checkout* is referred to as checkout
* *Payment Flow* is referred to as checkout process


## Supported PayPal Operations

Supported PayPal API Operations:

* `SetExpressCheckout` via `request_checkout_token()`
* `GetExpressCheckoutDetails` via `get_checkout_details()`
* `GetRecurringPaymentsProfileDetails` via `get_subscription_details()`
* `CreateRecurringPaymentsProfile` via `start_subscription()`


## Limitations

The class currently only supports recurring payments as this is all I needed.

The class also only support creating one recurring payments profile, where as PayPal docs outline that it is possible to create up to 10 different profiles in one transaction. 


## Roadmap

Future versions may include 

* Purchase of digital goods as well as recurring payments.
* Method to create up to 10 different profiles in one transaction. 
* Parsed responses from API calls with urldecoded values and keys translated into more human friendly terms


## Pull Requests

Patches are welcome, especially those that implement functionality to overcome the current limitations. 

To submit a patch:

1. Fork the project.
1. Make your feature addition or bug fix.
1. Comment your functions with a brief explanation of purpose. Comment inline only to [explain *why* your code works. Let your code explain *how*](http://www.codinghorror.com/blog/2006/12/code-tells-you-how-comments-tell-you-why.html).
1. Add examples for any new functionality.
1. Send me a pull request. Bonus points for topic branches.

Your syntax should conform to the [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards). 

Remember, the class is written to be friendly to humans, so place special emphasis on readability. It is more important than cleverness and brevity.

>Programs must be written for people to read, and only incidentally for machines to execute.
>&#8212; [Structure and Interpretation of Computer Programs](http://mitpress.mit.edu/sicp/full-text/book/book-Z-H-7.html)

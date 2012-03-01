# PayPal Digital Goods for Express Checkout PHP Library

PayPal's Digital Goods for Express Checkout service is a wonderful payment solution with disjointed documentation and an unfortunately verbose name.

This class connects the dots in documentation and offers a library for using the [PayPal Digital Goods API](https://merchant.paypal.com/cgi-bin/marketingweb?cmd=_render-content&content_ID=merchant/digital_goods) that is friendly to humans (or human programmers at least).

The library can be used to create both one off purchases and recurring payments (subscriptions).

To see the library in action, visit my [PayPal Digital Goods Demo](http://paypal.brentshepherd.com/).


## Why Use a Class

Using a distinct class for interacting with PayPal provides all the advantages you've come to love of Object-Oriented programming:

* **Abstraction**: the complexity of the PayPal NVP API is hidden behind simple function calls for common operations.
* **Encapsulation**: update your application to use the most recent version of the API without changing your application's code.


# Examples

Like to learn by example?

Check out the [PayPal Digital Goods PHP Examples](https://github.com/thenbrent/paypal-digital-goods-php-examples/) repository.


# Live Demo

Want to see a live example of Recurring Payments with PayPal's Digital Goods for Express Checkout? 

Take a look at my [PayPal Digital Goods Demo](http://paypal.brentshepherd.com/).


# Usage

### Configuration

Before creating a payment, you need to register a few settings with the `PayPal_Digital_Goods_Configuration` class. 

The minimum configuration settings required are your PayPal API Credentials, a return URI and cancel URI.

```php
<?php 
require_once( 'paypal-digital-goods.class.php' );

PayPal_Digital_Goods_Configuration::username( 'PAYPAL_API_USERNAME' );
PayPal_Digital_Goods_Configuration::password( 'PAYPAL_API_PASSWORD' );
PayPal_Digital_Goods_Configuration::signature( 'PAYPAL_API_SIGNATURE' );

PayPal_Digital_Goods_Configuration::return_url( 'http://example.com/return.php?paypal=paid' );
PayPal_Digital_Goods_Configuration::cancel_url( 'http://example.com/return.php?paypal=cancel' );
?>
```

Once the PayPal library is configured, you can create a Purchase or Subscription object, or a few of each if you prefer. 


### Creating a Purchase Object

The `PayPal_Purchase` class is used to create digital goods purchases. The class's constructor takes a multi-dimensional array of named parameters to customise the purchase to your needs. The individual parameters are explained in detail in the constructor's comments. 

Below is a quick example which creates a purchase of two different goods with a total transaction value of $12.00. 

```php
<?php 
require_once( 'paypal-purchase.class.php' );

$purchase_details = array(
	'name'        => 'Digital Good Purchase Example',
	'description' => 'Example Digital Good Purchase',
	'amount'      => '12.00',
	'items'       => array(
		array( // First item
			'item_name'        => 'First item name',
			'item_description' => 'This is a description of the first item in the cart, it costs $9.00',
			'item_amount'      => '9.00',
			'item_tax'         => '1.00',
			'item_quantity'    => 1,
			'item_number'      => 'XF100',
		),
		array( // Second item
			'item_name'        => 'Second Item',
			'item_description' => 'This is a description of the SECOND item in the cart, it costs $1.00 but there are 3 of them.',
			'item_amount'      => '1.00',
			'item_tax'         => '0.50',
			'item_quantity'    => 3,
			'item_number'      => 'XJ100',
		),
	)
);

$paypal_purchase = new PayPal_Purchase( $purchase_details );
?>
```

These are the purchase details used in the [PayPal Digital Goods PHP Examples](https://github.com/thenbrent/paypal-digital-goods-php-examples/) repository.


### Creating a Subscription

The `PayPal_Subscription` class is used to create recurring payments for digital goods. Much like the `PayPal_Purchase` class, the `PayPal_Subscription` class's constructor takes a multi-dimensional array of named parameters to customise the subscription to your needs. The individual parameters are explained in detail in the constructor's comments. 

Below is a quick example which creates a $2/week subscription for 4 weeks with a $10.00 sign-up fee. 

```php
<?php 
$subscription_details = array(
	'description'        => 'Example Subscription: $10 sign-up fee then $2/week for the next four weeks.',
	'initial_amount'     => '10.00',
	'amount'             => '2.00',
	'period'             => 'Week',
	'frequency'          => '1', 
	'total_cycles'       => '4',
);

$paypal_subscription = new PayPal_Subscription( $subscription_details );
?>
```

Note, it is highly recommend you include the subscription amounts and details in the `'description'` parameter as PayPal does not display the subscription details on the confirmation page.


### Adding the PayPal Buy Button

Once you have created a purchase or subscription object, the rest is simple. 

Add the following line to your checkout or payment page. It will add all necessary scripts and set-up the transaction with PayPal. 

```php
<?php $paypal_purchase->print_buy_button(); ?>
```

### Processing a Payment and Starting a Subscription

When a user returns from PayPal, processing their payment or starting their subscription is also a one line operation. 


#### Process a Payment 

To process a payment once a user has authorized the purchase with PayPal, call the `process_payment()` operation on your `PayPal_Purchase` object.

```php
<?php $paypal_purchase->process_payment(); ?>
```


#### Start a Subscription 

To start a subscription after a user has authorized the recurring payment plan with PayPal, call the `start_subscription()` operation on your `PayPal_Subscription` object.

```php
<?php $paypal_subscription->start_subscription(); ?>
```


### From Sandbox to a Live Environment

By default, the library uses the PayPal Sandbox. Switching from the Sandbox to the live PayPal site is easy, set the `environment` configuration setting to `live`.

```php
<?php PayPal_Digital_Goods_Configuration::environment( 'live' ); ?>
```


## Supported PayPal Operations

Supported PayPal API Operations:

* `SetExpressCheckout` via `request_checkout_token()`
* `GetExpressCheckoutDetails` via `get_checkout_details()`
* `DoExpressCheckoutPayment` via `process_payment()`
* `GetTransactionDetails` via `get_transaction_details( $transaction_id )`
* `CreateRecurringPaymentsProfile` via `start_subscription()`
* `GetRecurringPaymentsProfileDetails` via `get_profile_details( $profile_id )`


## Using the Library as a Git Submodule

To use the library as a [submodule](http://book.git-scm.com/5_submodules.html) in your application's main git repository, use the following command:

	# Add the PayPal Library as a submodule
	git submodule add git://github.com/thenbrent/paypal-digital-goods.git paypal-digital-goods

When cloning your application's Git repo, be sure to specify the --recursive option to include the contents of the PayPal submodule.

	# Clone my application and all submodules 
	git clone --recursive git://github.com/username/app-name.git


## Further Reading

PayPal has hidden some excellent articles within the x.commerce dev zone, including:

* [An Overview of PayPal for Digital Goods](https://www.x.com/devzone/articles/overview-paypal-digital-goods)
* [How to Implement PayPal Digital Goods](https://www.x.com/devzone/articles/how-implement-paypal-digital-goods)


## Pull Requests

Patches are welcome

To submit a patch:

1. Fork the project.
1. Make your feature addition or bug fix.
1. Add examples for any new functionality.
1. Send me a pull request. Bonus points for topic branches.

The class is written to be friendly to humans, so place special emphasis on readability of your code. It is more important than cleverness and brevity. Your syntax should conform to the [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards). Provide a brief explanation of each functions purpose in header comments, and comment inline only to [explain *why* your code works. Let your code explain *how*](http://www.codinghorror.com/blog/2006/12/code-tells-you-how-comments-tell-you-why.html).

>Programs must be written for people to read, and only incidentally for machines to execute.
>&#8212; [Structure and Interpretation of Computer Programs](http://mitpress.mit.edu/sicp/full-text/book/book-Z-H-7.html)

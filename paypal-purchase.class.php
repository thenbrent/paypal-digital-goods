<?php
/**
 * A PayPal Purchase object for the Digital Goods with Express Checkout API. 
 * 
 * @package    PayPal
 * @subpackage Purchase
 * 
 * @license    GPLv3 see license.txt
 * @copyright  2011 Leonard's Ego Pty. Ltd.
 */
class PayPal_Purchase extends PayPal_Digital_Goods {

		/**
		 * Details of the items in this particular purchase.
		 */
		private $purchase;


		/**
		 * Creates a PayPal Digital Goods Object configured according to the parameters in the args associative array. 
		 * 
		 * Available $args parameters:
		 * - purchase_details, array, details of the purchase.
		 * 		- description, string, Brief description of the subscription as shown to the subscriber in their PayPal account.
		 * 		Subscription price parameters (default: $25 per month):
		 * 		- amount, double, default 25.00. The price per period for the subscription.
		 * 		- initial_amount, double, default 0. An optional sign up fee.
		 * 		- average_amount, double, default 25.00. The average transaction amount, PayPal default is $25, only set higher if your monthly subscription value is higher
		 * 		Subscription temporal parameters (default: bill once per month forever):
		 * 		- start_date, date, default 24 hours in the future. The start date for the profile, defaults to one day in the future. Must take the form YYYY-MM-DDTHH:MM:SS and can not be in the past.
		 * 		- period, Day|Week|Month|Semimonth, default Month. The unit of interval between billing.
		 * 		- frequency, integer, default 1. How regularly to charge the amount. When period is Month, a frequency value of 1 would charge every month while a frequency value of 12 charges once every year.
		 * 		- total_cycles, integer, default perpetuity. The total number of occasions the subscriber should be charged. When period is month and frequency is 1, 12 would continue the subscription for one year. The default value 0 will continue the payment for perpetuity.
		 * 		Subscription trail period parameters (default: no trial period):
		 * 		- trial_amount, double, default 0. The price per trial period.
		 * 		- trial_period, Day|Week|Month|Semimonth, default Month. The unit of interval between trial period billing.
		 * 		- trial_frequency, integer, default 0. How regularly to charge the amount.
		 * 		- trial_total_cycles, integer, default perpetuity. 
		 * 
		 * @todo Allow purchase details to include more than one item. 
		 * 
		 * @param args, named parameters to customise the subscription and checkout process. See description for available parameters.
		 */
		function __construct( $purchase_details = array() ){

			/**
			 * @todo this merge needs to be made recursive to account for 
			 */
			$purchase_defaults = array( 
					'item_name'   => 'Digital Good',
					'description' => 'Digital Good Purchase',
					// Price
					'amount'      => '5.00',
					'tax'         => '0.00',
			);

			$purchase_details = array_merge( $purchase_defaults, $purchase_details );

			$this->purchase = (object)$purchase_details;

			parent::__construct();
		}


		/**
		 * Makes a payment by calling the PayPal DoExpressCheckoutPayment API operation.
		 * 
		 * After an express checkout transaction has been created in request_checkout_token, this function can
		 * be called to complete the transaction.
		 * 
		 * This function returned the response from PayPal, which includes a profile ID (PROFILEID). You should
		 * save this profile ID to aid with changing and 
		 * 
		 * @return array(
		 * 	[TOKEN] => EC-XXXX
		 * 	[SUCCESSPAGEREDIRECTREQUESTED] => false
		 * 	[TIMESTAMP] => YYYY-MM-DDTHH:MM:SSZ
		 * 	[CORRELATIONID] => XXXX
		 * 	[ACK] => Success
		 * 	[VERSION] => 76.0
		 * 	[BUILD] => 2271164
		 * 	[INSURANCEOPTIONSELECTED] => false
		 * 	[SHIPPINGOPTIONISDEFAULT] => false
		 * 	[PAYMENTINFO_0_TRANSACTIONID] => XXXX
		 * 	[PAYMENTINFO_0_TRANSACTIONTYPE] => cart
		 * 	[PAYMENTINFO_0_PAYMENTTYPE] => instant
		 * 	[PAYMENTINFO_0_ORDERTIME] => YYYY-MM-DDTHH:MM:SSZ
		 * 	[PAYMENTINFO_0_AMT] => XX.00
		 * 	[PAYMENTINFO_0_FEEAMT] => 0.XX
		 * 	[PAYMENTINFO_0_TAXAMT] => 0.00
		 * 	[PAYMENTINFO_0_CURRENCYCODE] => USD
		 * 	[PAYMENTINFO_0_PAYMENTSTATUS] => Completed
		 * 	[PAYMENTINFO_0_PENDINGREASON] => None
		 * 	[PAYMENTINFO_0_REASONCODE] => None
		 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITY] => Ineligible
		 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE] => None
		 * 	[PAYMENTINFO_0_SECUREMERCHANTACCOUNTID] => XXXX
		 * 	[PAYMENTINFO_0_ERRORCODE] => 0
		 * 	[PAYMENTINFO_0_ACK] => Success
		 * )
		 */
		function process_payment(){
			return $this->call_paypal( 'DoExpressCheckoutPayment' );
		}


		/**
		 * Returns information about a purchase transaction by calling the PayPal GetTransactionDetails API method.
		 * 
		 * @param $profile_id, string. The profile ID of the subscription for which the details should be looked up.
		 * @return array (
		 * 	[RECEIVEREMAIL] => recipient@example.com
		 * 	[RECEIVERID] => XXXX
		 * 	[EMAIL] => payer@example.com
		 * 	[PAYERID] => XXXX
		 * 	[PAYERSTATUS] => verified
		 * 	[COUNTRYCODE] => US
		 * 	[ADDRESSOWNER] => PayPal
		 * 	[ADDRESSSTATUS] => None
		 * 	[SALESTAX] => 0.00
		 * 	[SUBJECT] => Example Digital Good Purchase
		 * 	[TIMESTAMP] => YYYY-MM-DDTHH:MM:SSZ
		 * 	[CORRELATIONID] => XXXX
		 * 	[ACK] => Success
		 * 	[VERSION] => 76.0
		 * 	[BUILD] => 2230381
		 * 	[FIRSTNAME] => Test
		 * 	[LASTNAME] => User
		 * 	[TRANSACTIONID] => XXXX
		 * 	[TRANSACTIONTYPE] => cart
		 * 	[PAYMENTTYPE] => instant
		 * 	[ORDERTIME] => YYYY-MM-DDTHH:MM:SSZ
		 * 	[AMT] => XX.00
		 * 	[FEEAMT] => 0.XX
		 * 	[TAXAMT] => 0.00
		 * 	[SHIPPINGAMT] => 0.00
		 * 	[HANDLINGAMT] => 0.00
		 * 	[CURRENCYCODE] => USD
		 * 	[PAYMENTSTATUS] => Completed
		 * 	[PENDINGREASON] => None
		 * 	[REASONCODE] => None
		 * 	[PROTECTIONELIGIBILITY] => Ineligible
		 * 	[PROTECTIONELIGIBILITYTYPE] => None
		 * 	[L_NAME0] => Digital Good Example
		 * 	[L_QTY0] => 1
		 * 	[L_SHIPPINGAMT0] => 0.00
		 * 	[L_HANDLINGAMT0] => 0.00
		 * 	[L_CURRENCYCODE0] => USD
		 * 	[L_AMT0] => XX.00
		 * )
		 */
		function get_transaction_details( $transaction_id ){
			return $this->call_paypal( 'GetTransactionDetails', $transaction_id );
		}


		/**
		 * Overloads the base class's get_payment_details_url to map the subscription details 
		 * to the PayPal NVP format for posting to PayPal.
		 * 
		 * The function first calls the Parent's get_payment_details_url, which takes care of 
		 * common NVP fields, like API credentials. It then appends subscription related fields.
		 * 
		 * Note: The SetExpressCheckout action URL is partially built in the base class as it contains
		 * NVP fields common to both Purchases & Subscriptions. 
		 * 
		 * @param $action, string. The PayPal NVP API action to create the URL for. One of SetExpressCheckout, DoExpressCheckoutPayment or GetTransactionDetails.
		 * @param $transaction_id, (optional) string. A PayPal Transaction ID, required for the GetTransactionDetails operation. 
		 * @return string A URI which can be use in the @see call_paypal() method to perform the appropriate API operation.
		 */
		function get_payment_details_url( $action, $transaction_id = '' ){

			// Setup the Payment Details
			$api_request = parent::get_payment_details_url( $action );

			// Parameters to Request Recurring Payment Token
			if( 'SetExpressCheckout' == $action ) {

				$api_request  .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $this->currency ) // A 3-character currency code (default is USD).
							  .  '&PAYMENTREQUEST_0_PAYMENTACTION=Sale' // From PayPal: When implementing digital goods, this field is required and must be set to Sale.

							  // Payment details
							  .  '&PAYMENTREQUEST_0_AMT=' . $this->purchase->amount // (Required) Total cost of the transaction to the buyer. If tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. 
							  .  '&PAYMENTREQUEST_0_ITEMAMT=' . $this->purchase->amount // (Required) Sum of cost of all items in this order.
							  .  '&PAYMENTREQUEST_0_DESC=' . $this->purchase->description // (Optional) Description of items the buyer is purchasing. 

							  // Item details
							  .  '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital' // Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital.
							  .  '&L_PAYMENTREQUEST_0_NAME0=' . $this->purchase->item_name // Item name. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed.
							  .  '&L_PAYMENTREQUEST_0_DESC0=' . $this->purchase->description // (Optional) Item description. Character length and limitations: 127 single-byte characters
							  .  '&L_PAYMENTREQUEST_0_AMT0=' . $this->purchase->amount // Cost of item. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed.
							  .  '&L_PAYMENTREQUEST_0_QTY0=1'; // (Required) Item quantity. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed. For digital goods (L_PAYMENTREQUEST_n_ITEMCATEGORYm=Digital), this field is required.

				// Maybe add tax
				if( ! empty( $this->purchase->tax_amount ) ) {
					$api_request  .= '&PAYMENTREQUEST_0_TAXAMT=' . $this->purchase->tax_amount // (Optional) Sum of tax for all items in this order. 
								  .  '&L_PAYMENTREQUEST_0_TAXAMT0=' . $this->purchase->tax_amount; // (Optional) Item sales tax. Character length and limitations: Value is a positive number which cannot exceed $10,000 USD in any currency. It includes no currency symbol. It must have 2 decimal places, the decimal separator must be a period (.), and the optional thousands separator must be a comma (,).
				}

				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&L_PAYMENTREQUEST_0_NUMBER0=' . $this->purchase->item_number; // (Optional) Item number. Character length and limitations: 127 single-byte characters

				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&PAYMENTREQUEST_0_INVNUM=' . $this->purchase->invoice_number; // (Optional) Your own invoice or tracking number.

			} elseif ( 'DoExpressCheckoutPayment' == $action ) {

				$api_request    .= '&METHOD=DoExpressCheckoutPayment' 
								.  '&TOKEN=' . $this->token
								.  '&PAYERID=' . $_GET['PayerID']
	//							.  '&RETURNFMFDETAILS=1'

								// Payment details
								. '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $this->currency ) // A 3-character currency code (default is USD).
								. '&PAYMENTREQUEST_0_PAYMENTACTION=Sale' // From PayPal: When implementing digital goods, this field is required and must be set to Sale.

								// Payment details
								.  '&PAYMENTREQUEST_0_AMT=' . $this->purchase->amount // (Required) Total cost of the transaction to the buyer. If tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. 
								.  '&PAYMENTREQUEST_0_ITEMAMT=' . $this->purchase->amount // (Required) Sum of cost of all items in this order.
								.  '&PAYMENTREQUEST_0_DESC=' . $this->purchase->description // (Optional) Description of items the buyer is purchasing. 

								// Item details
								.  '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital' // Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital.
								.  '&L_PAYMENTREQUEST_0_NAME0=' . $this->purchase->item_name // Item name. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed.
								.  '&L_PAYMENTREQUEST_0_DESC0=' . $this->purchase->description // (Optional) Item description. Character length and limitations: 127 single-byte characters
								.  '&L_PAYMENTREQUEST_0_AMT0=' . $this->purchase->amount // Cost of item. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed.
								.  '&L_PAYMENTREQUEST_0_QTY0=1'; // (Required) Item quantity. This field is required when L_PAYMENTREQUEST_n_ITEMCATEGORYm is passed. For digital goods (L_PAYMENTREQUEST_n_ITEMCATEGORYm=Digital), this field is required.

				// Maybe add tax
				if( ! empty( $this->purchase->tax_amount ) )
					$api_request  .= '&PAYMENTREQUEST_0_TAXAMT=' . $this->purchase->tax_amount // (Optional) Sum of tax for all items in this order. 
								  .  '&L_PAYMENTREQUEST_0_TAXAMT0=' . $this->purchase->tax_amount; // (Optional) Item sales tax. Character length and limitations: Value is a positive number which cannot exceed $10,000 USD in any currency. It includes no currency symbol. It must have 2 decimal places, the decimal separator must be a period (.), and the optional thousands separator must be a comma (,).

				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&L_PAYMENTREQUEST_0_NUMBER0=' . $this->purchase->item_number; // (Optional) Item number. Character length and limitations: 127 single-byte characters

				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&PAYMENTREQUEST_0_INVNUM=' . $this->purchase->invoice_number; // (Optional) Your own invoice or tracking number.

			} elseif ( 'GetTransactionDetails' == $action ) {

				$api_request .= '&METHOD=GetTransactionDetails'
							  . '&TRANSACTIONID=' . urlencode( $transaction_id );

			}

			return $api_request;
		}


		/**
		 * Returns a string representing the price for the purchase, including currency code. For example "$10". 
		 */
		function get_purchase_price() {
			return $this->get_currency_symbol() . $this->purchase->amount;
		}


		/**
		 * Get the description for this subscription
		 */
		public function get_description(){
			return $this->purchase->description;
		}


}

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

require_once( 'paypal-digital-goods.class.php' );

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
		 * 		- description, string, (Optional) Description of items the buyer is purchasing.
		 * 		Transaction Totals:
		 * 		- amount, double, required. Total cost of the transaction to the buyer.
		 * 		- tax, double, default 0.00. The sum of tax for all items in this order. 
		 * 		- invoice_number, string, (Optional) Your own invoice or tracking number. Can be up to 127 single-byte alphanumeric characters.
		 * 		Item details:
		 * 		- items array, An array of arrays for each item in this transaction.
		 * 			- item_name, string, Item name. 
		 * 			- item_description, string, (Optional) Item description.
		 * 			- item_amount, string, (Optional) Cost of this individual item.
		 * 			- item_quantity, string, default 1. Number of this specific item.
		 * 			- item_tax, string, (Optional) Sales tax of this individual item.
		 * 			- item_number, string, (Optional) Your own invoice or tracking number. Can be up to 127 single-byte alphanumeric characters.
		 *		Miscellaneous
		 * 		- custom (Optional) A free-form field for your own use. 
		 * 
		 * @todo Allow purchase details to include more than one item. 
		 * 
		 * @param args, named parameters to customise the subscription and checkout process. See description for available parameters.
		 */
		public function __construct( $purchase_details = array() ){

			$purchase_defaults = array( 
					'name'           => 'Digital Good',
					'description'    => '',
					// Price
					'amount'         => '5.00',
					'tax_amount'     => '0.00',
					'invoice_number' => '',
					'number'         => '',
					'items'          => array(),
					'custom'         => '',
			);

			$purchase_details = array_merge( $purchase_defaults, $purchase_details );

			// Make it super simple to create a single item transaction
			if( empty( $purchase_details['items'] ) )
				$purchase_details['items'] = array( array( 
					'item_name'        => $purchase_details['name'],
					'item_description' => $purchase_details['description'],
					'item_amount'      => $purchase_details['amount'],
					'item_tax'         => $purchase_details['tax_amount'],
					'item_quantity'    => 1,
					'item_number'      => $purchase_details['number']
				) );

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
		 * 	[PAYMENTINFO_0_PAYMENTSTATUS] => Completed | None | Denied | Expired | Failed | In-Progress | Partially-Refunded | Pending | Refunded | Reversed | Processed | Voided
		 * 	[PAYMENTINFO_0_PENDINGREASON] => None
		 * 	[PAYMENTINFO_0_REASONCODE] => None
		 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITY] => Ineligible
		 * 	[PAYMENTINFO_0_PROTECTIONELIGIBILITYTYPE] => None
		 * 	[PAYMENTINFO_0_SECUREMERCHANTACCOUNTID] => XXXX
		 * 	[PAYMENTINFO_0_ERRORCODE] => 0
		 * 	[PAYMENTINFO_0_ACK] => Success
		 * )
		 */
		public function process_payment(){
			return $this->call_paypal( 'DoExpressCheckoutPayment' );
		}


		/**
		 * A wrapper for the process_payment function to implement the unified Digital Goods API.
		 */
		public function process(){
			return $this->process_payment();
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
		public function get_transaction_details( $transaction_id ){
			return $this->call_paypal( 'GetTransactionDetails', $transaction_id );
		}


		/**
		 * A wrapper for the get_transaction_details function implementing the unified API.
		 * 
		 * Accepts either a get_transaction_details ID or a $response array as returned
		 * from the SetExpressCheckout call.
		 */
		public function get_details( $transaction ){

			if ( is_array( $transaction ) && isset( $transaction['PAYMENTINFO_0_TRANSACTIONID'] ) )
				$transaction = $transaction['PAYMENTINFO_0_TRANSACTIONID'];

			return $this->get_transaction_details( $transaction );
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
		protected function get_payment_details_url( $action, $transaction_id = '' ){

			// Setup the Payment Details
			$api_request = parent::get_payment_details_url( $action, $transaction_id );

			// Parameters to Request Recurring Payment Token
			if( 'SetExpressCheckout' == $action ) {

				$api_request  .= '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $this->currency ) // A 3-character currency code (default is USD).
							  .  '&PAYMENTREQUEST_0_PAYMENTACTION=Sale' // From PayPal: When implementing digital goods, this field is required and must be set to Sale.

							  // Payment details
							  .  '&PAYMENTREQUEST_0_AMT=' . $this->purchase->amount // (Required) Total cost of the transaction to the buyer. If tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. 
							  .  '&PAYMENTREQUEST_0_ITEMAMT=' . ( $this->purchase->amount - $this->purchase->tax_amount ) // (Required) Sum of cost of all items in this order.
							  .  '&PAYMENTREQUEST_0_TAXAMT=' . $this->purchase->tax_amount // (Optional) Sum of tax for all items in this order
							  .  '&PAYMENTREQUEST_0_DESC=' . urlencode( $this->purchase->description ); // (Optional) Description of items the buyer is purchasing. 

				// Maybe add an Invoice number
				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&PAYMENTREQUEST_0_INVNUM=' . $this->purchase->invoice_number; // (Optional) Your own invoice or tracking number.

				// Maybe add an IPN URL
				if( ! empty( $this->notify_url ) )
					$api_request  .=  '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode( $this->notify_url );

				// Maybe add a custom field
				if( ! empty( $this->purchase->custom ) )
					$api_request  .=  '&PAYMENTREQUEST_0_CUSTOM=' . urlencode( $this->purchase->custom );

				// Item details
				$item_count = 0;
				foreach( $this->purchase->items as $item ) {
					
					$api_request  .= '&L_PAYMENTREQUEST_0_ITEMCATEGORY'.$item_count.'=Digital'
								  .  '&L_PAYMENTREQUEST_0_NAME'.$item_count.'=' .  urlencode( $item['item_name'] )
								  .  '&L_PAYMENTREQUEST_0_AMT'.$item_count.'=' . $item['item_amount']
								  .  '&L_PAYMENTREQUEST_0_QTY'.$item_count.'=' . $item['item_quantity'];

					if( ! empty( $item['item_description'] ) )
						$api_request  .= '&L_PAYMENTREQUEST_0_DESC'.$item_count.'=' . urlencode( $item['item_description'] );

					if( ! empty( $item['item_tax'] ) )
						$api_request  .= '&L_PAYMENTREQUEST_0_TAXAMT'.$item_count.'=' . $item['item_tax'];

					if( ! empty( $item['item_number'] ) )
						$api_request .= '&L_PAYMENTREQUEST_0_NUMBER'.$item_count.'=' . $item['item_number'];

					$item_count++;
				}

			} elseif ( 'DoExpressCheckoutPayment' == $action ) {

				$api_request    .= '&METHOD=DoExpressCheckoutPayment' 
								.  '&TOKEN=' . $this->token
								.  '&PAYERID=' . $_GET['PayerID']

							// Payment details
							 .  '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $this->currency )
							 .  '&PAYMENTREQUEST_0_PAYMENTACTION=Sale' // From PayPal: When implementing digital goods, this field is required and must be set to Sale.

							// Payment details
							 .  '&PAYMENTREQUEST_0_AMT=' . $this->purchase->amount // (Required) Total cost of the transaction to the buyer. If tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. 
							 .  '&PAYMENTREQUEST_0_ITEMAMT=' . ( $this->purchase->amount - $this->purchase->tax_amount ) // (Required) Sum of cost of all items in this order.
							 .  '&PAYMENTREQUEST_0_TAXAMT=' . $this->purchase->tax_amount // (Optional) Sum of tax for all items in this order
							 .  '&PAYMENTREQUEST_0_DESC=' . urlencode( $this->purchase->description ); // (Optional) Description of items the buyer is purchasing. 

				// Maybe add an Invoice number
				if( ! empty( $this->purchase->invoice_number ) )
					$api_request  .= '&PAYMENTREQUEST_0_INVNUM=' . $this->purchase->invoice_number; // (Optional) Your own invoice or tracking number.

				// Maybe add an IPN URL
				if( ! empty( $this->notify_url ) )
					$api_request  .=  '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode( $this->notify_url );

				// Maybe add a custom field
				if( ! empty( $this->purchase->custom ) )
					$api_request  .=  '&PAYMENTREQUEST_0_CUSTOM=' . urlencode( $this->purchase->custom );

				// Item details
				$item_count = 0;
				foreach( $this->purchase->items as $item ) {
					$api_request  .= '&L_PAYMENTREQUEST_0_ITEMCATEGORY'.$item_count.'=Digital'
								  .  '&L_PAYMENTREQUEST_0_NAME'.$item_count.'=' .  urlencode( $item['item_name'] )
								  .  '&L_PAYMENTREQUEST_0_AMT'.$item_count.'=' . $item['item_amount']
								  .  '&L_PAYMENTREQUEST_0_QTY'.$item_count.'=' . $item['item_quantity'];

					if( ! empty( $item['item_description'] ) )
						$api_request  .= '&L_PAYMENTREQUEST_0_DESC'.$item_count.'=' . urlencode( $item['item_description'] );

					if( ! empty( $item['item_tax'] ) )
						$api_request  .= '&L_PAYMENTREQUEST_0_TAXAMT'.$item_count.'=' . $item['item_tax'];

					if( ! empty( $item['item_number'] ) )
						$api_request .= '&L_PAYMENTREQUEST_0_NUMBER'.$item_count.'=' . $item['item_number'];

					$item_count++;
				}

			} elseif ( 'GetTransactionDetails' == $action ) {

				$api_request .= '&METHOD=GetTransactionDetails'
							  . '&TRANSACTIONID=' . urlencode( $transaction_id );

			}

			return $api_request;
		}


		/**
		 * Returns a string representing the price for the purchase, including currency code. For example "$10". 
		 */
		public function get_purchase_price() {
			return $this->get_currency_symbol() . $this->purchase->amount;
		}


		/**
		 * Get the description for this subscription
		 */
		public function get_description(){
			return $this->purchase->description;
		}


}

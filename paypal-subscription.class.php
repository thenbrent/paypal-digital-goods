<?php
/**
* A PayPal Recurring Payments object for the Digital Goods with Express Checkout API. 
 * 
 * @package    PayPal
 * @subpackage Subscription
 * 
 * @license    GPLv3 see license.txt
 * @copyright  2011 Leonard's Ego Pty. Ltd.
 */

require_once( 'paypal-digital-goods.class.php' );

class PayPal_Subscription extends PayPal_Digital_Goods{

		/**
		 * Details of this particular recurring payment profile, including price, period & frequency.
		 */
		private $subscription;


		/**
		 * Creates a PayPal Digital Goods Subscription Object configured according to the parameters in the $subscription_details associative array. 
		 * 
		 * Available $subscription_details parameters:
		 * - subscription_details, array, details of the recurring payment profile to be created.
		 * 		- description, string, Brief description of the subscription as shown to the subscriber in their PayPal account.
		 * 		Subscription price parameters (default: $25 per month):
		 * 		- amount, double, default 25.00. The price per period for the subscription, including tax.
		 * 		- initial_amount, double, default 0. An optional sign up fee.
		 * 		- average_amount, double, default 25.00. The average transaction amount, PayPal default is $25, only set higher if your monthly subscription value is higher
		 * 		Subscription temporal parameters (default: bill once per month forever):
		 * 		- start_date, date, default 24 hours in the future. The start date for the profile, defaults to one day in the future. Must take the form YYYY-MM-DDTHH:MM:SS and can not be in the past.
		 * 		- period, Day|Week|Month|Semimonth, default Month. The unit of interval between billing.
		 * 		- frequency, integer, default 1. How regularly to charge the amount. When period is Month, a frequency value of 1 would charge every month while a frequency value of 12 charges once every year.
		 * 		- total_cycles, integer, default perpetuity. The total number of occasions the subscriber should be charged. When period is month and frequency is 1, 12 would continue the subscription for one year. The default value 0 will continue the payment for perpetuity.
		 * 		Subscription trial period parameters (default: no trial period):
		 * 		- trial_amount, double, default 0. The price per trial period.
		 * 		- trial_period, Day|Week|Month|Semimonth, default Month. The unit of interval between trial period billing.
		 * 		- trial_frequency, integer, default 0. How regularly to charge the amount.
		 * 		- trial_total_cycles, integer, default perpetuity. 
		 * 
		 * @param $subscription_details, named parameters to customise the subscription prices, periods and frequencies.
		 */
		public function __construct( $subscription_details = array() ){

			$subscription_defaults = array(
				'description'         => 'Digital Goods Subscription',
				'invoice_number'      => '',
				'max_failed_payments' => '',
				// Price
				'amount'              => '25.00',
				'initial_amount'      => '0.00',
				'average_amount'      => '25',
				'tax_amount'          => '0.00',
				// Temporal Details
				'start_date'          => date( 'Y-m-d\TH:i:s', time() + ( 24 * 60 * 60 ) ),
				'period'              => 'Month',
				'frequency'           => '1',
				'total_cycles'        => '0',
				// Trial Period
				'trial_amount'        => '0.00',
				'trial_period'        => 'Month',
				'trial_frequency'     => '0',
				'trial_total_cycles'  => '0',
				// Miscellaneous
				'add_to_next_bill'    => true,
			);

			$subscription_details = array_merge( $subscription_defaults, $subscription_details );

			$this->subscription = (object)$subscription_details;

			parent::__construct();
		}


		/**
		 * Creates a subscription by calling the PayPal CreateRecurringPaymentsProfile API operation.
		 * 
		 * After a billing agreement has been created in request_checkout_token, this function can
		 * be called to start the subscription.
		 * 
		 * This function returned the response from PayPal, which includes a profile ID (PROFILEID). You should
		 * save this profile ID to aid with changing and 
		 *
		 * IMPORTANT: PayPal does not create the recurring payments profile until you receive a success response 
		 * from the CreateRecurringPaymentsProfile call.
		 * 
		 * Return: array(
		 * 	[PROFILEID] => I%2d537HXDRJCH67
		 * 	[PROFILESTATUS] => PendingProfile
		 * 	[TIMESTAMP] => 2011%2d06%2d25T09%3a18%3a19Z
		 * 	[CORRELATIONID] => beba80198304d
		 * 	[ACK] => Success
		 * 	[VERSION] => URL Encoded API Version, eg 
		 * 	[BUILD] => 1907759
		 * )
		 */
		public function start_subscription(){
			return $this->call_paypal( 'CreateRecurringPaymentsProfile' );
		}


		/**
		 * A wrapper for the start_subscription function to implement the unified Digital Goods API.
		 */
		public function process(){
			return $this->start_subscription();
		}


		/**
		 * Returns information about a subscription by calling the PayPal GetRecurringPaymentsProfileDetails API method.
		 * 
		 * @param $profile_id, string. The profile ID of the subscription for which the details should be looked up.
		 * @return array (
		 * 	[PROFILEID] => I-M0WXE0SLRVRY
		 * 	[STATUS] => Active
		 * 	[AUTOBILLOUTAMT] => AddToNextBilling
		 * 	[DESC] => Digital Goods Subscription. $10.00 sign-up fee then $2.00 per week for 4 weeks
		 * 	[MAXFAILEDPAYMENTS] => 0
		 * 	[SUBSCRIBERNAME] => Test User
		 * 	[PROFILESTARTDATE] => 2011-11-23T08:00:00Z
		 * 	[NEXTBILLINGDATE] => 2011-11-23T10:00:00Z
		 * 	[NUMCYCLESCOMPLETED] => 0
		 * 	[NUMCYCLESREMAINING] => 4
		 * 	[OUTSTANDINGBALANCE] => 0.00
		 * 	[FAILEDPAYMENTCOUNT] => 0
		 * 	[LASTPAYMENTDATE] => 2011-11-22T06:54:22Z
		 * 	[LASTPAYMENTAMT] => 10.00
		 * 	[TRIALAMTPAID] => 0.00
		 * 	[REGULARAMTPAID] => 0.00
		 * 	[AGGREGATEAMT] => 0.00
		 * 	[AGGREGATEOPTIONALAMT] => 10.00
		 * 	[FINALPAYMENTDUEDATE] => 2011-12-14T10:00:00Z
		 * 	[TIMESTAMP] => 2011-11-22T06:54:29Z
		 * 	[CORRELATIONID] => c0e3666366c96
		 * 	[ACK] => Success
		 * 	[VERSION] => 76.0
		 * 	[BUILD] => 2230381
		 * 	[BILLINGPERIOD] => Week
		 * 	[BILLINGFREQUENCY] => 1
		 * 	[TOTALBILLINGCYCLES] => 4
		 * 	[CURRENCYCODE] => USD
		 * 	[AMT] => 2.00
		 * 	[SHIPPINGAMT] => 0.00
		 * 	[TAXAMT] => 0.00
		 * 	[REGULARBILLINGPERIOD] => Week
		 * 	[REGULARBILLINGFREQUENCY] => 1
		 * 	[REGULARTOTALBILLINGCYCLES] => 4
		 * 	[REGULARCURRENCYCODE] => USD
		 * 	[REGULARAMT] => 2.00
		 * 	[REGULARSHIPPINGAMT] => 0.00
		 * 	[REGULARTAXAMT] => 0.00
		 * )
		 */
		public function get_profile_details( $profile_id ){
			return $this->call_paypal( 'GetRecurringPaymentsProfileDetails', $profile_id );
		}


		/**
		 * A wrapper for the get_profile_details function to provide a unified API.
		 * 
		 * Accepts either a profile ID or a $response array as returned from the 
		 * SetExpressCheckout call. 
		 */
		public function get_details( $profile ){

			if ( is_array( $profile ) && isset( $profile['PROFILEID'] ) ) {
				$profile = $profile['PROFILEID'];
			}

			return $this->get_profile_details( $profile );
		}


		/**
		 * A wrapper for the get_profile_details function to provide a unified API.
		 * 
		 * Accepts either a profile ID or a $response array as returned from the 
		 * SetExpressCheckout call. 
		 */
		public function manage_subscription_status( $profile_id, $status, $note = '' ){

			return $this->call_paypal( 'ManageRecurringPaymentsProfileStatus', $profile_id, $status, array( 'note' => $note ) );
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
		 * @param $action, string. The PayPal NVP API action to create the URL for. One of SetExpressCheckout, CreateRecurringPaymentsProfile or GetRecurringPaymentsProfileDetails.
		 * @param $profile_id, (optional) string. A PayPal Recurrent Payment Profile ID, required for GetRecurringPaymentsProfileDetails operation. 
		 * @return string A URL which can be called with the @see call_paypal() method to perform the appropriate API operation.
		 */
		protected function get_payment_details_url( $action, $profile_id = '', $status = '', $args = array() ){

			// Setup the Payment Details
			$api_request = parent::get_payment_details_url( $action, $profile_id );

			// Parameters to Request Recurring Payment Token
			if( 'SetExpressCheckout' == $action ) {

				$api_request .= '&L_BILLINGTYPE0=RecurringPayments'
							  . '&L_BILLINGAGREEMENTDESCRIPTION0=' . urlencode( $this->subscription->description )
							  . '&CURRENCYCODE=' . urlencode( $this->currency )
							  . '&MAXAMT=' . urlencode( $this->subscription->average_amount );

			} elseif ( 'CreateRecurringPaymentsProfile' == $action ) {

				$api_request  .=  '&METHOD=CreateRecurringPaymentsProfile' 
								. '&TOKEN=' . urlencode( $this->token )

								// Details
								. '&DESC=' . urlencode( $this->subscription->description )
								. '&CURRENCYCODE=' . urlencode( $this->currency )
								. '&PROFILESTARTDATE=' . urlencode( $this->subscription->start_date )

								// Price
								. '&AMT=' . urlencode( $this->subscription->amount )
								. '&INITAMT=' . urlencode( $this->subscription->initial_amount )
								. '&TAXAMT=' . urlencode( $this->subscription->tax_amount )

								// Period
								. '&BILLINGPERIOD=' . urlencode( $this->subscription->period )
								. '&BILLINGFREQUENCY=' . urlencode( $this->subscription->frequency )
								. '&TOTALBILLINGCYCLES=' . urlencode( $this->subscription->total_cycles )

								// Specify Digital Good Payment
								. "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital" // Best rates for Digital Goods sale
								. "&L_PAYMENTREQUEST_0_NAME0=" . urlencode( $this->subscription->description )
								. "&L_PAYMENTREQUEST_0_AMT0=" . urlencode( $this->subscription->amount )
								. "&L_PAYMENTREQUEST_0_QTY0=1";

				// Maybe add an Invoice number
				if( ! empty( $this->subscription->max_failed_payments ) )
					$api_request  .= '&MAXFAILEDPAYMENTS=' . $this->subscription->max_failed_payments;

				// Maybe add an Invoice number
				if( ! empty( $this->subscription->invoice_number ) )
					$api_request  .= '&PROFILEREFERENCE=' . $this->subscription->invoice_number; // (Optional) Your own invoice or tracking number.

				// Maybe add a trial period
				if( $this->subscription->trial_frequency > 0 || $this->subscription->trial_total_cycles > 0 ) {
					$api_request  .=  '&TRIALAMT=' . urlencode( $this->subscription->trial_amount )
									. '&TRIALBILLINGPERIOD=' . urlencode( $this->subscription->trial_period )
									. '&TRIALBILLINGFREQUENCY=' . urlencode( $this->subscription->trial_frequency )
									. '&TRIALTOTALBILLINGCYCLES=' . urlencode( $this->subscription->trial_total_cycles );
				}

				if ( $this->subscription->add_to_next_bill == true ) {
					$api_request  .= '&AUTOBILLOUTAMT=AddToNextBilling';
				} else {
					$api_request  .= '&AUTOBILLOUTAMT=NoAutoBill';
				}

			} elseif ( 'GetRecurringPaymentsProfileDetails' == $action ) {

				$api_request .= '&METHOD=GetRecurringPaymentsProfileDetails'
							  . '&PROFILEID=' . urlencode( $profile_id );

			} elseif ( 'ManageRecurringPaymentsProfileStatus' == $action ) {

				$api_request .= '&METHOD=ManageRecurringPaymentsProfileStatus'
							  . '&PROFILEID=' . urlencode( $profile_id )
							  . '&ACTION=' . urlencode( $status );

				if ( isset( $args['note'] ) && ! empty( $args['note'] ) ) {
					$api_request .= '&NOTE=' . urlencode( $args['note'] );
				}

			}

			return $api_request;
		}


		/**
		 * Returns a string representing the details of the subscription. 
		 * 
		 * For example "$10 sign-up fee then $20 per Month for 3 Months". 
		 */
		public function get_subscription_string(){

			if( empty( $this->subscription ) )
				return 'No subscription set.';

			$subscription_details = '';

			if( $this->subscription->initial_amount != '0.00' )
				$subscription_details .= sprintf( '%s%s sign-up fee then', $this->get_currency_symbol(), $this->subscription->initial_amount );

			if( $this->subscription->trial_frequency > 0 || $this->subscription->trial_total_cycles > 0 ) {
				$subscription_details .= sprintf( ' %s %s', $this->subscription->trial_total_cycles, strtolower( $this->subscription->trial_period ) );
				if( $this->subscription->trial_amount > '0.00' ) {
					if( $this->subscription->trial_frequency > 1 )
						$subscription_details .= sprintf( ' trial period charged at %s%s every %s %ss followed by', $this->get_currency_symbol(), $this->subscription->trial_amount, $this->subscription->trial_frequency, strtolower( $this->subscription->trial_period ) );
					else
						$subscription_details .= sprintf( ' trial period charged at %s%s per %s followed by', $this->get_currency_symbol(), $this->subscription->trial_amount, strtolower( $this->subscription->trial_period ) );
				} else {
						$subscription_details .= sprintf( ' free trial period followed by' );
				}
			}

			if( $this->subscription->frequency > 1 )
				$subscription_details .= sprintf( ' %s%s every %s %ss', $this->get_currency_symbol(), $this->subscription->amount, $this->subscription->frequency, strtolower( $this->subscription->period ) );
			else
				$subscription_details .= sprintf( ' %s%s per %s', $this->get_currency_symbol(), $this->subscription->amount, strtolower( $this->subscription->period ) );

			if( $this->subscription->total_cycles != 0 )
				$subscription_details .= sprintf( ' for %s %ss', $this->subscription->total_cycles, strtolower( $this->subscription->period ) );

			return $subscription_details;
		}


		/**
		 * Get the value of a given subscription detail, eg. amount
		 * 
		 * For a list of the available values of $key, see the $defaults array in the constructor.
		 */
		public function get_subscription_detail( $key ){

			if( isset( $this->$key ) )
				$value = $this->$key;
			elseif( isset( $this->subscription->$key ) )
				$value = $this->subscription->$key;
			else
				$value = false;

			return $value;
		}


		/**
		 * Get the description for this subscription
		 */
		public function get_description(){
			return $this->subscription->description;
		}


}

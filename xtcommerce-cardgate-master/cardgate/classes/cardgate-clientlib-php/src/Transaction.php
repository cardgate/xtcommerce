<?php
/**
 * Copyright (c) 2016 CardGate B.V.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @license     The MIT License (MIT) https://opensource.org/licenses/MIT
 * @author      CardGate B.V.
 * @copyright   CardGate B.V.
 * @link        https://www.cardgate.com
 */
namespace cardgate\api {

	/**
	 * Transaction instance.
	 */
	class Transaction {

		/**
		 * The client associated with this transaction.
		 * @var Client
		 * @access protected
		 */
		protected $_oClient;

		/**
		 * The transaction id.
		 * @var String
		 * @access private
		 */
		private $_sId;

		/**
		 * The site id to use for payments.
		 * @var Integer
		 * @access protected
		 */
		protected $_iSiteId;

		/**
		 * The site key to use for payments.
		 * @var String
		 * @access protected
		 */
		protected $_sSiteKey;

		/**
		 * The transaction amount in cents.
		 * @var Integer
		 * @access protected
		 */
		protected $_iAmount;

		/**
		 * The transaction currency (ISO 4217).
		 * @var String
		 * @access protected
		 */
		protected $_sCurrency;

		/**
		 * The description for the transaction.
		 * @var String
		 * @access protected
		 */
		protected $_sDescription;

		/**
		 * A reference for the transaction.
		 * @var String
		 * @access protected
		 */
		protected $_sReference;

		/**
		 * The payment method for the transaction.
		 * @var Method
		 * @access protected
		 */
		protected $_oPaymentMethod = NULL;

		/**
		 * The payment method issuer for the transaction.
		 * @var String
		 * @access protected
		 */
		protected $_sIssuer = NULL;

		/**
		 * The recurring flag
		 * @var Boolean
		 * @access private
		 */
		private $_bRecurring = FALSE;

		/**
		 * The consumer for the transaction.
		 * @var Consumer
		 * @access protected
		 */
		protected $_oConsumer = NULL;

		/**
		 * The cart for the transaction.
		 * @var Cart
		 * @access protected
		 */
		protected $_oCart = NULL;

		/**
		 * The URL to send payment callback updates to.
		 * @var String
		 * @access protected
		 */
		protected $_sCallbackUrl = NULL;

		/**
		 * The URL to redirect to on success.
		 * @var String
		 * @access protected
		 */
		protected $_sSuccessUrl = NULL;

		/**
		 * The URL to redirect to on failre.
		 * @var String
		 * @access protected
		 */
		protected $_sFailureUrl = NULL;

		/**
		 * The URL to redirect to on pending.
		 * @var String
		 * @access protected
		 */
		protected $_sPendingUrl = NULL;

		/**
		 * The URL to redirect to after initial transaction register.
		 * @var String
		 * @access protected
		 */
		protected $_sActionUrl = NULL;

		/**
		 * The constructor.
		 * @param Client $oClient_ The client associated with this transaction.
		 * @param Integer $iSiteId_ Site id to create transaction for.
		 * @param Integer $iAmount_ The amount of the transaction in cents.
		 * @param String $sCurrency_ Currency (ISO 4217)
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		function __construct( Client $oClient_, $iSiteId_, $iAmount_, $sCurrency_ = 'EUR' ) {
			$this->_oClient = $oClient_;
			$this->setSiteId( $iSiteId_ )->setAmount( $iAmount_ )->setCurrency( $sCurrency_ );
		}

		/**
		 * Set the transaction id.
		 * @param String $sId_ Transaction id to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setId( $sId_ ) {
			if (
				! is_string( $sId_ )
				|| empty( $sId_ )
			) {
				throw new Exception( 'Transaction.Id.Invalid', 'invalid id: ' . $sId_ );
			}
			$this->_sId = $sId_;
			return $this;
		}

		/**
		 * Get the transaction id associated with this transaction.
		 * @return String The transaction id associated with this transaction.
		 * @access public
		 * @api
		 */
		public function getId() {
			if ( empty( $this->_sId ) ) {
				throw new Exception( 'Transaction.Not.Initialized', 'invalid transaction state' );
			}
			return $this->_sId;
		}

		/**
		 * Configure the transaction object with a site id.
		 * @param Integer $iSiteId_ Site id to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setSiteId( $iSiteId_ ) {
			if ( ! is_integer( $iSiteId_ ) ) {
				throw new Exception( 'Transaction.SiteId.Invalid', 'invalid site: ' . $iSiteId_ );
			}
			$this->_iSiteId = $iSiteId_;
			return $this;
		}

		/**
		 * Get the site id associated with this transaction.
		 * @return Integer The site id associated with this transaction.
		 * @access public
		 * @api
		 */
		public function getSiteId() {
			return $this->_iSiteId;
		}

		/**
		 * Set the Site key to authenticate the hash in the request.
		 * @param String $sSiteKey_ The site key to set.
		 * @return Client
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setSiteKey( $sSiteKey_ ) {
			if ( ! is_string( $sSiteKey_ ) ) {
				throw new Exception( 'Client.SiteKey.Invalid', 'invalid site key: ' . $sSiteKey_ );
			}
			$this->_sSiteKey = $sSiteKey_;
			return $this;
		}

		/**
		 * Get the Merchant API key to authenticate the transaction request with.
		 * @return String The merchant API key.
		 * @access public
		 * @api
		 */
		public function getSiteKey() {
			return $this->_sSiteKey;
		}

		/**
		 * Configure the transaction object with an amount.
		 * @param Integer $iAmount_ Amount in cents to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setAmount( $iAmount_ ) {
			if ( ! is_integer( $iAmount_ ) ) {
				throw new Exception( 'Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_ );
			}
			$this->_iAmount = $iAmount_;
			return $this;
		}

		/**
		 * Get the amount of the transaction.
		 * @return Integer The amount of the transaction.
		 * @access public
		 * @api
		 */
		public function getAmount() {
			return $this->_iAmount;
		}

		/**
		 * Configure the transaction currency.
		 * @param String $sCurrency_ The currency to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setCurrency( $sCurrency_ ) {
			if ( ! is_string( $sCurrency_ ) ) {
				throw new Exception( 'Transaction.Currency.Invalid', 'invalid currency: ' . $sCurrency_ );
			}
			$this->_sCurrency = $sCurrency_;
			return $this;
		}

		/**
		 * Get the currency of the transaction.
		 * @return String The currency of the transaction.
		 * @access public
		 * @api
		 */
		public function getCurrency() {
			return $this->_sCurrency;
		}

		/**
		 * Configure the description for the transaction.
		 * @param String $sDescription_ The description to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setDescription( $sDescription_ ) {
			if ( ! is_string( $sDescription_ ) ) {
				throw new Exception( 'Transaction.Description.Invalid', 'invalid description: ' . $sDescription_ );
			}
			$this->_sDescription = $sDescription_;
			return $this;
		}

		/**
		 * Get the description for the transaction.
		 * @return String The description of the transaction.
		 * @access public
		 * @api
		 */
		public function getDescription() {
			return $this->_sDescription;
		}

		/**
		 * Configure the reference for the transaction.
		 * @param String $sReference_ The reference to set.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setReference( $sReference_ ) {
			if ( ! is_string( $sReference_ ) ) {
				throw new Exception( 'Transaction.Reference.Invalid', 'invalid reference: ' . $sReference_ );
			}
			$this->_sReference = $sReference_;
			return $this;
		}

		/**
		 * Get the reference for the transaction.
		 * @return String The reference of the transaction.
		 * @access public
		 * @api
		 */
		public function getReference() {
			return $this->_sReference;
		}

		/**
		 * Set the payment method to use for the transaction.
		 * @param Mixed $mPaymentMethod_ The payment method to use for the transaction. Can be one of the
		 * consts defined in {@link Method} or a {@link Method} instance.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setPaymentMethod( $mPaymentMethod_ ) {
			if ( $mPaymentMethod_ instanceof Method ) {
				$this->_oPaymentMethod = $mPaymentMethod_;
			} elseif ( is_string( $mPaymentMethod_ ) ) {
				$this->_oPaymentMethod = new Method( $this->_oClient, $mPaymentMethod_, $mPaymentMethod_ );
			} else {
				throw new Exception( 'Transaction.PaymentMethod.Invalid', 'invalid payment method: ' . $mPaymentMethod_ );
			}
			return $this;
		}

		/**
		 * Get the payment method that will be used for the transaction.
		 * @return Method The payment method that will be used for the transaction.
		 * @access public
		 * @api
		 */
		public function getPaymentMethod() {
			return $this->_oPaymentMethod;
		}

		/**
		 * Set the optional payment method issuer to use for the transaction.
		 * @param String $sIssuer_ The payment method issuer to use for the transaction.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setIssuer( $sIssuer_ ) {
			if (
				empty( $this->_oPaymentMethod )
				|| ! is_string( $sIssuer_ )
			) {
				throw new Exception( 'Transaction.Issuer.Invalid', 'invalid issuer: ' . $sIssuer_ );
			}
			$this->_sIssuer = $sIssuer_;
			return $this;
		}

		/**
		 * Get the optional payment method issuer that will be used for the transaction.
		 * @return Issuer The payment method issuer that will be used for the transaction.
		 * @access public
		 * @api
		 */
		public function getIssuer() {
			return $this->_oIssuer;
		}

		/**
		 * Set the recurring flag on the transaction.
		 * @param Boolean $bRecurring_ Wether or not this transaction can be used for recurring.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setRecurring( $bRecurring_ ) {
			$this->_bRecurring = $bRecurring_;
			return $this;
		}

		/**
		 * Get the recurring flag of the transaction.
		 * @return Boolean Returns wether or not this transaction can be used for recurring.
		 * @access public
		 * @api
		 */
		public function getRecurring() {
			return $this->_bRecurring;
		}

		/**
		 * Set the consumer for the transaction.
		 * @param Consumer $oConsumer_ The consumer for the transaction.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setConsumer( Consumer $oConsumer_ ) {
			$this->_oConsumer = $oConsumer_;
			return $this;
		}

		/**
		 * Get the consumer for the transaction.
		 * @return Consumer The consumer for the transaction.
		 * @access public
		 * @api
		 */
		public function getConsumer() {
			if ( empty( $this->_oConsumer ) ) {
				$this->_oConsumer = new Consumer();
			}
			return $this->_oConsumer;
		}

		/**
		 * Set the cart for the transaction.
		 * @param Cart $oCart_ The cart for the transaction.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setCart( Cart $oCart_ ) {
			$this->_oCart = $oCart_;
			return $this;
		}

		/**
		 * Get the cart for the transaction.
		 * @return Cart The cart for the transaction.
		 * @access public
		 * @api
		 */
		public function getCart() {
			if ( empty( $this->_oCart ) ) {
				$this->_oCart = new Cart();
			}
			return $this->_oCart;
		}

		/**
		 * Set the callback URL.
		 * @param String $sUrl_ The URL to send callbacks to.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setCallbackUrl( $sUrl_ ) {
			if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
				throw new Exception( 'Transaction.CallbackUrl.Invalid', 'invalid url: ' . $sUrl_ );
			}
			$this->_sCallbackUrl = $sUrl_;
			return $this;
		}

		/**
		 * Get the callbacl URL.
		 * @return String The URL callbacks are being sent to.
		 * @access public
		 * @api
		 */
		public function getCallbackUrl() {
			return $this->_sCallbackUrl;
		}

		/**
		 * Set the success URL.
		 * @param String $sUrl_ The URL to send successful transaction redirects.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setSuccessUrl( $sUrl_ ) {
			if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
				throw new Exception( 'Transaction.SuccessUrl.Invalid', 'invalid url: ' . $sUrl_ );
			}
			$this->_sSuccessUrl = $sUrl_;
			return $this;
		}

		/**
		 * Get the success URL.
		 * @return String The URL successful transactions are being redirected to.
		 * @access public
		 * @api
		 */
		public function getSuccessUrl() {
			return $this->_sSuccessUrl;
		}

		/**
		 * Set the failure URL.
		 * @param String $sUrl_ The URL to send failed transaction redirects.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setFailureUrl( $sUrl_ ) {
			if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
				throw new Exception( 'Transaction.FailureUrl.Invalid', 'invalid url: ' . $sUrl_ );
			}
			$this->_sFailureUrl = $sUrl_;
			return $this;
		}

		/**
		 * Get the failure URL.
		 * @return String The URL failed transactions are being redirected to.
		 * @access public
		 * @api
		 */
		public function getFailureUrl() {
			return $this->_sFailureUrl;
		}

		/**
		 * Set the failure URL.
		 * @param String $sUrl_ The URL to send failed transaction redirects.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setPendingUrl( $sUrl_ ) {
			if ( FALSE === filter_var( $sUrl_, FILTER_VALIDATE_URL ) ) {
				throw new Exception( 'Transaction.PendingUrl.Invalid', 'invalid url: ' . $sUrl_ );
			}
			$this->_sPendingUrl = $sUrl_;
			return $this;
		}

		/**
		 * Use this method to set the url for success, failure and pending all at once.
		 * @param String $sUrl_ The URL to use for success, failure and pending.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function setRedirectUrl( $sUrl_ ) {
			$this->setSuccessUrl( $sUrl_ )->setFailureUrl( $sUrl_ )->setPendingUrl( $sUrl_ );
			return $this;
		}

		/**
		 * Get the pending URL.
		 * @return String The URL pending transactions are being redirected to.
		 * @access public
		 * @api
		 */
		public function getPendingUrl() {
			return $this->_sPendingUrl;
		}

		/**
		 * Get the redirect URL after transaction register.
		 * @return String The URL to redirect to after register.
		 * @access public
		 * @api
		 */
		public function getActionUrl() {
			return $this->_sActionUrl;
		}

		/**
		 * Registers the transaction with the cardgate payment gateway.
		 * @return Transaction
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function register() {
			$aData = [
				'site_id' 		=> $this->_iSiteId,
				'amount'		=> $this->_iAmount,
				'currency_id'	=> $this->_sCurrency,
				'url_callback'	=> $this->_sCallbackUrl,
				'url_success'	=> $this->_sSuccessUrl,
				'url_failure'	=> $this->_sFailureUrl,
				'url_pending'	=> $this->_sPendingUrl,
				'description'	=> $this->_sDescription,
				'reference'		=> $this->_sReference,
				'recurring'		=> $this->_bRecurring ? '1' : '0'
			];
			if ( ! is_null( $this->_oConsumer ) ) {
				$aData['email'] = $this->_oConsumer->getEmail();
				$aData['phone'] = $this->_oConsumer->getPhone();
				$aData['consumer'] = array_merge(
					$this->_oConsumer->address()->getData(),
					$this->_oConsumer->shippingAddress()->getData( 'shipto_' )
				);
				$aData['country_id'] = $this->_oConsumer->address()->getCountry();
			}
			if ( ! is_null( $this->_oCart ) ) {
				$aData['cartitems'] = $this->_oCart->getData();
			}

			$sResource = 'payment/';
			if ( ! empty( $this->_oPaymentMethod ) ) {
				$sResource .= $this->_oPaymentMethod->getId() . '/';
				$aData['issuer'] = $this->_sIssuer;
			}

			$aData = array_filter( $aData ); // remove NULL values
			$aResult = $this->_oClient->doRequest( $sResource, $aData, 'POST' );

			if (
				empty( $aResult['payment'] )
				|| empty( $aResult['payment']['transaction'] )
			) {
				throw new Exception( 'Transaction.Request.Invalid', 'invalid payment data returned' );
			}
			$this->_sId = $aResult['payment']['transaction'];
			if (
				isset( $aResult['payment']['action'] )
				&& 'redirect' == $aResult['payment']['action']
			) {
				$this->_sActionUrl = $aResult['payment']['url'];
			}

			return $this;
		}

		/**
		 * This method can be used to determine if this transaction can be refunded.
		 * @param Boolean $iRemainder_ Will be set to the amount that can be refunded.
		 * refunds are supported.
		 * @return Boolean
		 * @throws Exception
		 * @access public
		 */
		public function canRefund( &$iRemainder_ = NULL ) {
			$sResource = "transaction/{$this->_sId}/";

			$aResult = $this->_oClient->doRequest( $sResource, NULL, 'GET' );

			if ( empty( $aResult['transaction'] ) ) {
				throw new \cardgate\api\Exception( 'Transaction.Details.Invalid', 'invalid transaction data returned' );
			}

			$iRemainder_ = (integer)@$aResult['transaction']['refund_remainder'];

			return !!@$aResult['transaction']['can_refund'];
		}

		/**
		 * This method can be used to (partially) refund a transaction.
		 * @param Integer $iAmount_
		 * @return Transaction The new (refund) transaction.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function refund( $iAmount_ = NULL, $sDescription_ = NULL ) {
			if (
				! is_null( $iAmount_ )
				&& ! is_integer( $iAmount_ )
			) {
				throw new Exception( 'Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_ );
			}

			$aData = [
				'amount'		=> is_null( $iAmount_ ) ? $this->_iAmount : $iAmount_,
				'currency_id'	=> $this->_sCurrency,
				'description'	=> $sDescription_
			];

			$sResource = "refund/{$this->_sId}/";

			$aData = array_filter( $aData ); // remove NULL values
			$aResult = $this->_oClient->doRequest( $sResource, $aData, 'POST' );

			if (
				empty( $aResult['refund'] )
				|| empty( $aResult['refund']['transaction'] )
			) {
				throw new Exception( 'Transaction.Request.Invalid', 'invalid payment data returned' );
			}

			return $this->_oClient->transactions()->get( $aResult['refund']['transaction'] );
		}

		/**
		 * This method can be used to recur a transaction.
		 * @param Integer $iAmount_
		 * @return Transaction The new (recurred) transaction.
		 * @throws Exception
		 * @access public
		 * @api
		 */
		public function recur( $iAmount_, $sReference_ = NULL, $sDescription_ = NULL ) {
			if ( ! is_integer( $iAmount_ ) ) {
				throw new Exception( 'Transaction.Amount.Invalid', 'invalid amount: ' . $iAmount_ );
			}

			$aData = [
				'amount'		=> $iAmount_,
				'currency_id'	=> $this->_sCurrency,
				'reference'		=> $sReference_,
				'description'	=> $sDescription_
			];

			$sResource = "recurring/{$this->_sId}/";

			$aData = array_filter( $aData ); // remove NULL values
			$aResult = $this->_oClient->doRequest( $sResource, $aData, 'POST' );

			if (
				empty( $aResult['recurring'] )
				|| empty( $aResult['recurring']['transaction_id'] )
			) {
				throw new Exception( 'Transaction.Request.Invalid', 'invalid payment data returned' );
			}

			return $this->_oClient->transactions()->get( $aResult['recurring']['transaction_id'] );
		}

	}

}

<?php

/**
 * Shop System Plugins - Terms of Use
 *
 * These plugins are offered by CardGate
 *
 * They have been tested and approved for full functionality in the standard
 * configuration
 * (status on delivery) of the corresponding shop system. They are under
 * General Public License Version 2 (GPLv2) and can be used, developed and
 * passed on to third parties under the same terms.
 *
 * However, CardGate does not provide any guarantee or accept any liability
 * for any errors occurring when used in an enhanced, customized shop system
 * configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and
 * requires a comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. CardGate does not guarantee
 * their full functionality neither does CardGate assume liability for any
 * disadvantages related to the use of the plugins. Additionally, CardGate
 * does not guarantee the full functionality for customized shop systems or
 * installed plugins of other vendors of plugins within the same shop system.
 *
 * Customers are responsible for testing the plugin's functionality before
 * starting productive operation.
 *
 * By installing the plugin into the shop system the customer agrees to these
 * terms of use. Please do not use the plugin if you do not agree to these
 * terms of use!
 */
defined ( '_VALID_CALL' ) or die ( 'Direct Access is not allowed.' );
class cardgate {
	var $data = array ();
	var $post_form = false;
	var $iframe = false;
	var $external = true;
	var $IFRAME_URL = '';
	var $_transaction_table = 'cardgate_transaction';
	var $_transaction_id = '';
	/**
	 * WD Variablen
	 */
	var $demoMode = false;
	var $initParams = array ();
	var $version = '1.5.1';
	var $paymentTypes = array (
			'CARDGATE_AFTERPAY' => 'afterpay',
			'CARDGATE_BANCONTACT' => 'bancontact',
			'CARDGATE_BANKTRANSFER' => 'banktransfer',
			'CARDGATE_BITCOIN' => 'bitcoin',
			'CARDGATE_CCARD' => 'creditcard',
			'CARDGATE_DIRECT_DEBIT' => 'directdebit',
			'CARDGATE_GIROPAY' => 'giropay',
			'CARDGATE_IDEAL' => 'idealpro',
			'CARDGATE_KLARNA' => 'klarna',
			'CARDGATE_PAYPAL' => 'paypal',
			'CARDGATE_P24' => 'przelewy24',
			'CARDGATE_SOFORTUEBERWEISUNG' => 'sofortbanking' 
	);
	
	/**
	 * php style constructor
	 *
	 * @access public
	 */
	function cardgate() {
		global $xtLink;
	}
	
	/**
	 * XTC-Funktion, um das Paymentrequest an einen externen PSP zu senden
	 *
	 * Die Funktion spiegelt in etwa die alte "payment_action" wieder. An dieser Stelle
	 * wird die Anfrage gestellt und je nach der Ergebnis der Sprung auf die entsprechende
	 * Seite vorbereitet (idR IFrame oder Fehlerseite)
	 *
	 * @param $order_data array
	 *        	mit den wichtigsten Infos zur Bestellung
	 * @return URL, zu der als nächstes gesprungen werden soll
	 * @access public
	 */
	function pspRedirect($aOrderData = null) {
		global $xtLink, $filter, $order, $db, $language;		
		require_once 'cardgate-clientlib-php/init.php';
		
		if (! $aOrderData) {
			$aOrderData = $order->order_data;
		}
		if (($res = $this->_checkOrderData ( $aOrderData )) !== true) {
			return $xtLink->_link ( $res );
		}
		
		$iOrderId = ( int ) $aOrderData ['orders_id'];
		if (! is_int ( $iOrderId )) {
			return $xtLink->_link ( array (
					'page' => 'cardgate_checkout',
					'paction' => 'failure',
					'conn' => 'SSL',
					'params' => 'code_1=210' 
			) );
		}
		// Special, da xt der Meinung ist alle alten GET Parameter mit anzuh�ngen
		$_GET = array ();
		
		// Anfrage durchführen
		
		try {
			
			$iMerchantId = ( int ) CARDGATE_MERCHANT_ID;
			$sMerchantApiKey = CARDGATE_MERCHANT_API_KEY;
			$bIsTest = (CARDGATE_TEST_MODE == 'true' ? true : false);
			$sLanguage = $aOrderData ['language_code'];
			if ($this->getMajorVersion () <= 4) {
				$sPlatformVersion = 'xtCommerce4';
			} else {
				$sPlatformVersion = 'xtCommerce5';
			}
			$iSiteId = ( int ) CARDGATE_SITE_ID;
			$iAmount = ( int ) round ( $order->order_total ['total'] ['plain'] * 100 );
			$sCurrency = $aOrderData ['currency_code'];
			$sPaymentMethod = $this->paymentTypes [$_SESSION ['selected_payment_sub']];
			$sSuccessUrl = $this->_link ( array (
					'page' => 'cardgate_checkout',
					'conn' => 'SSL' 
			) );
			$sFailureUrl = $sSuccessUrl;
			$sCallbackUrl = $this->_link ( array (
					'lang_code' => $language->default_language,
					'page' => 'cardgate_checkout',
					'paction' => 'confirm',
					'conn' => 'SSL' 
			) );
			$reference = 'O' . time () . $iOrderId;
			
			$oCardGate = new cardgate\api\Client ( ( int ) $iMerchantId, $sMerchantApiKey, $bIsTest );
			$oCardGate->setIp ( $_SERVER ['REMOTE_ADDR'] );
			$oCardGate->setLanguage ( $sLanguage );
			$oCardGate->version ()->setPlatformName ( 'xtCommerce' );
			$oCardGate->version ()->setPlatformVersion ( $sPlatformVersion );
			$oCardGate->version ()->setPluginName ( 'CardGate' );
			$oCardGate->version ()->setPluginVersion ( $this->version );
			
			$oTransaction = $oCardGate->transactions ()->create ( $iSiteId, $iAmount, $sCurrency );
			
			// Configure payment option.
			$oTransaction->setPaymentMethod ( $sPaymentMethod );
			if ($sPaymentMethod == 'idealpro') {
				$oTransaction->setIssuer ( 'bankOption' );
			}
			
			// Configure customer.
			$oCustomer = $oTransaction->getCustomer ();
			$oCustomer->setEmail ( $order->order_data ['customers_email_address'] );
			$oCustomer->address ()->setFirstName ( $order->order_data ['billing_firstname'] );
			$oCustomer->address ()->setLastName ( $order->order_data ['billing_lastname'] );
			$oCustomer->address ()->setAddress ( $order->order_data ['billing_street_address'] );
			$oCustomer->address ()->setZipCode ( $order->order_data ['billing_postcode'] );
			$oCustomer->address ()->setCity ( $order->order_data ['billing_city'] );
			$oCustomer->address ()->setCountry ( $order->order_data ['billing_country_code'] );
			
			//add cartitmes if necessary
			if ($sPaymentMethod =='afterpay' || $sPaymentMethod == 'klarna') {
				$oCart = $oTransaction->getCart ();
				$aCartItems = $this->getCartItems( $order, $iAmount);
				
				foreach ( $aCartItems as $item ) {			
					switch ($item ['type']) {
						case 'product' :
							$iItemType = \cardgate\api\Item::TYPE_PRODUCT;
							break;
						case 'shipping' :
							$iItemType = \cardgate\api\Item::TYPE_SHIPPING;
							break;
						case 'paymentfee' :
							$iItemType = \cardgate\api\Item::TYPE_HANDLING;
							break;
						case 'discount' :
							$iItemType = \cardgate\api\Item::TYPE_DISCOUNT;
							break;
					}
					
					$oItem = $oCart->addItem ( $iItemType, $item ['model'], $item ['name'], ( int ) $item ['quantity'], ( int ) $item ['price_wt'] );
					$oItem->setVat ( $item ['vat'] );
					$oItem->setVatAmount ( $item ['vat_amount'] );
					$oItem->setVatIncluded ( 0 );
				}
			}
			
			$oTransaction->setCallbackUrl ( $sCallbackUrl );
			$oTransaction->setSuccessUrl ( $sSuccessUrl );
			$oTransaction->setFailureUrl ( $sFailureUrl );
			
			$oTransaction->setReference ( $reference );
			$oTransaction->setDescription ( 'Order ' . $iOrderId );
			
			$result = @$db->Execute ( "INSERT INTO " . $this->_transaction_table . " (TRID, PAYSYS, STATE, DATE, ORDERID) VALUES ('" . $reference . "', '" . $sPaymentMethod . "','REDIRECTED', NOW(), $iOrderId)" );
			
			$oTransaction->register ();
			
			$sActionUrl = $oTransaction->getActionUrl ();
			
			if (NULL !== $sActionUrl) {
				return $sActionUrl;
			} else {
				$sErrorMessage = 'CardGate error: ' . htmlspecialchars ( $oException_->getMessage () );
				$this->_failureRedirect ( $sErrorMessage );
			}
		} catch ( cardgate\api\Exception $oException_ ) {
			$sErrorMessage = 'CardGate error: ' . htmlspecialchars ( $oException_->getMessage () );
			$this->_failureRedirect ( $sErrorMessage );
		}
	}
	
	/**
	 * XTC-Funktion, um auf eine spezielle Success-Seite zu springen
	 *
	 * Da der Aufruf in der checkout-Klasse "payment_process" falsch ausgewertet wird (!= anstatt !==)
	 * macht die Funktion zur Zeit keinen Sinn, da auch eine URL "true" wäre und nie aufgerufen werden
	 * würde.
	 *
	 * @return URL oder true
	 * @access public
	 */
	function pspSuccess() {
		return true;
	}
	
	/**
	 * Führt Prüfungen vor Absenden des Request durch
	 *
	 * @return true im Erfolgsfall, ansonsten Array mit Daten für Sprung zur Fehlerseite
	 * @access private
	 */
	function _checkOrderData($order_data) {
		// Prüfen, ob Paymenttype gsetezt
		if (! array_key_exists ( $_SESSION ['selected_payment_sub'], $this->paymentTypes )) {
			return array (
					'page' => 'cardgate_checkout',
					'paction' => 'failure',
					'conn' => 'SSL',
					'params' => 'code_1=209' 
			);
		}
		return true;
	}
	function _link($data) {
		global $xtLink;
		$ampedLink = $xtLink->_link ( $data );
		$link = str_replace ( '&amp;', '&', $ampedLink );
		return $link;
	}
	function _failureRedirect($message) {
		global $xtLink;
		$failureUrl = $xtLink->_link ( array (
				'page' => 'cardgate_checkout',
				'params' => 'message=' . $message,
				'conn' => 'SSL' 
		) );
		$xtLink->_redirect ( $failureUrl );
	}
	function getMajorVersion() {
		$parts = explode ( '.', _SYSTEM_VERSION );
		return ( int ) $parts [0];
	}
	function getMinorVersion() {
		$parts = explode ( '.', _SYSTEM_VERSION );
		return ( int ) $parts [1];
	}
	private function getCartItems($oOrder, $iAmount) {
		
		$items = array ();
		$nr = 0;
		$iCartItemTotal = 0;
		$iCartItemTaxTotal = 0;
		
		$iOrderTotal = (int) round($oOrder->order_total['total']['plain']*100);
		$aOrderItems = $oOrder->order_products;
		
		// any discount will be already calculated in the item total
		foreach ( $aOrderItems as $aItem ) {
			
			$iQty = (int) $aItem['products_quantity'];
			$iPrice = (int) round($aItem['products_price']['plain_otax'] *100);
			$iTax = (int) round($aItem['products_tax']['plain']*100);
			$iTotal = (int) round ( $iPrice + $iTax );
			$iTaxrate = ($iTax > 0 ? round ( ($iTotal / $iPrice - 1) * 100, 2 ) : 0);
			
			$nr ++;
			
			$items [$nr] ['type'] = 'product';
			$items [$nr] ['model'] = $aItem['products_model'];
			$items [$nr] ['name'] = $aItem['products_name'];
			$items [$nr] ['quantity'] = $iQty;
			$items [$nr] ['price_wt'] = $iPrice;
			$items [$nr] ['vat'] = $iTaxrate;
			$items [$nr] ['vat_amount'] = $iTax;
			
			$iCartItemTotal += round ( $iPrice * $iQty );
			$iCartItemTaxTotal += round ( $iTax * $iQty );
		}
		
		$iShippingTotal = 0;
		$iShippingVatTotal = 0;
		$sub_content = $_SESSION['cart']->show_sub_content;
		if (isset($sub_content['shipping'])){
			$aShipping = $sub_content['shipping'];
			
			$iQty = (int) $aShipping['products_quantity'];
			$iPrice = (int) round($aShipping['products_price']['plain_otax'] *100);
			$iTax = (int) round($aShipping['products_tax']['plain']*100);
			$iTotal = (int) round ( $iPrice + $iTax );
			$iTaxrate = ($iTax > 0 ? round ( ($iTotal / $iPrice - 1) * 100, 2 ) : 0);
			
			$nr ++;
			
			$items [$nr] ['type'] = 'shipping';
			$items [$nr] ['model'] = $aShipping['products_model'];
			$items [$nr] ['name'] = $aShipping['products_name'];
			$items [$nr] ['quantity'] = $iQty;
			$items [$nr] ['price_wt'] = $iPrice;
			$items [$nr] ['vat'] = $iTaxrate;
			$items [$nr] ['vat_amount'] = $iTax;
			
			$iShippingTotal = $iPrice;
			$iShippingVatTotal = $iTax;
		}
		
		$iExtraFee;
		
		/*
		$fpExtraFee = (empty ( $woocommerce->session->extra_cart_fee ) ? 0 : $woocommerce->session->extra_cart_fee);
		$iExtraFee = round ( $fpExtraFee * 100 );
		
		if ($iExtraFee > 0) {
			
			$nr ++;
			$items [$nr] ['type'] = 'paymentfee';
			$items [$nr] ['model'] = 'extra_costs';
			$items [$nr] ['name'] = 'payment_fee';
			$items [$nr] ['quantity'] = 1;
			$items [$nr] ['price_wt'] = $iExtraFee;
			$items [$nr] ['vat'] = 0;
			$items [$nr] ['vat_amount'] = 0;
		}
		*/
		
		$iCorrection = round ( $iOrderTotal - $iCartItemTotal - $iCartItemTaxTotal - $iShippingTotal - $iShippingVatTotal - $iExtraFee );
	
		if ($iCorrection != 0) {
			
			$nr ++;
			$items [$nr] ['type'] = 'product';
			$items [$nr] ['model'] = 'Correction';
			$items [$nr] ['name'] = 'item_correction';
			$items [$nr] ['quantity'] = 1;
			$items [$nr] ['price_wt'] = $iCorrection;
			$items [$nr] ['vat'] = 0;
			$items [$nr] ['vat_amount'] = 0;
		}
		return $items;
	}
}

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
define ( 'TABLE_CARDGATE_TRANSACTION', 'cardgate_transaction' );

$show_index_boxes = false;

if ($page->page_action == 'confirm') {
	// hash check
	if (! empty ( $_GET ['hash'] )) {
		$sRoot = dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) );
		
		require_once ($sRoot . '/cardgate/classes/cardgate-clientlib-php/init.php');
		
		try {
			$data = $_REQUEST;
			$iMerchantId = ( int ) CARDGATE_MERCHANT_ID;
			$sMerchantApiKey = CARDGATE_MERCHANT_API_KEY;
			$sHashKey = CARDGATE_HASH_KEY;
			$bTestMode = (CARDGATE_TEST_MODE == 'true' ? true : false);
			
			$oCardGate = new cardgate\api\Client ( $iMerchantId, $sMerchantApiKey, $bTestMode );
			$oCardGate->setIp ( $_SERVER ['REMOTE_ADDR'] );
			
			if (FALSE == $oCardGate->transactions ()->verifyCallback ( $data, $sHashKey )) {
				die ( 'Hashcheck failed!' );
			}
		} catch ( cardgate\api\Exception $oException_ ) {
			die ( 'Hashckeck failed!' );
		}
	}
	
	// hashcheck has passed
	// process order
	
	$result = $db->Execute ( "SELECT ORDERID FROM " . TABLE_CARDGATE_TRANSACTION . " WHERE TRID = '" . $data ['reference'] . "' LIMIT 1" );
	$fields = $result->fields;
	$iOrderId = ( int ) $fields ['ORDERID'];
	
	$order = new order ( $iOrderId, - 1 );
	
	if (checkPaid ( $order )) {
		die ( 'Payment already processed!' );
	}
	
	if ($data ['code'] == '0') {
		$paymentState = 'PENDING';
	}
	if ($data ['code'] >= '200' && $data ['code'] < '300') {
		$paymentState = 'SUCCESS';
	}
	if ($data ['code'] >= '300' && $data ['code'] < '400') {
		if ($data ['code'] == 309) {
			$paymentState = 'CANCEL';
		} else {
			$paymentState = 'FAILURE';
		}
	}
	if ($data ['code'] >= '700' && $data ['code'] < '800') {
		$paymentState = 'PENDING';
	}
	
	if ($paymentState == 'SUCCESS') {
		if (! empty ( $iOrderId )) {
			$result = $db->AutoExecute ( TABLE_CARDGATE_TRANSACTION, Array (
					'STATE' => $paymentState 
			), 'UPDATE', 'TRID="' . $data ['reference'] . '"' );
			updateOrderPayment ( $iOrderId, $paymentState );
		}
		
		$strMsg = TEXT_CARDGATE_PAYMENT_COMMENT . ' ' . constant ( "TEXT_PAYMENT_CARDGATE_" . strtoupper ( $data ['pt'] ) );
		;
		
		$order->_sendOrderMail ();
		$order->_updateOrderStatus ( CARDGATE_ORDER_STATUS_COMPLETED, $strMsg, 'true', 'true', 'user', $data ['transaction'] );
	}
	
	if ($paymentState == 'PENDING') {
		updateOrderPayment ( $iOrderId, $paymentState );
		$txtOk = $db->AutoExecute ( TABLE_CARDGATE_TRANSACTION, Array (
				'STATE' => $paymentState 
		), 'UPDATE', 'TRID="' . $data ['reference'] . '"' );
		
		$strMsg = TEXT_CARDGATE_PAYMENT_PENDINGCOMMENT;
		$order->_updateOrderStatus ( CARDGATE_ORDER_STATUS_PENDING, $strMsg, 'true', 'true', 'user', $data ['transaction'] );
	}
	
	if ($paymentState == 'CANCEL') {
		$strMsg = 'Customer canceled the payment process';
		$order->_updateOrderStatus ( CARDGATE_ORDER_STATUS_CANCEL, $strMsg, 'false' );
		$txtOk = $db->AutoExecute ( TABLE_CARDGATE_TRANSACTION, Array (
				'STATE' => $paymentState 
		), 'UPDATE', 'TRID="' . $data ['reference'] . '"' );
	}
	
	if ($paymentState == 'FAILURE') {
		$payment_error_message = 'An error occured during the payment process: <br>' . $data ['reference'];
		// Order-Status setzen und History speichern
		$order->_updateOrderStatus ( CARDGATE_ORDER_STATUS_FAILED, $payment_error_message, 'false' );
		$txtOk = $db->AutoExecute ( TABLE_CARDGATE_TRANSACTION, Array (
				'STATE' => $paymentState 
		), 'UPDATE', 'TRID="' . $data ['reference'] . '"' );
	}
	
	die ( $data ['transaction'] . '.' . $data ['code'] );
} else {
	
	$strState = "";
	if (isset ( $_GET ['reference'] )) {
		
		if (isset ( $_SESSION ['redirect_url'] )) {
			unset ( $_SESSION ['redirect_url'] );
		}
		
		$rs = $db->Execute ( 'SELECT STATE,MESSAGE FROM ' . TABLE_CARDGATE_TRANSACTION . ' WHERE `TRID`="' . $_GET ['reference'] . '" ' );
		if ($rs->RecordCount () == 1) {
			$strState = $rs->fields ['STATE'];
		}
	}
	if ($strState == 'SUCCESS') {
		unset ( $_SESSION ['last_order_id'] );
		$_SESSION ['cart']->_resetCart ();
		$checkout_data = array (
				'page_action' => 'success' 
		);
	} elseif ($strState == 'CANCEL') {
		$checkout_data = array (
				'page_action' => 'cancel' 
		);
	} elseif ($strState == 'PENDING') {
		unset ( $_SESSION ['last_order_id'] );
		$_SESSION ['cart']->_resetCart ();
		$checkout_data = array (
				'page_action' => 'pending' 
		);
	} elseif ($strState == 'FAILURE') {
		$messages = array ();
		$messages [0] ['message'] = 'An error occured during the payment process: <br>' . $data ['reference'];
		$checkout_data = array (
				'page_action' => 'failure',
				'messages' => $messages 
		);
	} else {
		$messages = array ();
		if (isset ( $_GET ['message'] )) {
			$messages [0] ['message'] = htmlentities ( $_GET ['message'] );
		} else {
			$messages [0] ['message'] = 'Invalid call';
		}
		$checkout_data = array (
				'page_action' => 'failure',
				'messages' => $messages 
		);
	}
	
	if (is_array ( $checkout_data )) {
		$tpl_data = $checkout_data;
		($plugin_code = $xtPlugin->PluginCode ( 'module_checkout.php:checkout_data' )) ? eval ( $plugin_code ) : false;
		$template = new Template ();
		$tpl = 'cardgate_checkout.html';
		($plugin_code = $xtPlugin->PluginCode ( 'module_checkout.php:checkout_bottom' )) ? eval ( $plugin_code ) : false;
		
		$page_data = $template->getTemplate ( 'smarty', '/' . _SRV_WEB_CORE . 'pages/' . $tpl, $tpl_data );
	}
}
function checkPaid($order) {
	return ( bool ) ($order->order_data ['orders_status_id'] == CARDGATE_ORDER_STATUS_COMPLETED);
}
function updateOrderPayment($oid, $strOrderStatus, $callback_id='',$callback_messge='') {
	$parts = explode ( '.', _SYSTEM_VERSION );
	$iVersion = ( int ) $parts [0];
	
	if ($iVersion < 5) {
		if (! empty ( $strOrderStatus ) && $oid > 0) {
			global $db;
			$ok = $db->AutoExecute ( TABLE_ORDERS, Array (
					'subpayment_code' => $strOrderStatus 
			), 'UPDATE', 'orders_id="' . $oid . '" AND subpayment_code!="' . $strOrderStatus . '"' );
			if (! $ok) {
				
				return cardgateCheckoutPageConfirmResponse ( 'Paymenttype update failed' );
			}
		}
		return true;
	}
}
function cardgateCheckoutPageConfirmResponse($message = null) {
	if ($message != null) {
		$value = 'result="NOK" message="' . $message . '" ';
	} else {
		$value = 'result="OK"';
	}
	return '<QPAY-CONFIRMATION-RESPONSE ' . $value . ' />';
}

?>

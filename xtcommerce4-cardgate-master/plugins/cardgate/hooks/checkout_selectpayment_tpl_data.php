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
if ($tpl_data ['payment_code'] == 'cardgate') {
	$tpl_data ['plugin'] = new cardgate ();
}

$cg_payments = array (
		array (
				'name' => 'CARDGATE_AFTERPAY',
				'active' => CARDGATE_ACTIVATE_AFTERPAY,
				'order' => CARDGATE_ORDER_AFTERPAY,
				'group' => CARDGATE_PERMISSION_AFTERPAY,
				'text' => TEXT_PAYMENT_CARDGATE_AFTERPAY,
				'img' => 'afterpay' 
		),
		array (
				'name' => 'CARDGATE_BANCONTACT',
				'active' => CARDGATE_ACTIVATE_BANCONTACT,
				'order' => CARDGATE_ORDER_BANCONTACT,
				'group' => CARDGATE_PERMISSION_BANCONTACT,
				'text' => TEXT_PAYMENT_CARDGATE_BANCONTACT,
				'img' => 'bancontact' 
		),
		array (
				'name' => 'CARDGATE_BANKTRANSFER',
				'active' => CARDGATE_ACTIVATE_BANKTRANSFER,
				'order' => CARDGATE_ORDER_BANKTRANSFER,
				'group' => CARDGATE_PERMISSION_BANKTRANSFER,
				'text' => TEXT_PAYMENT_CARDGATE_BANKTRANSFER,
				'img' => 'banktransfer' 
		),
		
		array (
				'name' => 'CARDGATE_BITCOIN',
				'active' => CARDGATE_ACTIVATE_BITCOIN,
				'order' => CARDGATE_ORDER_BITCOIN,
				'group' => CARDGATE_PERMISSION_BITCOIN,
				'text' => TEXT_PAYMENT_CARDGATE_BITCOIN,
				'img' => 'bitcoin' 
		),
		array (
				'name' => 'CARDGATE_CCARD',
				'active' => CARDGATE_ACTIVATE_CCARD,
				'order' => CARDGATE_ORDER_CCARD,
				'group' => CARDGATE_PERMISSION_CCARD,
				'text' => TEXT_PAYMENT_CARDGATE_CCARD,
				'img' => 'creditcard' 
		),
		array (
				'name' => 'CARDGATE_DIRECT_DEBIT',
				'active' => CARDGATE_ACTIVATE_DIRECT_DEBIT,
				'order' => CARDGATE_ORDER_DIRECT_DEBIT,
				'group' => CARDGATE_PERMISSION_DIRECT_DEBIT,
				'text' => TEXT_PAYMENT_CARDGATE_DIRECT_DEBIT,
				'img' => 'directdebit' 
		),
		array (
				'name' => 'CARDGATE_GIROPAY',
				'active' => CARDGATE_ACTIVATE_GIROPAY,
				'order' => CARDGATE_ORDER_GIROPAY,
				'group' => CARDGATE_PERMISSION_GIROPAY,
				'text' => TEXT_PAYMENT_CARDGATE_GIROPAY,
				'img' => 'giropay' 
		),
		array (
				'name' => 'CARDGATE_IDEAL',
				'active' => CARDGATE_ACTIVATE_IDEAL,
				'order' => CARDGATE_ORDER_IDEAL,
				'group' => CARDGATE_PERMISSION_IDEAL,
				'text' => TEXT_PAYMENT_CARDGATE_IDEAL,
				'img' => 'ideal' 
		),
		array (
				'name' => 'CARDGATE_KLARNA',
				'active' => CARDGATE_ACTIVATE_KLARNA,
				'order' => CARDGATE_ORDER_KLARNA,
				'group' => CARDGATE_PERMISSION_KLARNA,
				'text' => TEXT_PAYMENT_CARDGATE_KLARNA,
				'img' => 'klarna' 
		),
		array (
				'name' => 'CARDGATE_PAYPAL',
				'active' => CARDGATE_ACTIVATE_PAYPAL,
				'order' => CARDGATE_ORDER_PAYPAL,
				'group' => CARDGATE_PERMISSION_PAYPAL,
				'text' => TEXT_PAYMENT_CARDGATE_PAYPAL,
				'img' => 'paypal' 
		),
		array (
				'name' => 'CARDGATE_P24',
				'active' => CARDGATE_ACTIVATE_P24,
				'order' => CARDGATE_ORDER_P24,
				'group' => CARDGATE_PERMISSION_P24,
				'text' => TEXT_PAYMENT_CARDGATE_P24,
				'img' => 'przelewy24' 
		),
		array (
				'name' => 'CARDGATE_SOFORTUEBERWEISUNG',
				'active' => CARDGATE_ACTIVATE_SOFORTUEBERWEISUNG,
				'order' => CARDGATE_ORDER_SOFORTUEBERWEISUNG,
				'group' => CARDGATE_PERMISSION_SOFORTUEBERWEISUNG,
				'text' => TEXT_PAYMENT_CARDGATE_SOFORTUEBERWEISUNG,
				'img' => 'sofortbanking' 
		) 
);

$customer = $_SESSION ['customer'];
$customer_info = $customer->customer_info;
// get customergroup of current customer
$customer_status = $customer_info ['customers_status'];

// sort paymenttypes for view
foreach ( $cg_payments as $key => $row ) {
	if ($row ['group'] == 0 || $row ['group'] == $customer_status) {
		$order [$key] = $row ['order'];
		$name [$key] = $row ['text'];
	} else {
		// unset paymenttype for customer without permissions
		unset ( $cg_payments [$key] );
	}
}
array_multisort ( $order, SORT_ASC, $name, SORT_ASC, $cg_payments );
$tpl_data ['cardgate_payment_types'] = $cg_payments;


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

$sRootPath = dirname(dirname(dirname(dirname(__FILE__))));
require_once($sRootPath . '/cardgate/classes/cardgate.php');

class cardgatebanktransfer extends cardgate{
	function __construct() {
		parent::__construct();
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
		return parent::pspRedirect($aOrderData);
	}
}

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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

if ($this->order_data['order_data']['payment_code'] == 'paysafecard') {
    /** @global ADODB_mysql $db */
    global $db;

    $rs = $db->GetAssoc("SELECT * FROM cardgate_transaction WHERE orderid=?", array((int)$this->order_data['order_data']['orders_id']));
    if (count($rs))
    {
        $cg_data = array_pop($rs);
        if (strlen($cg_data['RESPONSEDATA']))
        {
            $blacklist = array('last_order_id');
            $info = json_decode($cg_data['RESPONSEDATA']);
            foreach ($info as $k => $v)
            {
                if (in_array($k, $blacklist))
                    continue;
                $tpl_data['order_data']['order_info_options'][] = array('text' => $k, 'value' => $v);
            }

        }
    }

//    if($rs->RecordCount()>0) {
//        print "xxx";
//        foreach ($rs as $v)
//            print_r($v);
//    }

}


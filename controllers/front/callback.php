<?php

class DotpayPaymentModuleFrontController extends ModuleFrontController
{
	public function displayAjax()
        {
                if($_SERVER['REMOTE_ADDR'] == '195.150.9.37' && $_SERVER['REQUEST_METHOD'] == 'POST') 
                {
                        if(Dotpay::check_urlc_legacy())
                        {
                                switch (Tools::getValue('t_status'))
                                {
                                    case 1:
                                        $actual_state = Configuration::get('PAYMENT_DOTPAY_NEW_STATUS');
                                        break;
                                    case 2:
                                        $actual_state = _PS_OS_PAYMENT_;
                                        break;
                                    case 3:
                                        $actual_state = _PS_OS_ERROR_;
                                        break;
                                    case 4:
                                        $actual_state = _PS_OS_ERROR_;
                                        break;
                                    case 5:
                                        $actual_state = Configuration::get('PAYMENT_DOTPAY_COMPLAINT_STATUS');   
                                    default:
                                        die ("WRONG TRANSACTION STATUS");
                                }
                                $cart = new Cart((int)Tools::getValue('control'));
                                //$address = new Address($cart->id_address_invoice);
                                $customer = new Customer((int)$cart->id_customer);
                                $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

                                if ($cart->OrderExists() == false)
                                    $this->module->validateOrder($cart->id, Configuration::get('PAYMENT_DOTPAY_NEW_STATUS'), (float)($cart->getOrderTotal(true, Cart::BOTH)), $this->module->displayName, NULL, array(), (int)$cart->id_currency, false, $customer->secure_key);
                                   
                                if ($order_id = Order::getOrderByCartId((int)Tools::getValue('control'))) 
                                {
                                        $history = new OrderHistory();
                                        $history->id_order = $order_id;
                                        $sql = 'SELECT total_paid FROM '._DB_PREFIX_.'orders WHERE id_cart = '.$cart->id.' and id_order = '.$order_id;
                                        $totalAmount = round(Db::getInstance()->getValue($sql),2);
                                        $postAmount = round(Tools::getValue('original_amount'),2);

                                        if ($toatalAmount > $postAmount) 
                                                die("INCORRECT AMOUNT $totalAmount > ".Tools::getValue('original_amount'));
                                        
                                        if ( OrderHistory::getLastOrderState($order_id) == _PS_OS_PAYMENT_ ) 
                                        {
                                                die('WRONG STATE');
                                        } else {
                                                $history->changeIdOrderState($actual_state, $order_id);
                                                $history->addWithemail(true);
                                                die ("OK");
                                        }
                                } else die('NO MATCHING ORDER');
                        } else die ("LEGACY MD5 ERROR - CHECK PIN");
                } else die("ERROR");
        }
}
<?php
class VivawalletFailModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
  
	  if(isset($_GET['s']) && $_GET['s']!=''){

	  $OrderCode = addslashes($_GET['s']);
	  
	  $check_query = "select * from vivawallet_data where OrderCode='".$OrderCode."' ORDER BY id DESC";
	  $check = Db::getInstance()->executeS($check_query, $array = true, $use_cache = 0);
	  $oid = $check[0]['ref'];
	
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active){
			
			$id_cart = (int)$oid;
			$this->context->cart = new Cart($id_cart);
			$this->context->cookie->id_cart = $id_cart;
			
			$cart = new Cart((int) $id_cart);
            if (Validate::isLoadedObject($cart)) {
                $customer = new Customer((int) $cart->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    $customer->logged = 1;
                    $this->context->customer = $customer;
                    $this->context->cookie->id_customer = (int) $customer->id;
                    $this->context->cookie->customer_lastname = $customer->lastname;
                    $this->context->cookie->customer_firstname = $customer->firstname;
                    $this->context->cookie->logged = 1;
                    $this->context->cookie->check_cgv = 1;
                    $this->context->cookie->is_guest = $customer->isGuest();
                    $this->context->cookie->passwd = $customer->passwd;
                    $this->context->cookie->email = $customer->email;
                }
            }
			
			if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active){
			 Tools::redirect('index.php?controller=order&step=1');
			}
			
		}	
			
			   $update_query = "update vivawallet_data set order_state='F' where OrderCode='".$OrderCode."'";
			   $update = Db::getInstance()->execute($update_query);
			   
			    $this->errors[] = $this->l('Transaction Failed.');
				$this->redirectWithNotifications('index.php?controller=order&step=1');
	

	}  else {
	echo 'No valid input received.';
	}   
	  
	}
}
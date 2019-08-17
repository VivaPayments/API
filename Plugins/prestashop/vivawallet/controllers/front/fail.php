<?php
class VivawalletFailModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
  
	  if(isset($_GET['s']) && $_GET['s']!=''){

	  $OrderCode = stripslashes($_GET['s']);
	  
	  $update_query = "update vivawallet_data set order_state='F' where OrderCode='".$OrderCode."'";
	  $update = Db::getInstance()->execute($update_query);
	
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
			
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');	
			
			   $update_query = "update vivawallet_data set order_state='F' where OrderCode='".$OrderCode."'";
			   $update = Db::getInstance()->execute($update_query);
			   
			    $this->errors[] = $this->l('Transaction Failed.');
				$this->redirectWithNotifications('index.php?controller=order&step=1');
	

	}  else {
	echo 'No valid input received.';
	}   
	  
	}
}
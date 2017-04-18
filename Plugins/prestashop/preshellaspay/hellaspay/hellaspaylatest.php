<?php
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class hellaspay extends PaymentModule
{
	const INSTALL_SQL_FILE = 'install.sql';
	
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		if(substr(_PS_VERSION_,2,1) > 4){
		if (empty(Context::getContext()->link))
		Context::getContext()->link = new Link();
		}
		
		$this->name = 'hellaspay';
		$this->displayName = 'Vivawallet';		
		$this->tab = 'payments_gateways';
		$this->author = 'Viva Wallet';
		$this->version = 1.7;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->is_eu_compatible = 1;

		$config = Configuration::getMultiple(array('HELLASPAY_MERCHANTID','HELLASPAY_MERCHANTPASS','HELLASPAY_SOURCE','HELLASPAY_INSTAL','HELLASPAY_CURRENCIES'));	
		
		
		
		if (isset($config['HELLASPAY_MERCHANTID']))
			$this->MerchantId = $config['HELLASPAY_MERCHANTID'];	
		if (isset($config['HELLASPAY_MERCHANTPASS']))
			$this->MerchantPass = $config['HELLASPAY_MERCHANTPASS'];	
		if (isset($config['HELLASPAY_SOURCE']))
			$this->Source = $config['HELLASPAY_SOURCE'];	
		if (isset($config['HELLASPAY_INSTAL']))
			$this->wb_instal = $config['HELLASPAY_INSTAL'];		
		if (isset($config['HELLASPAY_CURRENCIES']))
			$this->currencies = $config['HELLASPAY_CURRENCIES'];	
		
		$this->bootstrap = true;
		parent::__construct();

		$this->page = basename(preg_replace('/latest/', '', __FILE__), '.php');
		$this->description = $this->l('Accept payments with Viva Payments');
		
		if (!isset($this->MerchantId) OR !isset($this->MerchantPass))
			$this->warning = $this->l('your Viva Payments settings must be configured in order to use this module correctly');
		if (!Configuration::get('HELLASPAY_CURRENCIES'))
		{
			$currencies = Currency::getCurrencies();
			$authorized_currencies = array();
			foreach ($currencies as $currency)
				$authorized_currencies[] = $currency['id_currency'];
			Configuration::updateValue('HELLASPAY_CURRENCIES', implode(',', $authorized_currencies));
		}
	}
		

	function install()
	{
		$currencies = Currency::getCurrencies();
		$authorized_currencies = array();
		foreach ($currencies as $currency)
		$authorized_currencies[] = $currency['id_currency'];
		
		// SQL Table
		if (!file_exists(dirname(preg_replace('/latest/', '', __FILE__)).'/'.self::INSTALL_SQL_FILE))
			die('error 1');
		elseif (!$sql = file_get_contents(dirname(preg_replace('/latest/', '', __FILE__)).'/'.self::INSTALL_SQL_FILE))
			die('error 2');
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql as $query)
			if ($query AND sizeof($query) AND !Db::getInstance()->Execute(trim($query)))
				return false;
			
		//hookDisplayPaymentEU - added hook
		if (!parent::install()
			OR !Configuration::updateValue('HELLASPAY_CURRENCIES', implode(',', $authorized_currencies))
			OR !$this->registerHook('PaymentOptions')
			OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}
	
	
	
	function uninstall()
	{
		if (!Configuration::deleteByName('HELLASPAY_MERCHANTID')
			OR !Configuration::deleteByName('HELLASPAY_MERCHANTPASS')
			OR !Configuration::deleteByName('HELLASPAY_INSTAL')
		    OR !Configuration::deleteByName('HELLASPAY_SOURCE')
			OR !Configuration::deleteByName('HELLASPAY_CURRENCIES')
			OR !parent::uninstall())
			return false;
		return true;		
	}	


	function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			
			if ($wb_instal = Tools::getValue('hellaspay_wb_instal'))
				Configuration::updateValue('HELLASPAY_INSTAL', $wb_instal);	
			if ($MerchantId = Tools::getValue('hellaspay_MerchantId'))
				Configuration::updateValue('HELLASPAY_MERCHANTID', $MerchantId);
			if ($MerchantPass = Tools::getValue('hellaspay_MerchantPass'))
				Configuration::updateValue('HELLASPAY_MERCHANTPASS', $MerchantPass);
			if ($Source = Tools::getValue('hellaspay_Source'))
				Configuration::updateValue('HELLASPAY_SOURCE', $Source);
			
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= "<div class='alert error'>{$err}</div>";
		}
		else
		{
			$this->_html .= "<br />";
		}

		$this->_displayhellaspay();
		$this->_displayForm();

		return $this->_html;
	}


	function hookPaymentReturn($params)
	{
		global $smarty, $cart, $cookie;
		
		$check = Db::getInstance()->executeS('SELECT transaction_id FROM '._DB_PREFIX_.'order_payment WHERE order_reference ="'.$params['order']->reference.'"');
		 
		 $transaction_id = '';
		 if ($check){
		   $transaction_id = $check[0]['transaction_id'];
		 } 
		 
   		$currency = $this->context->currency;
		
		$state = $params['order']->getCurrentState();
		if ($state == _PS_OS_OUTOFSTOCK_ or $state == _PS_OS_PAYMENT_)
			$smarty->assign(array( 
				'total_to_pay' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
				'status' 		=> 'ok',
				'storename' 	=> $this->context->shop->name,
				'shop_name' 	=> $this->context->shop->name,
				'id_order' 		=> $params['order']->id
			));
		else
			$smarty->assign('status', 'failed');

		return $this->display(preg_replace('/latest/', '', __FILE__), 'payment_return.tpl');
	}


	function hookPaymentOptions($params)
	{
		
		if (!$this->active) {
            return;
        }
		
		global $smarty, $cart, $cookie;
		
		$currency = Currency::getCurrencyInstance($cart->id_currency);
		if ($currency->iso_code != 'EUR')
		return false;
		
		$delivery = new Address(intval($cart->id_address_delivery));
		$invoice = new Address(intval($cart->id_address_invoice));
		$customer = new Customer(intval($cart->id_customer));		
		
		$id_eur_currency = 0; // EUR currency ID
    	$id_dest_currency = -1;

		$currencies = $this->getCurrency((int)$cart->id_currency);
		$authorized_currencies = array_flip(explode(',', $this->currencies));
        $currencies_used = array();

			
         foreach ($currencies as $key => $currency) {
         if (isset($authorized_currencies[$currency['id_currency']])) {
                    $currencies_used[] = $currencies[$key];    
         if ($currency['iso_code'] == 'EUR') {
            $id_eur_currency = $currency['id_currency'];
          }
          if ($currency['id_currency'] == $cart->id_currency) {
            $id_dest_currency = $cart->id_currency;
          }
        }
      }
      $smarty->assign('currencies_used',$currencies_used);

      if ($id_dest_currency < 0) $id_dest_currency = $id_eur_currency;

      $dest_currency = Currency::getCurrency(intval($id_dest_currency));
	  
      	$currency_symbol ='';
		$language_code ='';
		$eb_total ='';
		$ebfullname ='';
		
		switch ($dest_currency['iso_code']) {
		case 'EUR':
   		$currency_symbol = 978;
   		break;
		case 'USD':
   		$currency_symbol = 840;
   		break;
		case 'GBP':
   		$currency_symbol = 826;
   		break;
		default:
        $currency_symbol = 978;
		}      
			
		$currency = new Currency((int)($cart->id_currency));
		$amount = $cart->getOrderTotal(true, Cart::BOTH);
		$id_currency_max = $id_dest_currency;

		if ($currency->id != $id_currency_max)
		{
			$amount = $amount / $currency->conversion_rate;
			$amount = Tools::convertPrice($amount, new Currency((int)($id_currency_max)));
			//$cart->id_currency = $id_currency_max;
		}	
		
		$wb_total = number_format($amount, 2, '.', '');		
		$wb_instal_total = round($amount);
		
		$wb_total_cents = number_format($amount, 2, '.', '')*100;		
		$wb_total_cents = round($wb_total_cents);

            $products = $cart->getProducts();
			
				
			foreach ($products as $key => $product)
			{
				$products[$key]['name'] = str_replace('"', '\'', $product['name']);
				$products[$key]['name'] = htmlentities(utf8_decode($product['name']));
			}									
					
	if(strtolower(Language::getIsoById($cookie->id_lang))=='el' || strtolower(Language::getIsoById($cookie->id_lang))=='gr'){
	$languagecode ='el-GR';
	} else {
	$languagecode ='en-US';
	}
	
	$MerchantID =  Configuration::get('HELLASPAY_MERCHANTID');
	$Password =   html_entity_decode(Configuration::get('HELLASPAY_MERCHANTPASS'));
	
	$poststring['Amount'] = $wb_total_cents;
	$poststring['RequestLang'] = $languagecode;
	$poststring['Email'] = $customer->email;
	
	$poststring['MerchantTrns'] = $cart->id;
	$poststring['SourceCode'] = Configuration::get('HELLASPAY_SOURCE');
	$poststring['PaymentTimeOut'] = '300';	
	$TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	
	$charge = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
	
	$maxperiod = '1';
	 $installogic = Configuration::get('HELLASPAY_INSTAL');
	 if(isset($installogic) && $installogic!=''){
	 $split_instal_nbghps = explode(',',$installogic);
	 $c = count($split_instal_nbghps);	
	 $instal_nbghps_max = array();
	 for($i=0; $i<$c; $i++){
		list($instal_amount, $instal_term) = explode(":", $split_instal_nbghps[$i]);
			if($charge >= $instal_amount){
			$instal_nbghps_max[] = trim($instal_term);
			}
		}
		if(count($instal_nbghps_max) > 0){
		 $maxperiod = max($instal_nbghps_max);
		}
	}
	
	$curl = curl_init("https://www.vivapayments.com/api/orders");
	curl_setopt($curl, CURLOPT_PORT, 443);
	
	$postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($maxperiod).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&PaymentTimeOut=300&DisableIVR=true';
	
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password); 
	$curlversion = curl_version();
	if(!preg_match("/NSS/" , $curlversion['ssl_version'])){
	curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, "TLSv1");
	}
	
	// execute curl
	$response = curl_exec($curl);
		
	if(curl_error($curl)){
	curl_setopt($curl, CURLOPT_PORT, 443);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $MerchantID.':'.$Password);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($curl);
	}
	
		curl_close($curl);
		
		try {
		if (version_compare(PHP_VERSION, '5.3.99', '>=')) {
		$resultObj=json_decode($response, false, 512, JSON_BIGINT_AS_STRING);
		} else {
		$response = preg_replace('/:\s*(\-?\d+(\.\d+)?([e|E][\-|\+]\d+)?)/', ': "$1"', $response, 1);
		$resultObj = json_decode($response);
		}
		} catch( Exception $e ) {
			throw new Exception("Result is not a json object (" . $e->getMessage() . ")");
		}
		
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
		$OrderCode = $resultObj->OrderCode;
		$ErrorCode = $resultObj->ErrorCode;
		$ErrorText = $resultObj->ErrorText;
		}
		else{
			throw new Exception("Unable to create order code (" . $resultObj->ErrorText . ")");
		}

	if (version_compare(_PS_VERSION_, '1.5', '<')){
	$seckey = $customer->secure_key;
	} else {
	$seckey = Context::getContext()->customer->secure_key;
	}
	
	$tmquery = "insert into hellaspay_data (secure_key, OrderCode, ErrorCode, ErrorText, Timestamp, ref, total_cost, currency, order_state) values ('".$seckey."','".$OrderCode."','".$ErrorCode."','".$ErrorText."',now(),'".$cart->id."','".$wb_total_cents."','978','I')";
	Db::getInstance()->execute($tmquery); //tommodps15
	
	$this->HellaspayUrl = 'https://www.vivapayments.com/web/newtransaction.aspx';
		
		$post_variables = Array(
		'HellaspayUrl' 		=> $this->HellaspayUrl,
		'Ref' 				=> $OrderCode);
		
		$formpost = '';
		foreach ($post_variables as $name => $value) {
			$formpost.= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
		}
		
		$logo = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/hellaspay.gif');

        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->trans('Pay by Viva', array(), 'Modules.Hellaspay.Admin'))
		->setLogo($logo)
		->setForm('<form id="hellaspay_confirmation_form" name="hellaspay_confirmation" data-ajax="false" action="'.Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__.'modules/hellaspay/pay.php" method="post">'.$formpost.'</form>');

        return [$newOption];

	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['hellaspay_MerchantId']))
				$this->_postErrors[] = $this->l('Your MerchantId is required.');	
			if (empty($_POST['hellaspay_MerchantPass']))
				$this->_postErrors[] = $this->l('Your MerchantPass is required.');	
		}
		elseif (isset($_POST['currenciesSubmit']))
		{
			$currencies = Currency::getCurrencies();
			$authorized_currencies = array();
			foreach ($currencies as $currency)
				if (isset($_POST['currency_'.$currency['id_currency']]) AND $_POST['currency_'.$currency['id_currency']])
					$authorized_currencies[] = $currency['id_currency'];
			if (!sizeof($authorized_currencies))
				$this->_postErrors[] = $this->l('at least one currency is required.');
		}
	}

	private function _postProcess()
	{
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('HELLASPAY_MERCHANTID', trim($_POST['hellaspay_MerchantId']));
			Configuration::updateValue('HELLASPAY_MERCHANTPASS', trim($_POST['hellaspay_MerchantPass']));
			Configuration::updateValue('HELLASPAY_INSTAL', trim($_POST['hellaspay_wb_instal']));
			Configuration::updateValue('HELLASPAY_SOURCE', trim($_POST['hellaspay_Source']));
		}
		elseif (isset($_POST['currenciesSubmit']))
		{
			$currencies = Currency::getCurrencies();
			$authorized_currencies = array();
			foreach ($currencies as $currency)
				if (isset($_POST['currency_'.$currency['id_currency']]) AND $_POST['currency_'.$currency['id_currency']])
					$authorized_currencies[] = $currency['id_currency'];
			Configuration::updateValue('HELLASPAY_CURRENCIES', implode(',', $authorized_currencies));
		}
		$ok = $this->l('Ok');
		$updated = $this->l('Settings Updated');
		$this->_html .= "<div class='conf confirm'><img src='../modules/hellaspay/ok.gif' alt='{$ok}' />{$updated}</div>";
	}
	
	private function _displayhellaspay()
	{
		$modDesc 	= $this->l('This module allows you to accept payments using Viva Payments.');
		$modStatus	= $this->l('Viva Payments online banking service could be the right solution for you');
		$modconfirm	= $this->l('');
		$this->_html .= "<img src='../modules/hellaspay/hellaspay.gif' style='float:left; margin-right:15px;' />
						<b>{$modDesc}</b>
						<br />
						{$modconfirm}
						<br />
						<br />
						<br />";
	}




	private function _displayForm()
	{
		$modhellaspay			= $this->l('Viva Payments Setup');
		$modhellaspayDesc		= $this->l('Please specify the gateway settings');	
		$modInstalLabel			= $this->l('Instalment logic');
		$modInstalDescription	= $this->l('Instalment logic example: 300:3,600:6 -> Order total 300 euro: allow 3 instalments, order total 600: allow 3 and 6 instalments.');
		$modMerchantId		= $this->l('MerchantId');
		$modMerchantPass		= $this->l('API Key');
		$modSource			= $this->l('Source Code');

		$modCurrencies				= $this->l('Currencies');
		$modUpdateSettings 			= $this->l('Update settings');
		$modCurrenciesDescription	=$this->l('Currencies authorized for Viva Payments payment. At the moment Viva Payments only accepts Euro!!!');
		$modAuthorizedCurrencies	= $this->l('Authorized currencies');		
		
		$this->_html .=
		"<form action='{$_SERVER['REQUEST_URI']}' method='post'>
			<fieldset>
			<legend><img src='../modules/hellaspay/access.png' />{$modhellaspay}</legend>
				<table border='0' width='500' cellpadding='0' cellspacing='0' id='form'>
					<tr>
						<td colspan='2'>
							{$modhellaspayDesc}<br /><br />
						</td>
					</tr>";
					
	$this->_html .="<tr>
						<td colspan='2'>
							{$modInstalDescription}
							<br />
							<br />
						</td>
					</tr><tr>
						<td width='130'>{$modInstalLabel}<br /><br /></td>
						<td>";
	$this->_html .= '<input type="text" name="hellaspay_wb_instal" value="'.Tools::getValue('hellaspay_wb_instal', Configuration::get('HELLASPAY_INSTAL')).'" />';
											
	$this->_html .= "<br /><br /></td>
					</tr>";					
					
	$this->_html .="<tr>
						<td width='130'>{$modMerchantId}<br /><br /></td>
						<td>";
	$this->_html .= '<input type="text" name="hellaspay_MerchantId" value="'.Tools::getValue('hellaspay_MerchantId', Configuration::get('HELLASPAY_MERCHANTID')).'" />';
	$this->_html .= "<br /><br /></td>
					</tr>";	
	$this->_html .="<tr>
						<td width='130'>{$modMerchantPass}<br /><br /></td>
						<td>";
	$this->_html .= '<input type="text" name="hellaspay_MerchantPass" value="'.Tools::getValue('hellaspay_MerchantPass', Configuration::get('HELLASPAY_MERCHANTPASS')).'" />';
	$this->_html .= "<br /><br /></td>
					</tr>";	
									
	$this->_html .="<tr>
						<td width='130'>{$modSource}<br /><br /></td>
						<td>";
	$this->_html .= '<input type="text" name="hellaspay_Source" value="'.Tools::getValue('hellaspay_Source', Configuration::get('HELLASPAY_SOURCE')).'" />';
	$this->_html .= "<br /><br /></td>
					</tr>";		
					

	$this->_html .= "<tr>
						<td colspan='2' align='center'>
							<input class='button' name='btnSubmit' value='{$modUpdateSettings}' type='submit' />
						</td>
					</tr>
				</table>
			</fieldset>
		</form>
		<br />
		<br />
		<form action='{$_SERVER['REQUEST_URI']}' method='post'>
			<fieldset>
			<legend><img src='../modules/hellaspay/dollar.gif' />{$modAuthorizedCurrencies}</legend>
				<table border='0' width='500' cellpadding='0' cellspacing='0' id='form'>
					<tr>
						<td colspan='2'>
							{$modCurrenciesDescription}
							<br />
							<br />
						</td>
					</tr>			
					<tr>
						<td width='130' style='height: 35px; vertical-align:top'>{$modCurrencies}</td>
						<td>";
			$currencies = Currency::getCurrencies();
			$authorized_currencies = array_flip(explode(',', Configuration::get('HELLASPAY_CURRENCIES')));
			foreach ($currencies as $currency)
				$this->_html .= '<label style="float:none; "><input type="checkbox" value="true" name="currency_'.$currency['id_currency'].'"'.(isset($authorized_currencies[$currency['id_currency']]) ? ' checked="checked"' : '').' />&nbsp;<span style="font-weight:bold;">'.$currency['name'].'</span> ('.$currency['sign'].')</label><br />';
				$this->_html .="
						</td>
					</tr>					
					<tr>
						<td colspan='2' align='center'>
							<br />
							<input class='button' name='currenciesSubmit' value='{$modUpdateSettings}' type='submit' />
						</td>
					</tr>
				</table>
			</fieldset>
		</form>";
	}
}

?>
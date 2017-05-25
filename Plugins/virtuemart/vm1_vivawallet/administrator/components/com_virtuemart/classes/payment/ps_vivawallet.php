<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*@Vivawallet Payment Gateway
* @version : ps_vivawallet.php 2013-01-11 Created By Viva Wallet
*/

class ps_vivawallet {
    var $classname = "ps_vivawallet";
    var $payment_code = "Vivawallet";
    /**
    * Show all configuration parameters for this payment method
    * @returns boolean False when the Payment method has no configration
    */
    function show_configuration() {
        global $VM_LANG;
        $db = new ps_DB();
        
        /** Read current Configuration ***/
        include_once(CLASSPATH ."payment/".$this->classname.".cfg.php");
    ?>
<SCRIPT type="text/javascript">
function CopyAndPaste( from, to )
{
		document.getElementsByName(to)[0].value = document.getElementsByName(from)[0].value;
}
</SCRIPT>
    <table>
        <tr>
        <td width="200"><strong>Merchant ID</strong></td>
            <td width="305"><input type="text" name="VIVAWALLET_ID" class="inputbox" value="<?php  echo VIVAWALLET_ID; ?>" /></td>
            <td width="300">The Merchant ID for Vivawallet. </td>
			</tr>
         <tr>
        <td width="200"><strong>API Key</strong></td>
            <td width="305"><input type="text" name="VIVAWALLET_PASS" class="inputbox" value="<?php  echo VIVAWALLET_PASS; ?>" /></td>
            <td width="300"></td>
			</tr> 
         <tr>
        <td width="200"><strong>Source</strong></td>
            <td width="305"><input type="text" name="VIVAWALLET_SOURCE" class="inputbox" value="<?php  echo VIVAWALLET_SOURCE; ?>" /></td>
            <td width="300">Vivawallet Source code. </td>
			</tr>   
			<tr>
		      <td><strong>Form Vivawallet</strong></td>
            <td colspan="2">
<textarea name="VIVAWALLET_FORM" cols="80" rows="15" readonly="readonly" STYLE="display:none;">
<?php echo "<?php\n"; ?>

$tax_total = $db->f("order_tax") + $db->f("order_shipping_tax");
$discount_total = $db->f("coupon_discount") + $db->f("order_discount");

$vivawallet_amount = number_format($db->f("order_total"), 2, '.', '') * 100;

$auth = $_SESSION['auth'];
 // Get user billing information
        $dbbt = new ps_DB;
        $qt = "SELECT * FROM `#__{vm}_user_info` WHERE user_id='".$auth["user_id"]."' AND address_type='BT'";
        $dbbt->query($qt);
        $dbbt->next_record();
        $user_info_id = $dbbt->f("user_info_id");
        if( $user_info_id != $d["ship_to_info_id"]) {
            // Get user billing information
            $dbst = new ps_DB;
            $qt = "SELECT * FROM #__{vm}_user_info WHERE user_info_id='".$d["ship_to_info_id"]."' AND address_type='ST'";
            $dbst->query($qt);
            $dbst->next_record();
        }
        else {
            $dbst = $dbbt;
        }
        
	if($dbbt->f("email")==''){
    $email = $dbbt->f("user_email");
    } else {
    $email = $dbbt->f("email");
    }

    $TmSecureKey = 'd2ViaXQuYnovbGljZW5zZS50eHQ='; // for extra encryption options
	$poststring['Amount'] = $vivawallet_amount;
	$poststring['RequestLang'] = 'el-GR';
	$poststring['Email'] = $email;
	$poststring['MerchantTrns'] = $db->f("order_id");
	$poststring['SourceCode'] = VIVAWALLET_SOURCE;
	$poststring['PaymentTimeOut'] = '300';
    
    $Installments = 1;
    
    $postargs = 'Amount='.urlencode($poststring['Amount']).'&RequestLang='.urlencode($poststring['RequestLang']).'&Email='.urlencode($poststring['Email']).'&MaxInstallments='.urlencode($Installments).'&MerchantTrns='.urlencode($poststring['MerchantTrns']).'&SourceCode='.urlencode($poststring['SourceCode']).'&PaymentTimeOut=300';

		$curl = curl_init("https://www.vivapayments.com/api/orders");
		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, VIVAWALLET_ID.':'.html_entity_decode(VIVAWALLET_PASS));
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
        curl_setopt($curl, CURLOPT_USERPWD, VIVAWALLET_ID.':'.html_entity_decode(VIVAWALLET_PASS));
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
    
     $qt = "UPDATE `#__{vm}_order_payment` SET order_payment_trans_id='".$OrderCode."' WHERE order_id='".$db->f("order_id")."'";
     $dbbt->query($qt);

echo '<form action="https://www.vivapayments.com/web/newtransaction.aspx" method="get">';
echo '<input type="hidden" name="Ref" value="'.$OrderCode.'" />';
echo '<input type="image" name="submit" src="'.SECUREURL .'components/com_virtuemart/shop_image/ps_image/vivawallet.jpg" border="0"/>';
echo '</form>';
?>
</textarea>
<BUTTON onClick="CopyAndPaste('VIVAWALLET_FORM', 'payment_extrainfo')">
Copy and Paste</BUTTON>            </td>
        </tr>
      </table>

    <?php
    }
    
    function has_configuration() {
      // return false if there's no configuration
      return true;
   }
   
  /**
	* Returns the "is_writeable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
   function configfile_writeable() {
      return is_writeable( CLASSPATH."payment/".$this->classname.".cfg.php" );
   }
   
  /**
	* Returns the "is_readable" status of the configuration file
	* @param void
	* @returns boolean True when the configuration file is writeable, false when not
	*/
   function configfile_readable() {
      return is_readable( CLASSPATH."payment/".$this->classname.".cfg.php" );
   }
   
  /**
	* Writes the configuration file for this payment method
	* @param array An array of objects
	* @returns boolean True when writing was successful
	*/
   function write_configuration( &$d ) {
      
      $my_config_array = array(
				  "VIVAWALLET_ID" => $d['VIVAWALLET_ID'],
		  		  "VIVAWALLET_PASS" => $d['VIVAWALLET_PASS'],
				  "VIVAWALLET_SOURCE" => $d['VIVAWALLET_SOURCE']
				  );
      $config = "<?php\n";
      $config .= "if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ; \n\n";
      foreach( $my_config_array as $key => $value ) {
        $config .= "define ('$key', '$value');\n";
      }
      
      $config .= "?>";
  
      if ($fp = fopen(CLASSPATH ."payment/".$this->classname.".cfg.php", "w")) {
          fputs($fp, $config, strlen($config));
          fclose ($fp);
          return true;
     }
     else
        return false;
   }
   
  /**************************************************************************
  ** name: process_payment()
  ** returns: 
  ***************************************************************************/
   function process_payment($order_number, $order_total, &$d) {
   global $vendor_currency;
   
   if($vendor_currency!='EUR'){
   $displayMsg = 'Only Euro currency supported.';
   echo $displayMsg;
	$d["error"] = $displayMsg;
	$vmLogger->err($displayMsg);
   return false;
   } else {
   return true;
   }
  }
   
}
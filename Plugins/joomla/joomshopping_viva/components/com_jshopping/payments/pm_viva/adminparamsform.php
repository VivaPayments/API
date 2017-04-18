<?php
/**
* @version      4.3.1 02.12.2013
* @author       Viva Wallet
* @package      Jshopping
* @copyright    Copyright (C) 2013 vivawallet.com All rights reserved.
* @license      -
*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td style="width:250px;" class="key">
     <?php echo _JSHOP_VIVA_MERCHANT_ID;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[merchantid]" size="45" value = "<?php echo $params['merchantid']?>" />
     <?php echo JHTML::tooltip(_JSHOP_VIVA_MERCHANT_ID_DESCRIPTION);?>
   </td>
 </tr>
 <tr>
   <td  class="key">
     <?php echo _JSHOP_VIVA_MERCHANT_PASS;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[merchantpass]" size="45" value = "<?php echo $params['merchantpass']?>" />
     <?php echo JHTML::tooltip(_JSHOP_VIVA_MERCHANT_PASS_DESCRIPTION);?>
   </td>
 </tr> 
 <tr>
   <td  class="key">
     <?php echo _JSHOP_VIVA_MERCHANT_SOURCE;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[merchantsource]" size="45" value = "<?php echo $params['merchantsource']?>" />
     <?php echo JHTML::tooltip(_JSHOP_VIVA_MERCHANT_SOURCE_DESCRIPTION);?>
   </td>
 </tr> 
 <tr>
   <td  class="key">
     <?php echo _JSHOP_VIVA_INSTALL;?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[install]" size="45" value = "<?php echo $params['install']?>" />
     <?php echo JHTML::tooltip(_JSHOP_VIVA_INSTALL_DESCRIPTION);?>
   </td>
 </tr> 
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_END;?>
   </td>
   <td>
     <?php              
     print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_END_DESCRIPTION);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_PENDING;?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_PENDING_DESCRIPTION);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_FAILED;?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_FAILED_DESCRIPTION);
     ?>
   </td>
 </tr>
</table>
</fieldset>
</div>
<div class="clr"></div>
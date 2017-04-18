<?php
/**
 * @package	HikaShop for Joomla!
 * @version	1.0.0
 * @author	Viva Wallet
 * @copyright	(C) 2013 Viva Wallet. All rights reserved.
 * @license	-
 */
defined('_JEXEC') or die('Restricted access');
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchantid]">
			<?php echo JText::_( 'Source Code' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantid]" value="<?php echo @$this->element->payment_params->merchantid; ?>" />
	</td>
</tr>
<tr>
	<td class="key" valign="top">
		<label for="data[payment][payment_params][merchantidloc]">
			<?php echo JText::_( 'Source Code Locale' ); ?>
		</label>
	</td>
	<td valign="top">
		<input type="text" name="data[payment][payment_params][merchantidloc]" value="<?php echo @$this->element->payment_params->merchantidloc; ?>" /><br />Locale used for this source code, like <strong>el-GR</strong><br />Leave empty in case 1 language is used.
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchantidsec]">
			<?php echo JText::_( 'Secondary Source Code' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantidsec]" value="<?php echo @$this->element->payment_params->merchantidsec; ?>" /><br />Used for this secondary language<br />Leave empty in case 1 language is used.
	</td>
</tr>
<tr>
	<td class="key" valign="top">
		<label for="data[payment][payment_params][merchantidsecloc]">
			<?php echo JText::_( 'Secondary Source Code Locale' ); ?>
		</label>
	</td>
	<td valign="top">
		<input type="text" name="data[payment][payment_params][merchantidsecloc]" value="<?php echo @$this->element->payment_params->merchantidsecloc; ?>" /><br />Locale used for this secondary source code, like <strong>en-GB</strong><br />Leave empty in case 1 language is used.
	</td>
</tr>
<tr>
	<td class="key" valign="top">
		<label for="data[payment][payment_params][user]">
			<?php echo JText::_( 'Merchant ID' ); ?>
		</label>
	</td>
	<td valign="top">
		<input type="text" name="data[payment][payment_params][user]" value="<?php echo @$this->element->payment_params->user; ?>" />
	</td>
</tr>
<tr>
	<td class="key" valign="top">
		<label for="data[payment][payment_params][pass]">
			<?php echo JText::_( 'API Key' ); ?>
		</label>
	</td>
	<td valign="top">
		<input type="text" name="data[payment][payment_params][pass]" value="<?php echo @$this->element->payment_params->pass; ?>" />
	</td>
</tr>
<tr>
	<td class="key" valign="top">
		<label for="data[payment][payment_params][instal]">
			<?php echo JText::_( 'Instalment Logic' ); ?>
		</label>
	</td>
	<td valign="top">
		<input type="text" name="data[payment][payment_params][instal]" value="<?php echo @$this->element->payment_params->instal; ?>" />
        <br />Example: <strong>90:3,180:6</strong>
        <br />Explained: 90 euro order total->allow 0 and 3 instalments, 180 euro order total->allow 0, 3 and 6 instalments
        <br />Leave empty to disable instalments
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>
<tr>
	<td colspan="2">
    	<strong>Configure your Source(s) with following URLs:</strong><br /><br /> 
        <strong>Success URL:</strong><br /><?php echo htmlspecialchars('index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=viva&tmpl=component&vivok'); ?><br /><br />
        <strong>Fail URL:</strong><br /><?php echo htmlspecialchars('index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=viva&tmpl=component&vivfail'); ?><br /><br />
        <strong>Note:</strong> In case your URLs contain a language parameter, place this parameter also in the Shop URL and use the secondary source setup if needed. Make sure that no spaces occur in the URLs when copying them.
    </td>
</tr>
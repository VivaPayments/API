<?php
/**
 * @package	HikaShop for Joomla!
 * @version	1.0.0
 * @author	Viva Wallet
 * @copyright	(C) 2013 Viva Wallet. All rights reserved.
 * @license	-
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_viva_end" id="hikashop_viva_end">
	<span id="hikashop_viva_end_message" class="hikashop_viva_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_viva_end_spinner" class="hikashop_viva_end_spinner hikashop_checkout_end_spinner">
	</span>
	<br/>
	<form id="hikashop_viva_form" name="hikashop_viva_form" action="<?php echo $plg_viva_url;?>" method="get">
		<div id="hikashop_viva_end_image" class="hikashop_viva_end_image">
			<input id="hikashop_viva_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.(string)$value.'" />';
			}
			JRequest::setVar('noform',1); ?>
	</form>
	<script type="text/javascript">
		<!--
		document.getElementById('hikashop_viva_form').submit();
		//-->
	</script>
</div>

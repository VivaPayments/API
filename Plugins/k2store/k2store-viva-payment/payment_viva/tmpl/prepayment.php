<?php defined('_JEXEC') or die('Restricted access'); ?>
<form method="get" action="<?php echo $vars->form_url; ?>">
    <input type="hidden" name="Ref" value="<?php echo $vars->OrderCode;?>" />
    <input type="submit" class="btn btn-primary button" value="<?php echo JText::_('PLG_VIVA_PAYNOW'); ?>" />
</form>
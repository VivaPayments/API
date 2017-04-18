<p class="payment_module clearfix">
		<span style="float:left;"><img src="{$module_dir}hellaspay.gif" alt="{l s='Pay by Viva Payments' mod='hellaspay'}"/></span>
		<span>{l s='Pay with Viva Payments' mod='hellaspay'}<br />{l s='Pay safely and quickly on the next page' mod='hellaspay'}</span>
</p>
<form name="hellaspay_confirmation" action="{$base_dir_ssl}{$InstalmentUrl}" method="post">
    {$WbInstalLogic}
</form>
        
<form name="hellaspay_confirmation" action="{$HellaspayUrl}" method="get">
    <input type="hidden" name="Ref" value="{$Ref}" />
    <input style="{$WbSubmit}" type="submit" value="{l s='Pay Now' mod='hellaspay'}" class="exclusive_large" />
</form>
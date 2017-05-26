<p class="payment_module clearfix">
		<span style="float:left;"><img src="{$module_dir}vivawallet.gif" alt="{l s='Pay by Vivawallet' mod='vivawallet'}"/></span>
		<span>{l s='Pay with Vivawallet' mod='vivawallet'}<br />{l s='Pay safely and quickly on the next page' mod='vivawallet'}</span>
</p>
<form name="vivawallet_confirmation" action="{$base_dir_ssl}{$InstalmentUrl}" method="post">
    {$WbInstalLogic}
</form>
        
<form name="vivawallet_confirmation" action="{$VivawalletUrl}" method="get">
    <input type="hidden" name="Ref" value="{$Ref}" />
    <input style="{$WbSubmit}" type="submit" value="{l s='Pay Now' mod='vivawallet'}" class="exclusive_large" />
</form>
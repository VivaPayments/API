{capture name=path}{l s='Shipping'}{/capture}

    <div id="cms_block">
	<h2>{l s='Vivawallet Payment Order Summary' mod='vivawallet'}</h2>
    {l s='Buy On-Line with Credit or Debit Card via Vivawallet.' mod='vivawallet'}
    </div>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}



<p>
	<img src="{$this_path}vivawallet.gif" alt="{l s='Vivawallet' mod='vivawallet'}" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Vivawallet.' mod='vivawallet'}
</p>
<p>
	<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='vivawallet'}.</b>
</p>


<p align="right"><a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='vivawallet'}</a></p>

<form name="vivawallet_confirmation" action="{$this_path}payment.php" method="post" />
    {$WbInstalLogic}
</form>
        
<form name="vivawallet_confirmation" action="{$VivawalletUrl}" method="get" />
    <input type="hidden" name="Ref" value="{$Ref}" />
    <input style="{$WbSubmit}" type="submit" value="{l s='I confirm my order' mod='vivawallet'}" class="exclusive_large" />
</form>
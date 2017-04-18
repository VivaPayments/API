{capture name=path}{l s='Shipping'}{/capture}

    <div id="cms_block">
	<h2>{l s='Viva Payments Payment Order Summary' mod='hellaspay'}</h2>
    {l s='Buy On-Line with Credit or Debit Card via Viva Payments.' mod='hellaspay'}
    </div>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}



<p>
	<img src="{$this_path}hellaspay.gif" alt="{l s='Viva Payments' mod='hellaspay'}" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Viva Payments.' mod='hellaspay'}
</p>
<p>
	<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='hellaspay'}.</b>
</p>


<p align="right"><a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='hellaspay'}</a></p>

<form name="hellaspay_confirmation" action="{$this_path}payment.php" method="post" />
    {$WbInstalLogic}
</form>
        
<form name="hellaspay_confirmation" action="{$HellaspayUrl}" method="get" />
    <input type="hidden" name="Ref" value="{$Ref}" />
    <input style="{$WbSubmit}" type="submit" value="{l s='I confirm my order' mod='hellaspay'}" class="exclusive_large" />
</form>
{* $Id: hellaspay.php 11696 2012-12-12 09:30:02Z vivawallet $ *}

{assign var="payment_info" value=$payment_id|fn_get_payment_method_data}

{if $payment_method.params.step && $cart.total>$payment_method.params.step}
{math assign="installments" equation="floor(x/y)+z" x=$cart.total y=$payment_method.params.step z=$payment_method.params.start_installments}

{if $installments}
<div class="form-field">
	<label for="cc_instalments">{$lang.hellaspay_instalments}:</label>
	<select id="cc_instalments" name="payment_info[installments]">
		<option value="0" {if $cart.payment_info.installments == 1}selected="selected"{/if}>{$lang.hellaspay_no_instalments}</option>
		{section name=foo start=$payment_method.params.start_installments loop=$installments step=1}
		{if !($payment_method.params.installments && $smarty.section.foo.index>$payment_method.params.installments)}
			<option value="{$smarty.section.foo.index}" {if $cart.payment_method.installments == $smarty.section.foo.index}selected="selected"{/if}>{$smarty.section.foo.index} {$lang.hellaspay_instalments}</option>
		{/if}
		{/section}
	</select>
</div>
{/if}
{/if}
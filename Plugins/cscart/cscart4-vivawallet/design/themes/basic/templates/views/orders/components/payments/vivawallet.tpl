{if $payment_method.processor_params.step && $cart.total>$payment_method.processor_params.step}
{math assign="installments" equation="floor(x/y)+z" x=$cart.total y=$payment_method.processor_params.step z=$payment_method.processor_params.start_installments}

{if $installments}
<div class="control-group">
	<label for="cc_instalments" class="control-label">{$payment_method.payment} {__("vivawallet_instalments")}:</label>
	<select id="cc_instalments" name="payment_info[installments]">
		<option value="0" {if $cart.payment_info.installments == 1}selected="selected"{/if}>{__("vivawallet_no_instalments")}</option>
		{section name=foo start=$payment_method.processor_params.start_installments loop=$installments step=1}
		{if !($payment_method.processor_params.installments && $smarty.section.foo.index>$payment_method.processor_params.installments)}
			<option value="{$smarty.section.foo.index}" {if $cart.payment_method.installments == $smarty.section.foo.index}selected="selected"{/if}>{$smarty.section.foo.index} {__("vivawallet_instalments")} ({$smarty.section.foo.index} x {number_format($cart.total / $smarty.section.foo.index, 2, '.', '')})</option>
		{/if}
		{/section}
	</select>
</div>
{/if}
{/if}
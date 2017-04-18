<script>
function submyform() {
        document.getElementById("hellaspay_payment_form").submit();
}
</script>

<p class="payment_module">
	<a title="{l s='Pay with Viva Payments' mod='hellaspay'}" class="bankwire" href="javascript:submyform()" rel="nofollow">
		<img src="{$module_dir}hellaspay.gif" alt="{l s='Pay with Viva Payments' mod='hellaspay'}" style="float:left;" />
		<br />{l s='Pay with Viva Payments' mod='hellaspay'}
		<br />{l s='Pay safely and quickly on the next page' mod='hellaspay'}
		<br style="clear:both;" />
	</a>
</p>

<form name="hellaspay_confirmation" action="{$HellaspayUrl}" method="get" id="hellaspay_payment_form">
    <input type="hidden" name="Ref" value="{$Ref}" />
</form>
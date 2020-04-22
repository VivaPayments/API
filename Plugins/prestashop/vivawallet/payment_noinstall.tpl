<script>
function submyform() {
        document.getElementById("vivawallet_payment_form").submit();
}
</script>

<p class="payment_module">
	<a title="{l s='Pay with Vivawallet' mod='vivawallet'}" class="bankwire" href="javascript:submyform()" rel="nofollow">
		<img src="{$module_dir}vivawallet.gif" alt="{l s='Pay with Vivawallet' mod='vivawallet'}" style="float:left;" />
		<br />{l s='Pay with Vivawallet' mod='vivawallet'}
		<br />{l s='Pay safely and quickly on the next page' mod='vivawallet'}
		<br style="clear:both;" />
	</a>
</p>

<form name="vivawallet_confirmation" action="{$VivawalletUrl}" method="get" id="vivawallet_payment_form">
    <input type="hidden" name="Ref" value="{$Ref}" />
</form>
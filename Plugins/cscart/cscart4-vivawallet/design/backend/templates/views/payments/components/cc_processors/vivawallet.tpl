{assign var="success_url" value="payment_notification.success?payment=vivawallet"|fn_url:'C':'http'}
{assign var="failure_url" value="payment_notification.fail?payment=vivawallet"|fn_url:'C':'http'}
<p>{__("text_vivawallet_notice", ["[success_url]" => $success_url, "[failure_url]" => $failure_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchant_id">Merchant ID:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">API Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="source">Source Code:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][source]" id="source" value="{$processor_params.source}"  size="20">
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="details">Instalments:</label>
	<div class="controls">
    <input type="text" name="payment_data[processor_params][installments]" id="installments" 
				value="{$processor_params.installments}" size="60" maxlength="2">
<br><em>Example: 12 - maximum of 12 instalments can be selected</em>
</div>
</div>

<div class="control-group">
	<label class="control-label" for="details">Instalment steps:</label>
	<div class="controls">
    <input type="text" name="payment_data[processor_params][step]" id="installments" 
				value="{$processor_params.step}" size="60" maxlength="6">
<br><em>Example: 30 - for every 30 euro an instalment option will be available</em>                
</div>
</div>

<div class="control-group">
	<label class="control-label" for="details">First instalment:</label>
	<div class="controls">
    <input type="text" name="payment_data[processor_params][start_installments]" id="installments" 
				value="{$processor_params.start_installments}" size="60" maxlength="2">
<br><em>Example: 3 - first instalment option will be 3</em>                
</div>
</div>

<div class="control-group">
	<label class="control-label" for="currency_id">{__("currency")}:</label>
    <div class="controls">
	<select name="payment_data[processor_params][currency_id]" id="currency_id">
		<option value="EUR"{if $processor_params.currency_id eq "EUR"} selected="selected"{/if}>EUR</option>
	</select>
</div>
</div>
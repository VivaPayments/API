{* $Id: vivawallet.php 11696 2012-12-12 09:30:02Z vivawallet $ *}

{assign var="success_url" value="`$config.http_location`/`$config.customer_index`?dispatch=payment_notification.success&payment=vivawallet"}
{assign var="failure_url" value="`$config.http_location`/`$config.customer_index`?dispatch=payment_notification.fail&payment=vivawallet"}
{assign var="webhook_url" value="`$config.http_location`/`$config.customer_index`?dispatch=payment_notification.webhook&payment=vivawallet"}
<p>{$lang.text_vivawallet_notice|replace:"[success_url]":$success_url|replace:"[failure_url]":$failure_url|replace:"[webhook_url]":$webhook_url}</p>
<hr />

<div class="form-field">
	<label for="merchant_id">Merchant ID:</label>
	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" class="input-text"  size="60" />
</div>

<div class="form-field">
	<label for="password">API Key:</label>
	<input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}" class="input-text"  size="60" />
</div>

<div class="form-field">
	<label for="source">Source Code:</label>
	<input type="text" name="payment_data[processor_params][source]" id="source" value="{$processor_params.source}" class="input-text"  size="20" />
</div>

<div class="form-field">
	<label for="details">Instalments:</label>
	<input type="text" name="payment_data[processor_params][installments]" id="installments" 
				value="{$processor_params.installments}" class="input-text" size="60" maxlength="2" />
<br><em>Example: 12 - maximum of 12 instalments can be selected</em>
</div>

<div class="form-field">
	<label for="details">Instalment steps:</label>
	<input type="text" name="payment_data[processor_params][step]" id="installments" 
				value="{$processor_params.step}" class="input-text" size="60" maxlength="6" />
<br><em>Example: 30 - for every 30 euro an instalment option will be available</em>                
</div>

<div class="form-field">
	<label for="details">First instalment:</label>
	<input type="text" name="payment_data[processor_params][start_installments]" id="installments" 
				value="{$processor_params.start_installments}" class="input-text" size="60" maxlength="2" />
<br><em>Example: 3 - first instalment option will be 3</em>                
</div>

<div class="form-field">
	<label for="currency_id">{$lang.currency}:</label>
	<select name="payment_data[processor_params][currency_id]" id="currency_id">
		<option value="EUR"{if $processor_params.currency_id eq "EUR"} selected="selected"{/if}>EUR</option>
        <option value="GBP"{if $processor_params.currency_id eq "GBP"} selected="selected"{/if}>GBP</option>
        <option value="RON"{if $processor_params.currency_id eq "RON"} selected="selected"{/if}>RON</option>
        <option value="BGN"{if $processor_params.currency_id eq "BGN"} selected="selected"{/if}>BGN</option>
	</select>
</div>
{if $status == 'ok'}
	<p>{l s='Your order on' mod='vivawallet'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='vivawallet'}
		<br /><br />{l s='For any questions or for further information, please contact us.' mod='vivawallet'}
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your transaction. If you think this is an error, please contact us.' mod='vivawallet'} 
	</p>
{/if}

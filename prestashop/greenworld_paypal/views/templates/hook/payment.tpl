<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_paypal', 'payment', [], true)}" title="{l s='Pay by paypal.' mod='greenworld_paypal'}">
		{l s='使用綠界paypal付款.' mod='greenworld_'}
	</a>
</p>
<!--
<a href="javascript:$('#greenworld_paypal').submit();" title="{$payment}">
<p class="payment_module">{$payment}</p></a>
<form action="{$link->getModuleLink('linkGreenWorld', 'payment')}" method="post" id="greenworld_paypal" class="hidden">
<input type="hidden" name="hiddenlink" id="hiddenlink" value="{$link_pay}"/>
</form>
<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_paypal', 'validation', [], true)}" title="{l s='Pay by check.' mod='cheque'}">
		<img src="{$this_path}cheque.jpg" alt="{l s='Pay by check.' mod='cheque'}" width="86" height="49" />
		{l s='Pay by check (order processing will take more time).' mod='cheque'}
	</a>
</p>
                -->
                
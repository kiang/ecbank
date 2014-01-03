<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_ecpay', 'payment', [], true)}" title="{l s='Pay by ecpay.' mod='greenworld_ecpay'}">
		{l s='使用綠界ECPAY付款.' mod='greenworld_'}
	</a>
</p>
<!--
<a href="javascript:$('#greenworld_ecpay').submit();" title="{$payment}">
<p class="payment_module">{$payment}</p></a>
<form action="{$link->getModuleLink('linkGreenWorld', 'payment')}" method="post" id="greenworld_ecpay" class="hidden">
<input type="hidden" name="hiddenlink" id="hiddenlink" value="{$link_pay}"/>
</form>
<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_ecpay', 'validation', [], true)}" title="{l s='Pay by check.' mod='cheque'}">
		<img src="{$this_path}cheque.jpg" alt="{l s='Pay by check.' mod='cheque'}" width="86" height="49" />
		{l s='Pay by check (order processing will take more time).' mod='cheque'}
	</a>
</p>
                -->
                
<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_webatm', 'payment', [], true)}" title="{l s='Pay by webatm.' mod='greenworld_webatm'}">
		{l s='使用WEBATM付款.' mod='greenworld_'}
	</a>
</p>
<!--
<a href="javascript:$('#greenworld_webatm').submit();" title="{$payment}">
<p class="payment_module">{$payment}</p></a>
<form action="{$link->getModuleLink('linkGreenWorld', 'payment')}" method="post" id="greenworld_webatm" class="hidden">
<input type="hidden" name="hiddenlink" id="hiddenlink" value="{$link_pay}"/>
</form>
<p class="payment_module">
	<a href="{$link->getModuleLink('greenworld_webatm', 'validation', [], true)}" title="{l s='Pay by check.' mod='cheque'}">
		<img src="{$this_path}cheque.jpg" alt="{l s='Pay by check.' mod='cheque'}" width="86" height="49" />
		{l s='Pay by check (order processing will take more time).' mod='cheque'}
	</a>
</p>
                -->
                
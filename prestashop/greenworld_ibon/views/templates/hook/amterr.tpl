{capture name=path}{l s='Shipping' mod='greenworld'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summation' mod='greenworld'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
{$error_code}
<form action="{$home}" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p>
           使用此付款方式,付款金額需介於30~20000<br>
	</p>
	<p class="cart_navigation">
		<input type="submit" name="submit" value="{l s='確認' mod='greenworld'}" class="exclusive_large" />
	</p>
</form>
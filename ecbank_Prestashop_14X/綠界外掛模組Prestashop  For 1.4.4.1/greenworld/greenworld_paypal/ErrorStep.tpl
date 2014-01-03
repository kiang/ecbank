{capture name=path}{l s='Shipping' mod='greenworld'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summation' mod='greenworld'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<form action="{$home}" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p>
            您的操作有問題，請按下方確認鍵回到首頁。<BR>
            或留言給管理者，回報您的錯誤。<a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='cashondelivery'}</a>
	</p>
	<p class="cart_navigation">
		<input type="submit" name="submit" value="{l s='確認' mod='greenworld'}" class="exclusive_large" />
	</p>
</form>
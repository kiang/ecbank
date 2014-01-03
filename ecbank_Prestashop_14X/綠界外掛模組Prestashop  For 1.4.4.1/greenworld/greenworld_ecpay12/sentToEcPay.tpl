{capture name=path}{l s='Shipping' mod='greenworld'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summation' mod='greenworld'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<form action="{$URL}" method="post">
	<p>
                您在{$shop_name}訂單已經成立。<BR/>
                訂單編號：<font color="red">{$od_sob}</font>。<BR/>
                分期總金額：<font color="red">{$amt}</font>元整。<BR /><BR/>

                請按下方<font color="red">繼續</font>，完成付款程序。<BR /><BR/>

                如果您有相關問題想與系統管理者聯繫，請按 <a href="{$link->getPageLink('contact-form.php', true)}" color="blue">聯繫管理人員</a>。</p>

	</p>
	<p class="cart_navigation">
		<input type="submit" name="submit" value="{l s='繼續' mod='greenworld'}" class="exclusive_large" />
                <input type="hidden" name="act" value="{$payment_type}">
                <input type="hidden" name="client" value="{$mer_id}">
                <input type="hidden" name="amount" value="{$amt}">
                <input type="hidden" name="stage" value="12">
                <input type="hidden" name="roturl" value="{$return_url}">
                <input type="hidden" name="od_sob" value="{$od_sob}">
        </p>
</form>
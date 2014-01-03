{capture name=path}{l s='Check payment' mod='greenworld'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summation' mod='代碼繳費'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
<h3>{l s='定單確認' mod='cheque'}</h3>


{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

    <div>
   
<form action="{$link->getModuleLink('greenworld_allpay', 'validation', [], true)}" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p >
                您在{$shop_name}所購買所有商金額：{$total} 元整。<BR /><BR/>

                如果您所購買的金額總數非正整數(ex:13,100,1500)，有小數點(ex:100.5 150.44)。<BR/>
                系統會將您所購買的<font color='red'>{$total}</font>金額轉為正整數<font color='red'>{$inttotal}</font>。<BR/> 
                如果確認無誤並認同轉換為正整數的話，請按下方<font color='red'>同意</font>按鈕開始進行付費機制。<BR /><BR/>

                如果您有相關問題想與系統管理者聯繫，請按 <a href="{$link->getPageLink('contact.php', true)}" color="blue">聯繫管理人員</a>。
	</p>
	<p class="cart_navigation">
                <a href="{$link->getPageLink('order.php', true)}?step=0" class="button_large">{l s='返回購物車' mod='cashondelivery'}</a>
		<input type="submit" name="submit" value="{l s='同意' mod='greenworld'}" class="exclusive_large" />
	</p>
</form>
        </div>
{/if}

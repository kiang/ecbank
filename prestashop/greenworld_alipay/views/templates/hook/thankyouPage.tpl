

<form  method="post" action="https://ecbank.com.tw/gateway.php">
	<input type="hidden" name="mer_id" value="{$mer_id}" />
        <input type="hidden" name="payment_type" value="alipay" />
        <input type="hidden" name="amt" value="{$amt}" />
        <input type="hidden" name="roturl" value="{$return_url}" />
        <input type="hidden" name="ok_url" value="{$return_url}" />
        <input type="hidden" name="od_sob" value="{$id_order}" />
        {foreach from=$name item=value key=key }
        <input name="goods_name[]" type="hidden" value="{$name[$key]}" />
        <input name="goods_amount[]" type="hidden" value="{$qty[$key]}" />
        {/foreach}

	<p>
               您的訂單編號：<font color='red'>{$id_order}</font> 已經成功下單。<BR/>
               您的訂單金額：<font color='red'>{$amt}</font> 。<BR/>

              
             
            
             
	</p>
        <input type="submit" value="前往繳費" class="exclusive_large">
</form>
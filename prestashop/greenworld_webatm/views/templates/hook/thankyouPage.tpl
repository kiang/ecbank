
<form  method="post" action="https://ecbank.com.tw/gateway.php">
	<input type="hidden" name="mer_id" value="{$mer_id}" />
        <input type="hidden" name="payment_type" value="web_atm" />
        <input type="hidden" name="amt" value="{$amt}" />
        <input type="hidden" name="return_url" value="{$return_url}" />
        <input type="hidden" name="od_sob" value="{$id_order}" />
     
	<p>
               您的訂單編號：<font color='red'>{$id_order}</font> 已經成功下單。<BR/>
               您的訂單金額：<font color='red'>{$amt}</font> 。<BR/>
              
             
            
             
	</p>
        <input type="submit" value="前往繳費" class="exclusive_large">
</form>
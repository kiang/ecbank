
<form  method="post" action="https://ecpay.com.tw/form_Sc_to5_fn.php">
	<input type="hidden" name="client" value="{$mer_id}" />
        <input type="hidden" name="act" value="auth" />
        <input type="hidden" name="amount" value="{$amt}" />
        <input type="hidden" name="roturl" value="{$return_url}" />
        <input type="hidden" name="od_sob" value="{$id_order}" />
        <input type="hidden" name="stage" value="6" />
        
     
	<p>
               您的訂單編號：<font color='red'>{$id_order}</font> 已經成功下單。<BR/>
               您的訂單金額：<font color='red'>{$amt}</font> 。<BR/>
              
             
            
             
	</p>
        <input type="submit" value="前往繳費" class="exclusive_large">
</form>
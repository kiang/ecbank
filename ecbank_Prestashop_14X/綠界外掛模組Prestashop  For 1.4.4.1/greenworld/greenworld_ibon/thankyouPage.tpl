
<form action="" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p>
               您的訂單編號：<font color='red'>{$id_order}</font> 已經成功下單。<BR/>
               繳費代碼：<font color='red'>{$payno}</font><BR/>
               綠界ECBank交易單號:<font color='red'>{$tsr}</font><BR/>
               請在<font color='red'>{$expire_date}</font>之前到7-11完成繳費。<BR/><BR/>

               (建議將<font color='blue'>綠界ECBank交易單號</font>及<font color='blue'>訂單編號</font>記錄下來，以便日後查詢。)
	</p>
</form>
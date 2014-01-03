
<form action="" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p>
               您的訂單編號：<font color='red'>{$id_order}</font><BR/>
               ECPay授權交易單號:<font color='red'>{$gwsr}</font><BR/>
               您本次分期的總金額為：<font color='blue'>{$amount}</font><BR/>
               您本次分期的期數為：<font color='blue'>{$stage}</font><BR/>
               第一期的繳款費用為：<font color='blue'>{$stast}</font><BR/>
               其他期數的繳款費用為：<font color='blue'>{$staed}</font><BR/>
               感謝您，你已經完成ECPay信用卡交易，如果有還有相關問題請 <a href="{$link->getPageLink('contact-form.php', true)}" color="blue">聯繫管理人員</a>。<BR>
               (建議將<font color='blue'>ECPay授權交易單號</font>及<font color='blue'>訂單編號</font>記錄下來，以便日後查詢。)
	</p>
</form>
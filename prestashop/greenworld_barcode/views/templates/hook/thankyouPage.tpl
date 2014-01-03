
<form action="" method="post">
	<input type="hidden" name="confirm" value="1" />
	<p>
               您的訂單編號：<font color='red'>{$id_order}</font> 已經成功下單。<BR/>
               請列印你的超商繳費單：<font color='red'><a href='{$pay_URL}' target='_blank'>＜列印繳費單＞</a></font><BR/>
               綠界ECBank交易單號:<font color='red'>{$tsr}</font><BR/>
               請於{$expire_date}之前，到各大超商繳費。<BR /><BR />
               
               
	</p>
</form>

<form action="" method="post">
	<input type="hidden" name="confirm" value="1" />
	
	
	
	<table width=100% border=1 align=center cellpadding=3 cellspacing=1>
                   <tr>
                     <td width="288" height="47" align=center bgcolor=#FFFF99><font size="3">付款方式</font></td>
                     <td width=1339 bgcolor=#FFFF99><font size="3">　銀行轉帳 --- 轉帳銀行代碼 (<font color="#0000FF" size="3">{$bankcode}</font>)</font></td>
                   </tr><tr>
                   </tr><tr>
                     <td height="45" align=center><font size="3">銀行轉帳帳戶</font></td><td>　<font color="#0000FF" size="3">{$vaccno}</font></td>
                   </tr><tr>
                     <td height="46" align=center><font size="3">繳費金額</font></td><td>　<font size="3">$</font><font color="#0000FF" size="3">{$amt}</font><font size="3">元</font></td>
                   </tr><tr>
                     <td height="43" align=center><font size="3">訂單編號</font></td><td>　<font color="#0000FF" size="3">{$od_sob}</font></td>
                   </tr>                   
                   <tr>
                     <td height="74" colspan=2><font size="3">　請記下上列轉帳銀行代碼及轉帳帳戶,至最近的提款機台(ATM)操作轉帳,<br />
                     <br>
                     　便可完成繳費,繳費之收據請留存以供備核,繳費之後才算完成購物流程</font></td>
                   </tr>
     </table>
</form>
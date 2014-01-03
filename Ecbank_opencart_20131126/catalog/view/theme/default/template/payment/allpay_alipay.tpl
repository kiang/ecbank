<script type="text/javascript"><!--
function checkTotal(){
	var total = <?php echo $total; ?>;	
	if(total>=10 && total<=2000000){		
		$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/allpay_alipay/confirm',
		success: function() {
			document.forms['form1'].submit();
			}		
		});
	}		
	else{
		alert('<?php echo $text_total_error; ?>');
	}
}
//--></script>
<?php //$this->cart->clear(); ?>
<?php echo $def_url; ?>
<div style="background: #F7F7F7; border: 1px solid #DDDDDD; padding: 10px; margin-bottom: 10px;"><?php echo $text_payment; ?><br /><br />
	<?php echo ($allpay_alipay_description == "") ? "" : $text_instruction . "<br /><font color='#FF9900'>" . $allpay_alipay_description . "</font><br />"; ?>	
  <br />
</div>
<div class="buttons">
  <div class="right"><a  id="button-confirm" class="button" onclick="checkTotal()"><span><?php echo $button_confirm; ?></span></a></div>
</div>
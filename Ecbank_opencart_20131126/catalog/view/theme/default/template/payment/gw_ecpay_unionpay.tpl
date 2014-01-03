<script type="text/javascript"><!--
function checkTotal(){
	var total = <?php echo $total; ?>;				
		$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/gw_ecpay_unionpay/confirm',
		success: function() {
			document.forms['form1'].submit();
			}		
		});	
}
//--></script> 
<?php echo $def_url; ?>
<div class="checkout-heading" style="background: #F7F7F7; border: 1px solid #DDDDDD; margin-bottom: 0px;"><?php echo $text_payment; ?></div>
<div class="buttons" style="border: 1px solid #DDDDDD; border-top: 0px; padding: 10px; margin-bottom: 0px;<?php echo ($gw_ecpay_unionpay_description == "") ? " display:none;" : "";?>">
	<?php echo ($gw_ecpay_unionpay_description == "") ? "" : $text_instruction . "<br /><font color='#FF9900'>" . $gw_ecpay_unionpay_description . "</font><br />"; ?>
</div>
<div class="buttons" style="border: 1px solid #DDDDDD; border-top: 0px; padding: 10px; margin-bottom: 0px;" >
	<div style="position: relative;float: right; width: 200"><?php echo $text_symboleft.$credit_fee; ?></div>
	<div style="position: relative;float: right; width: 200;font-weight: bold;"><?php echo $text_creditfee; ?></div>
</div>
<div class="buttons" style="border: 1px solid #DDDDDD; border-top: 0px; padding: 10px; margin-bottom: 10px;">
	<div style="position: relative;float: right; width: 200; font-weight: bold; font-size: 18pt;"><?php echo $text_credittotal.$text_symboleft.$credit_total; ?></div>
</div>
<div class="buttons">
	<div class="right"><a  id="button-confirm" class="button" onclick="checkTotal()"><span><?php echo $button_confirm; ?></span></a></div>
</div>
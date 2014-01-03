<script type="text/javascript"><!--
function checkTotal(){
	var total = <?php echo $total; ?>;	
	if(total>=30 && total<=20000){		
		$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/ecbank_pincode/confirm',
		success: function() {
			location = '<?php echo $continue; ?>';
			}		
		});
	}		
	else{
		alert('<?php echo $text_total_error; ?>');
	}
}
//--></script> 

<div style="background: #F7F7F7; border: 1px solid #DDDDDD; padding: 10px; margin-bottom: 10px;"><?php echo $text_payment; ?><br /><br />
	<?php echo ($ecbank_vacc_description == "") ? "" : $text_instruction . "<br /><font color='#FF9900'>" . $ecbank_vacc_description . "</font><br />"; ?>	
  <br />
</div>
<div class="buttons">
  <div class="right"><a  id="button-confirm" class="button" onclick="checkTotal()"><span><?php echo $button_confirm; ?></span></a></div>
</div>
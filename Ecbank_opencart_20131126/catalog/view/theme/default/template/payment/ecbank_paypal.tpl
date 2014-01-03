<script type="text/javascript"><!--
function checkTotal(){
	var total = <?php echo $total; ?>;			
		$.ajax({ 
		type: 'GET',
		url: 'index.php?route=payment/ecbank_paypal/confirm',
		success: function() {
			document.forms['form1'].submit();
			}		
		});	
}
//--></script> 
<?php echo $def_url; ?>
<div style="background: #F7F7F7; border: 1px solid #DDDDDD; padding: 10px; margin-bottom: 10px;"><?php echo $text_payment; ?><br /><br />
	<?php echo ($ecbank_paypal_description == "") ? "" : $text_instruction . "<br /><font color='#FF9900'>" . $ecbank_paypal_description . "</font><br />"; ?>	
  <br />
</div>
<div class="buttons">
  <div class="right"><a  id="button-confirm" class="button" onclick="checkTotal()"><span><?php echo $button_confirm; ?></span></a></div>
</div>
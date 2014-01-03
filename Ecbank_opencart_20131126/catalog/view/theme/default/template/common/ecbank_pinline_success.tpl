<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  <h1><?php echo (!$error) ? $heading_title : $heading_title_error; ?></h1> 
  <?php echo $text_title; ?><?php echo (!$error) ? $text_message1 . $od_sob . $text_message2 . $lineurl . $text_message3 : $error_message; ?>
    <div class="buttons">
      <table>
        <tr>
          <td align="right"><a id="checkout" class="button"><span><?php echo $button_continue; ?></span></a></td>
        </tr>
      </table>
    </div>  
  <div class="bottom">
  </div>
</div>
<?php echo $footer; ?>
<script type="text/javascript"><!--
$('#checkout').click(function() {
	$.ajax({ 
		type: 'GET',
		url: 'index.php?route=checkout/ecbank_pinline_success/home',
		success: function() {
			location = '<?php echo $continue; ?>';
		}		
	});
});
//--></script>


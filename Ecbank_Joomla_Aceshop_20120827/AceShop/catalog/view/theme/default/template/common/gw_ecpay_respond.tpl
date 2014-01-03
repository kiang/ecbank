<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  <h1><?php if($res == 'succ'){echo $heading_title;}elseif($res == 'error'){echo $heading_title_error;}else{echo $res_err;} ?></h1> 
  <?php echo $text_title; if($res == 'succ'){echo $text_message_succ;}elseif($res == 'error'){echo $text_message_error;}else{echo $res_err;} ?>
    <div class="buttons">
      <table>
        <tr>
          <td align="right"><a onclick="location = '<?php echo str_replace('&', '&amp;', $continue); ?>'" class="button"><span><?php echo $button_continue; ?></span></a></td>
        </tr>
      </table>
    </div>  
  <div class="bottom">
  </div>
</div>
<?php echo $footer; ?>

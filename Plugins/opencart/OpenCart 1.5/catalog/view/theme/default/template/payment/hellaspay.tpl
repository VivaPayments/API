<form action="<?php echo $action; ?>" method="get" id="payment">
  <input type="hidden" name="Ref" value="<?php echo $hellaspay_ordercode; ?>" />
  <div class="buttons">
  <div class="right">
    <input type="button" onclick="$('#payment').submit();" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>
</form>
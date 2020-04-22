<?php

class VivawalletPayModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		?>
        <form id="payment_form_vivawallet" name="payment_form_vivawallet" action="<?php echo $_POST['VivawalletUrl']; ?>" method="get">
		<?php
            foreach( $_POST as $name => $value ) {
            if($name !='VivawalletUrl'){
                echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
                }
            }
        ?>
        <noscript>
        <input type="submit" value="<?php echo $this->l('Pay Now'); ?>" />
        </noscript>
        </form>
        <script type="text/javascript">
            <!--
            document.getElementById('payment_form_vivawallet').submit();
            //-->
        </script>
            <?php
			
	}
}

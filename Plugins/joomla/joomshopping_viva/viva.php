<?php
@ini_set('display_errors',0);
define('_JEXEC', 0);

if (file_exists(__DIR__ . '/defines.php'))
{
	include_once __DIR__ . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', __DIR__);
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';


$lang = JFactory::getLanguage();
$locale = $lang->get('tag');

if(file_exists(JPATH_ROOT . '/components/com_jshopping/lang/viva/'.$locale.'.php')){
	require_once (JPATH_ROOT . '/components/com_jshopping/lang/viva/'.$locale.'.php');
} else { 
	require_once (JPATH_ROOT . '/components/com_jshopping/lang/viva/en-GB.php');
}

	//submitform
	if(isset($_POST['APACScommand']) && $_POST['APACScommand']=='NewPayment') { 
    $db = JFactory::getDbo();
    $db->setQuery("SELECT * FROM #__vivadata WHERE ref='".addslashes($_POST['merchantRef'])."';");
    $check_query = $db->loadObjectList();
    $gatewayurl = $check_query[0]->gatewayurl;
	$ordercode  = $check_query[0]->ordercode;
	?>
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <p><?php echo _JSHOP_VIVA_PAYNOTE; ?></p>
    <form action="<?php echo $gatewayurl; ?>" method="GET" name="process"> 
    <?php
     echo '<input type="hidden" name="Ref" value="'.$ordercode.'" />';
    ?>    
    <input type="submit" value="<?php echo _JSHOP_VIVA_PAYNOW; ?>" />
    </form>
        <script type="text/javascript">
        window.onload = function(){
            document.process.submit();
        };
        </script>
     </body>
    </html>	
	<?php } //end submitform
	  
	  //fail
	  elseif(preg_match("/fail/i", $_SERVER['REQUEST_URI'])) {
	  if(isset($_GET['s']) && $_GET['s'] !='') {
	  $tm_ref = $_GET['s'];
	  
	    $db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__vivadata WHERE ordercode='".addslashes($tm_ref)."';");
		$check_query = $db->loadObjectList();
	
		$db_failurl = $check_query[0]->failurl;
		$db_orderid = $check_query[0]->orderid;
		
		if(isset($db_failurl) && isset($db_orderid)){
		$query = "UPDATE #__vivadata SET order_state = 'X' WHERE ordercode='".addslashes($tm_ref)."';";
		$db->setQuery($query);
		$db->query();
		
			if ( strpos($db_failurl, '&amp;') !== false ) {
			  $db_failurl = str_replace('&amp;', '&', $db_failurl);
			}
			
			if (!headers_sent($filename, $linenum)) {
			header('Location: ' . $db_failurl);
			} else {
			die("Headers already sent in $filename on line $linenum\n");
			}
		}
	  
	  } else {
	  	die("Error connecting to the bank, check URLs - MerchantID");
		}
	  }//end fail
	  
	  //success
	  elseif(preg_match("/success/i", $_SERVER['REQUEST_URI'])) {
	  
	  if(isset($_GET['s']) && $_GET['s'] !='') {
	  $tm_ref = $_GET['s'];
	  
	    $db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__vivadata WHERE ordercode='".addslashes($tm_ref)."';");
		$check_query = $db->loadObjectList();
	
		$db_okurl = $check_query[0]->okurl;
		$db_orderid = $check_query[0]->orderid;
	  
		if(isset($db_okurl) && isset($db_orderid)){
		$query = "UPDATE #__vivadata SET order_state = 'P' WHERE ordercode='".addslashes($tm_ref)."';";
		$db->setQuery($query);
		$db->query();

			if ( strpos($db_okurl, '&amp;') !== false ) {
			  $db_okurl = str_replace('&amp;', '&', $db_okurl);
			}
					
			if (!headers_sent($filename, $linenum)) {
			header('Location: ' . $db_okurl);
			} else {
			die("Headers already sent in $filename on line $linenum\n");
			}
		}
	  
	  }

	  }//end success	
?>
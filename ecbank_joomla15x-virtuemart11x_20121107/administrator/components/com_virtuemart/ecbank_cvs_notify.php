<?php 

if ($_POST) {



	define('_VALID_MOS', '1');



	header("HTTP/1.0 200 OK");



    global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database,

    $mosConfig_mailfrom, $mosConfig_fromname;

    

        /*** access Joomla's configuration file ***/

        $my_path = dirname(__FILE__);

        

        if( file_exists($my_path."/../../../configuration.php")) {

            $absolute_path = dirname( $my_path."/../../../configuration.php" );

            require_once($my_path."/../../../configuration.php");

        }

        elseif( file_exists($my_path."/../../configuration.php")){

            $absolute_path = dirname( $my_path."/../../configuration.php" );

            require_once($my_path."/../../configuration.php");

        }

        elseif( file_exists($my_path."/configuration.php")){

            $absolute_path = dirname( $my_path."/configuration.php" );

            require_once( $my_path."/configuration.php" );

        }

        else {

            die( "Joomla Configuration File not found!" );

        }

        

        $absolute_path = realpath( $absolute_path );

        

        // Set up the appropriate CMS framework

        if( class_exists( 'jconfig' ) ) {

			define( '_JEXEC', 1 );

			define( 'JPATH_BASE', $absolute_path );

			define( 'DS', DIRECTORY_SEPARATOR );

			

			// Load the framework

			require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );

			require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );



			// create the mainframe object

			$mainframe = & JFactory::getApplication( 'site' );

			

			// Initialize the framework

			$mainframe->initialise();

			

			// load system plugin group

			JPluginHelper::importPlugin( 'system' );

			

			// trigger the onBeforeStart events

			$mainframe->triggerEvent( 'onBeforeStart' );

			$lang =& JFactory::getLanguage();

			$mosConfig_lang = $GLOBALS['mosConfig_lang']          = strtolower( $lang->getBackwardLang() );

			// Adjust the live site pathsucc

			$mosConfig_live_site = str_replace('/administrator/components/com_virtuemart', '', JURI::base());

			$mosConfig_absolute_path = JPATH_BASE;

        } else {

        	define('_VALID_MOS', '1');

        	require_once($mosConfig_absolute_path. '/includes/joomla.php');

        	require_once($mosConfig_absolute_path. '/includes/database.php');

        	$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );

        	$mainframe = new mosMainFrame($database, 'com_virtuemart', $mosConfig_absolute_path );

        }



        // load Joomla Language File

        if (file_exists( $mosConfig_absolute_path. '/language/'.$mosConfig_lang.'.php' )) {

            require_once( $mosConfig_absolute_path. '/language/'.$mosConfig_lang.'.php' );

        }

        elseif (file_exists( $mosConfig_absolute_path. '/language/english.php' )) {

            require_once( $mosConfig_absolute_path. '/language/english.php' );

        }

    /*** END of Joomla config ***/

    

    

    /*** VirtueMart part ***/        

        require_once($mosConfig_absolute_path.'/administrator/components/com_virtuemart/virtuemart.cfg.php');

        include_once( ADMINPATH.'/compat.joomla1.5.php' );

        require_once( ADMINPATH. 'global.php' );

        require_once( CLASSPATH. 'ps_main.php' );

     

     

     

        

     

	

		   //載入綠界便利付代碼繳費的設定檔

//		   print_r($_REQUEST);

//		   echo "<hr>";



	   

			require_once( CLASSPATH. 'payment/ps_ecbank_cvs_curl.cfg.php' );

			

			$post_mer_id= trim(stripslashes($_POST['mer_id']));

			$payment_type= trim(stripslashes($_POST['payment_type']));

			$tsr = trim(stripslashes($_POST['tsr'])); 	

			$od_sob= trim(stripslashes($_POST['od_sob']));

			//$process_time = trim(stripslashes($_POST['process_time'])); 	

			$payno = trim(stripslashes($_POST['payno']));
			$amt = trim(stripslashes($_POST['amt'])); 
			$succ  = trim(stripslashes($_POST['succ'])); 
			$payfrom  = trim(stripslashes($_POST['payfrom'])); 
			$proc_date  = trim(stripslashes($_POST['proc_date'])); 
			$proc_time  = trim(stripslashes($_POST['proc_time'])); 
						
			

			$checkcode = ECBANK_CVS_CHECKCODE;

			
		// 組合字串
 		$serial = trim($proc_date.$proc_time.$tsr);

		// 回傳的交易驗證壓碼
		$tac = trim(stripslashes($_POST['tac']));
		
		// ECBank 驗證Web Service網址
		
		// 取得驗證結果 (OpenSSL 方式) ***************************************************
		//$ws_url = 'https://ecbank.com.tw/web_service/get_outmac_valid.php?key='.$checkcode.
		//         '&serial='.$serial.
		//          '&tac='.$tac;
		//$tac_valid = file_get_contents($ws_url);
		// 取得驗證結果 (OpenSSL 方式) **************************************************
		
		// 取得驗證結果 (Curl 方式) ************************************************

		$ecbank_gateway = 'https://ecbank.com.tw/web_service/get_outmac_valid.php';
		$post_parm	=	'key='.$checkcode.
						'&serial='.$serial.
						'&tac='.$tac;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ecbank_gateway);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post_parm);
		$strAuth = curl_exec($ch);
		if (curl_errno($ch)) $strAuth = false;
		curl_close($ch);

		

		$d['order_id'] = $od_sob;
		
		if($strAuth == 'valid=1') {
		    $d['order_status'] = 'C';
		} 
			
		else {
		    $d['order_status'] = 'X';
		}
		require_once ( CLASSPATH . 'ps_order.php' );
	  	$ps_order= new ps_order;
	   	$ps_order->order_status_update($d);	
		echo "ok"; 	

	

}
  

?>


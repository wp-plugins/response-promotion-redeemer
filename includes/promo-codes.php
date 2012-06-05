<?php
define( 'BLOCK_LOAD', true );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
global $wpdb; 

$postID = $_REQUEST['pID'];

// parse query string


$the_referer = $_SERVER["HTTP_REFERER"];
//        Who you want to recieve the emails from the form. (Hint: generally you.)
$sendto = $_REQUEST['email'];
$bcc = 'bbielefeldt@thepowertoprovoke.com';
$sendFrom = $_REQUEST['from_email'];
$redURL = $_REQUEST['redURL'];
$table_name = $_REQUEST['tablename'] ;
$ptype = $_REQUEST['ptype'] ;


//        The subject you'll see in your inbox
$subject = 'Promotion portal submission at '.$the_referer;

//        Message for the user when he/she doesn't fill in the form correctly.
$errormessage = 'Oops! There seems to have been a problem. May we suggest...';

//        Message for the user when he/she fills in the form correctly.
$thanks = "Thank you for your submission your Partner promotion code will be emailed to you go check...";

//        Message for the bot when it fills in in at all.
$honeypot = "You filled in the honeypot! If you're human, try again!";

//        Various messages displayed when the fields are empty.
$emptyname =  'Entering your Name?';

$emptylicense =  'Entering your Promotion code?';

$emptyemail = 'Entering your Email Address?';


//       Various messages displayed when the fields are incorrectly formatted.
$alertname =  'Entering your Name using only the standard alphabet?';

$alertlicense =  'Entering your Promotion Code using only the standard alphanumeric characters?';
$invalidlicense =  'The Promotion Code you entered is invalid, please try again';

$alertemail = 'Entering your Email in this format: <i>name@example.com</i>?';

// --------------------------- Thats it! don't mess with below unless you are really smart! ---------------------------------

//Setting used variables.
$alert = '';
$pass = 0;

$license = $_REQUEST['license'];
$license = str_replace("*%", "1", $license, $count);
$license = str_replace("/&*o", "5", $license, $count);	
$data_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE our_code = '".$license."'" ) );
$fivesdrafts = $wpdb->get_results( $wpdb->prepare( "SELECT time_entered, partner_code, user_email, user_name FROM $table_name WHERE our_code = '".$license."'" ) );
foreach ( $fivesdrafts as $fivesdraft ) {
			$time = $fivesdraft->time_entered;
			$partner_code = $fivesdraft->partner_code;
			$partner_code = str_replace("*%", "6", $partner_code, $count);
			$partner_code = str_replace("/&*o", "2", $partner_code, $count);
			$user_email = $fivesdraft->user_email;
			$user_name = $fivesdraft->user_name;
		}

// Sanitizing the data, kind of done via error messages first. Twice is better!
function clean_var($variable) {
    $variable = strip_tags(stripslashes(trim(rtrim($variable))));
  return $variable;
}

//The first if for honeypot.
if ( empty($_REQUEST['last']) ) {

	// A bunch of if's for all the fields and the error messages.
	if ( empty($_REQUEST['name']) ) {
		$pass = 1;
		$alert .= "<li>" . $emptyname . "</li>";
	} elseif ( ereg( "[][{}()*+?.\\^$|]", $_REQUEST['name'] ) ) {
		$pass = 1;
		$alert .= "<li>" . $alertname . "</li>";
	}
	if ( empty($_REQUEST['license']) ) {
		$pass = 1;
		$alert .= "<li>" . $emptylicense . "</li>";
	} elseif ( ereg( "[][{}()*+?.\\^$|]", $_REQUEST['license'] ) ) {
		$pass = 1;
		$alert .= "<li>" . $alertlicense . "</li>";
	} elseif ($data_count == 0 || $data_count > 1) {
		$pass = 1;
		$alert .= "<li>" . $invalidlicense . " ...</li>";
	} elseif ($user_email != 'Not Used' || $user_name != 'Not Used') {
		$pass = 1;
		$alert .= "<li>" . $invalidlicense . "  ...</li>";
	}
	if ( empty($_REQUEST['email']) ) {
		$pass = 1;
		$alert .= "<li>" . $emptyemail . "</li>";
	} elseif ( !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$", $_REQUEST['email']) ) {
		$pass = 1;
		$alert .= "<li>" . $alertemail . "</li>";
	}
	
	//If the user err'd, print the error messages.
	if ( $pass==1 ) {

		//This first line is for ajax/javascript, comment it or delete it if this isn't your cup o' tea.
	echo "<script>$(\".message\").hide(\"slow\").show(\"slow\"); </script>";
	echo "<b>" . $errormessage . "</b>";
	echo "<ul>";
	echo $alert;
	echo "</ul>";
	 

	// If the user didn't err and there is in fact a message, time to email it.
	} elseif ($pass==0) {
		$time = current_time('mysql');
		$email = $_REQUEST['email'];
		$name = $_REQUEST['name'];
		$p_code = str_replace("6", "*%", $partner_code, $count);
		$p_code = str_replace("2", "/&*o", $$p_code, $count);	
		$wpdb->update( $table_name, array('time_entered' => $time, 'user_email' => $email, 'user_name' => $name), array( 'partner_code' => $p_code ), array( '%s', '%s', '%s'), array( '%s' ) );
		//Construct the message.
		$message = "\n\nA promotion portal code has been submitted at ".$the_referer.".\n\n";
	    $message .= "From: " . clean_var($_REQUEST['name']) . "\n";
		$message .= "Email: " . clean_var($_REQUEST['email']) . "\n\n\n";
		$message .= "Logitech Promotion Code: " . clean_var($_REQUEST['license']) . "\n\n\n";
		
		$message .= "To redeem your promotion got to " . $redURL . "\n";
	    $message .= "And enter the code, ".$partner_code. " to complete the process";
		
	    $header = 'From:'. $sendFrom;
		$headers .= 'Bcc: '. $bcc . "\r\n";
	    if ($ptype == 'Connect') {
			$conURL = $_REQUEST['conURL'] ;
			$conNAME = $_REQUEST['conNAME'] ;
			$conTNAME = $_REQUEST['conTNAME'] ;
			$conUSER = $_REQUEST['conUSER'] ;
			$conPASSWORD = $_REQUEST['conPASSWORD'] ;
			$conQSTRING = $_REQUEST['conQSTRING'] ;
		} else if ($ptype == 'Query') {
			$queV1 = $_REQUEST['queV1'] ;
			$queV2 = $_REQUEST['queV2'] ;
			$queV3 = $_REQUEST['queV3'] ;
			// construct query string
			$username = str_replace(" ","_",$name);
			$theQUERY = $redURL.'?'.$queV1.'='.$username.'&'.$queV2.'='.$email.'&'.$queV3.'='.$partner_code;
		}
//Mail the message - for production

		mail($sendto, $subject, $message, $header);
//This is for javascript, 
	    $instructions = clean_var($_REQUEST['name']) . ", thankyou for redeeming your promotion.\n";
		$instructions .= "The information provded to finish the process is listed below and will be emailed to, " . clean_var($_REQUEST['email']) . "<br><br>";
		if ($ptype == 'Query') {
			$instructions .= "To redeem your promotion got to <a href='{$theQUERY}' target='_blank'>" . $redURL . "</a>, ";
		} else {
			$instructions .= "To redeem your promotion got to <a href='{$redURL}' target='_blank'>" . $redURL . "</a>, ";
		}
	    $instructions .= " and enter the code: ".$partner_code;
		echo "<script>$(\".message\").hide(\"slow\").show(\"slow\").animate({opacity: 1.0}, 4000); $(':input').clearForm() </script>";
		echo $instructions."<br>";
		//echo $thanks;
		if ($ptype == 'Query') {
			echo '<script type="text/javascript" language="javascript"> 
			window.open("'.$theQUERY.'"); 
			</script>';
		}
		die();

//Echo the email message - for development
		//echo "<br/><br/>" . $message;

	}
	
//If honeypot is filled, trigger the message that bot likely won't see.
} else {
	echo "<script>$(\".message\").hide(\"slow\").show(\"slow\"); </script>";
	echo $honeypot;
}

?>
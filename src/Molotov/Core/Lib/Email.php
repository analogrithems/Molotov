<?php
namespace Molotov\Core\Lib;


class Email
{
	public static function email($email='', $subject='', $message='', $headers='', $additional_parameters=''){
		$di = \Phalcon\DI::getDefault();
		if( isset($di->get('config')->test_email) && !empty($di->get('config')->test_email) ){
			$email = $di->get('config')->test_email;
		}
		$new_email = array(
			'to'=>$email,
			'subject'=>$subject,
			'message'=>$message,
			'headers'=>$headers,
			'additional_parameters'=>$additional_parameters
		);
		
		if(false && $di->has('queue')){
			$di->get('logger')->log('Queued Email to:'.$email);
			return $di->get('queue')->putInTube('email', $new_email);			
		}else{
		    return self::realSendEmail($new_email);
		}
	}
	
	public static function realSendEmail ( $email ){
		$di = \Phalcon\DI::getDefault();
		$mail = new \PHPMailer();

		$mail->addAddress($email['to']);
		$mail->FromName = 'Molotov';
		$mail->From = 'no-reply@asynonymous.com';
		
		if(preg_match('/From: (.+)/i', $email['headers'], $matches) > 0){
			$mail->From = $matches[1];
		}
		if(preg_match('/Fromname: (.+)/i', $email['headers'], $matches) > 0){
			$mail->FromName = $matches[1];
		}
		if(preg_match('/Bcc: (.+)/i', $email['headers'], $bcc_matches) > 0){
			$bcc_list = explode(',',$bcc_matches[1]);
			foreach($bcc_list as $bcc){
				if( isset($di->get('config')->test_email) && !empty($di->get('config')->test_email) ){
					$bcc = $di->get('config')->test_email;
				}
				$mail->addBCC($bcc);
			}
		}
		if(preg_match('/Cc: (.+)/i', $email['headers'], $cc_matches) > 0){
			$cc_list = explode(',',$cc_matches[1]);
			foreach($cc_list as $cc){
				if( isset($di->get('config')->test_email) && !empty($di->get('config')->test_email) ){
					$cc = $di->get('config')->test_email;
				}
				$mail->addCC($cc);
			}
		}
		if(isset($email['additional_parameters']['files'])){
			if(is_array($email['additional_parameters']['files'])){
				foreach($email['additional_parameters']['files'] as $file){
					$mail->addAttachment($file);
				}
			}else{
				$mail->addAttachment($email['additional_parameters']['files']);
			}
		}
		
		$mail->WordWrap = 70;
		
		$mail->isHTML(true);
		
		$mail->Subject = $email['subject'];
		$mail->Body    = $email['message'];
		$mail->AltBody = strip_tags($email['message']);
		
		if(!$mail->send()) {
			$di->get('logger')->log('Mailer Error: ' . $mail->ErrorInfo);
			return false;
		}
		
		return true;
	}
	
	

	public static function sendEmail( $job = null ){
		$di = \Phalcon\DI::getDefault();
	    $email = $job->getBody();
	    echo "Email Worker Sending Email to: ".print_r($email,1)."\n";
	    self::realSendEmail($email);

	    exit(0);
	}
}
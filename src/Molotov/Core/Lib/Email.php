<?php
namespace Molotov\Core\Lib;


class Email
{
	public static function mail($email='', $subject='', $message='', $headers='', $additional_parameters=''){
		$di = \Phalcon\DI::getDefault();
		if('dev' == $di->get('config')->mode){
			$email = 'aaron@digitalmediums.com';
		}
		if($di->has('queue')){
			return $di->get('queue')->putInTube('email', array(
				'to'=>$email,
				'subject'=>$subject,
				'message'=>$message,
				'headers'=>$headers,
				'additional_parameters'=>$additional_parameters
			));			
		}else{
			mail($email, $subject, $message, $headers, $additional_parameters);
		}


	}

	public static function sendEmail( $job = null ){
	    $_eml = $job->getBody();
	    echo "Email Worker Sending Email to: ".print_r($_eml,1)."\n";
	    mail(
	    	$_eml['to'],
	    	$_eml['subject'],
	    	$_eml['message'],
	    	wordwrap($_eml['headers'], 70, "\r\n"),
	    	$_eml['additional_parameters']
	    );
	    exit(0);
	}
}
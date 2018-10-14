<?php
class Mailgun {
	function __construct() {
	   //parent::__construct();
	}

	//send mail
	public function send($email_settings){
		$fields = array();//always reset this to reuse

		//to
		if(count($email_settings['to'])>0){
		  $fields['to'] = implode(",", $email_settings['to']);
		}


		//cc
		if(count($email_settings['cc'])>0){
			$fields['cc'] = implode(",", $email_settings['cc']);
		}

		//bcc
		if(count($email_settings['bcc'])>0){
			$fields['bcc'] = implode(",", $email_settings['bcc']);
		}

		//build Mailgun array and send out if there is a value in the to field
	 	$fields['html']=$email_settings['html'];
	 	$fields['subject']=$email_settings['subject'];
	 	$fields['from']=$email_settings['from'];


		//send mailgun email
		$ch = curl_init('https://api.mailgun.net/v3/'.MAILGUN_DOMAIN.'/messages');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:'.MAILGUN_KEY);


		$result = json_decode(curl_exec($ch), true);

		//check mail success
		if($result['message']=="Queued. Thank you.") {
			$email_success = array(
				'status' => true,
			);
		} else {
			$error = array(
				'status' => false,
				'message' => 'Email could not be sent.'
			);
			return $error;
		}
 	}
 }
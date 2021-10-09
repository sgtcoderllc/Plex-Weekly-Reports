<?php

class Core {
	function __construct() {}

	public function getXMLObject($url){
		$xml = $this->curl_get($url);
		$xml_array = new SimpleXMLElement($xml);

		return $xml_array;
	}

	public function curl_get($url, $options=array()){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'X-Plex-Token:'.PLEX_API_KEY,
		));

		$server_output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $server_output;
	}
	
	public function replaceMappings($body, $mappings){
		foreach($mappings as $key=>$value){
			if(!is_array($value)){
				$body=str_replace('{{'.$key.'}}', $value, $body);
			}
		}
		return $body;
	}

	public function getMediaInfo($imdb_id){
		$config = new \Imdb\Config();
		$imdb = new \Imdb\Title($imdb_id, $config);
		
		$media_info = [
			'title'=>$imdb->title(),
			'image'=>$imdb->photo(),
			'imdb_rating'=>$imdb->rating().'/10',
			'runtime'=>$imdb->runtime(),
			'director'=>implode(', ', array_column($imdb->director(), 'name')),
		];

		return $media_info;
	}

	public function getPlexEmails(){
		$friends = $this->getXMLObject("https://plex.tv/api/users/");
		$friends = $friends->User;

		$emails=[];
		foreach($friends as $friend){
			$emails[] = (string)$friend->attributes()->email;
		}

		// add your own email
		$account = $this->getXMLObject("https://plex.tv/users/account/?X-Plex-Token=".PLEX_API_KEY);
		$emails[] = (string)$account->attributes()->email;

		// Override for Test
		//$emails = [(string)$account->attributes()->email];

		return $emails;
	}

	public function sendEmail($subject, $html, $emails){
		$emails = (is_array($emails)) ? $emails : [$emails];
		
		$m = new SimpleEmailServiceMessage();
		foreach($emails as $email){
			$m->addTo($email);
		}
		$m->setFrom(EMAIL_FROM);
		$m->setSubject($subject);
		$m->setMessageFromString("", $html);

		$ses = new SimpleEmailService(SES_ACCESS_KEY, SES_SECRET_KEY, SES_REGION_ENDPOINT);
		return $ses->sendEmail($m);
	}
}
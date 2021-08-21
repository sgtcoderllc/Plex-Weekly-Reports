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
			$body=str_replace('{{'.$key.'}}', $value, $body);
		}
		return $body;
	}

	public function getMovieInfo($movie_id){
	   	$movie_url = 'https://api.themoviedb.org/3/movie/'.$movie_id.'?api_key='.MOVIEDB_KEY;
	   	$movie = json_decode(@file_get_contents($movie_url), TRUE);

	   	$omdb_url = 'https://www.omdbapi.com/?i='.$movie['imdb_id'].'&apikey='.OMDB_KEY.'&plot=short&r=json';
	   	$omdb_result = json_decode(@file_get_contents($omdb_url), TRUE);

	   	$data_return = array(
	   		'id'			=> $movie['id'],
	   		'title'			=> $movie['title'],
	   		'image'			=> "https://image.tmdb.org/t/p/w154".$movie['poster_path'],
	   		'year'			=> $omdb_result['Year'],
	   		'tagline'		=> $movie['tagline'],
	   		'synopsis'		=> $movie['overview'],
	   		'runtime'		=> $movie['runtime'],
	   		'imdb'			=> "http://www.imdb.com/title/".$movie['imdb_id'],
			'imdb_rating' 	=> $omdb_result['imdbRating'],
	        'imdb_votes'  	=> $omdb_result['imdbVotes'],
	        'director'    	=> $omdb_result['Director'],
	        'actors'      	=> $omdb_result['Actors'],
	        'genre'      	=> $omdb_result['Genre'],
	        'released'    	=> $omdb_result['Released'],
	        'rating'      	=> $omdb_result['Rated'],
	   	);

	   	return $data_return;
	}

	public function getShowInfo($show_id){
	   	$tv_url = 'https://thetvdb.com/api/'.TVDB_KEY.'/series/'.$show_id;

		$tv_show = $this->getXMLObject($tv_url);

		$tv_show = (array) $tv_show->Series;
	   	
	   	$omdb_url = 'https://www.omdbapi.com/?i='.$tv_show['IMDB_ID'].'&apikey='.OMDB_KEY.'&plot=short&r=json';
		
	   	$omdb_result = json_decode(@file_get_contents($omdb_url), TRUE);

	   	$data_return = array(
	   		'id'			=> $tv_show['id'],
	   		'title'			=> $tv_show['SeriesName'],
	   		'image'			=> "https://thetvdb.com/banners/".$tv_show['poster'],
	   		'year'			=> $omdb_result['Year'],
	   		'tagline'		=> $tv_show['Overview'],
	   		'synopsis'		=> $tv_show['Overview'],
	   		'runtime'		=> $tv_show['Runtime'],
	   		'imdb'			=> "https://www.imdb.com/title/".$tv_show['IMDB_ID'],
			'imdb_rating' 	=> $omdb_result['imdbRating'],
	        'imdb_votes'  	=> $omdb_result['imdbVotes'],
	        'director'    	=> $omdb_result['Director'],
	        'actors'      	=> $omdb_result['Actors'],
	        'genre'      	=> $omdb_result['Genre'],
	        'released'    	=> $omdb_result['Released'],
	        'rating'      	=> $omdb_result['Rated'],
	   	);

	   	return $data_return;
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
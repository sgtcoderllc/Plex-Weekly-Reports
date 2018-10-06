<?php

function getDirectories(&$directories){
	foreach($directories->Directory as $library){
		$key = (string)$library->attributes()->key;
		$type = (string)$library->attributes()->type;
		
		if(strstr($key, 'children')) {
			//subdirectory
			$subsections = getXMLObject(PLEX_URL.$key);
	    }else{
	    	$subsections = getXMLObject(PLEX_URL."/library/sections/".$key.'/all');
	    }

		if(isset($subsections->Video)){
			//get all videos in directory
			getNewVideos($subsections->Video);
		}

		if(isset($subsections->Directory)){
			//recursive load directories to get more videos
			getDirectories($subsections);
		}

	}
}

function getNewVideos($videos){
	global $new_movies, $new_episodes;

	foreach($videos as $item){
		$key = (string)$item->attributes()->key;
		$type = (string)$item->attributes()->type;
		$added_at = date("Y-m-d H:i:s", (int)$item->attributes()->addedAt);

		if($added_at>= date("Y-m-d H:i:s", strtotime("-1 week"))){
			$meta = getXMLObject(PLEX_URL.$key);

			if($type=="movie"){
				$show_id = (string)$meta->Video->attributes()->guid;
				$show_id = preg_replace('/com.plexapp.agents.themoviedb:\/\//', '',$show_id);
				$show_id = explode("?", $show_id);
				$show_id = $show_id[0];	

				$movie = array(
					'key'=>(string)$item->attributes()->key,
					'show_id'=>$show_id,
					'title'=>(string)$item->attributes()->title,
					'summary'=>(string)$item->attributes()->summary,
					'rating'=>(string)$item->attributes()->rating,
					'year'=>(string)$item->attributes()->year,
					'studio'=>(string)$item->attributes()->studio,
				);
				$new_movies[] = $movie;
			}elseif($type=="episode"){
				$show_id = (string)$meta->Video->attributes()->guid;

				$show_id = preg_replace('/com.plexapp.agents.thetvdb:\/\//', '',$show_id);
				$show_info = explode("?", $show_id);
				$show_info = $show_info[0];
				$show_info = explode("/", $show_info);
				$show_id = $show_info[0];
				$season_id = $show_info[1];
				$episode_id = $show_info[2];

				$episode = array(
					'key'=>(string)$item->attributes()->key,
					'show_id'=>$show_id,
					'title'=>(string)$item->attributes()->grandparentTitle,
					'season'=>$season_id,
					'episode'=>$episode_id,
				);
				$new_episodes[$show_id][$season_id][$episode_id] = $episode;
			}
		}
	}
}


function getXMLObject($url){
	$xml = curl_get($url);
	$xml_array = new SimpleXMLElement($xml);

	return $xml_array;
}

function print_r2($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}


function curl_get($url, $options=array()){

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


function replaceMappings($body, $mappings){
	foreach($mappings as $key=>$value){
		$body=str_replace('{{'.$key.'}}', $value, $body);
	}
	return $body;
}


function getMovieInfo($movie_id){
   	$movie_url = 'https://api.themoviedb.org/3/movie/'.$movie_id.'?api_key='.MOVIE_DB_KEY;
   	$movie = json_decode(file_get_contents($movie_url), TRUE);

   	$omdb_url = 'http://www.omdbapi.com/?i='.$movie['imdb_id'].'&apikey='.OMDB_KEY.'&plot=short&r=json';
   	$omdb_result = json_decode(file_get_contents($omdb_url), TRUE);

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

function getEpisodeInfo($movie_id){
   	$tv_url = 'http://thetvdb.com/api/'.TVDB_KEY.'/series/'.$movie_id;
   	$tv_show = getXMLObject($tv_url);

	$tv_show = (array) $tv_show->Series;
   	
   	$omdb_url = 'http://www.omdbapi.com/?i='.$tv_show['IMDB_ID'].'&apikey='.OMDB_KEY.'&plot=short&r=json';

   	$omdb_result = json_decode(file_get_contents($omdb_url), TRUE);

   	$data_return = array(
   		'id'			=> $tv_show['id'],
   		'title'			=> $tv_show['SeriesName'],
   		'image'			=> "http://thetvdb.com/banners/".$tv_show['poster'],
   		'year'			=> $omdb_result['Year'],
   		'tagline'		=> $tv_show['Overview'],
   		'synopsis'		=> $tv_show['Overview'],
   		'runtime'		=> $tv_show['Runtime'],
   		'imdb'			=> "http://www.imdb.com/title/".$tv_show['IMDB_ID'],
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

function getPlexEmails(){
	$friends = getXMLObject("https://plex.tv/api/users/?X-Plex-Token=".PLEX_API_KEY);
	$friends = $friends->User;

	$emails=array();
	
	foreach($friends as $friend){
		$emails[] = (string)$friend->attributes()->email;
	}

	//add your own email
	$account = getXMLObject("https://plex.tv/users/account/?X-Plex-Token=".PLEX_API_KEY);
	$emails[] = (string)$account->attributes()->email;

	return $emails;
}
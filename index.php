<?php

require_once("includes/common.php");

global $new_movies, $new_episodes;

$new_movies = array();
$new_episodes = array();

$sections = getXMLObject(PLEX_URL."/library/sections");

getDirectories($sections);

//echo 'new Movies';print_r2($new_movies);echo 'new episodes';print_r2($new_episodes);exit;

$html_template = file_get_contents("templates/template.php");
$shows_template = file_get_contents("templates/shows.php");
$movies_template = file_get_contents("templates/movies.php");


if(count($new_movies)>0){
	foreach($new_movies as $movie){

		$movie_info = getMovieInfo($movie['show_id']);

		$array_map = array(
			'title'=>$movie_info['title'],
			'year'=>$movie_info['year'],
			'genre'=>$movie_info['genre'],
			'director'=>$movie_info['director'],
			'actors'=>$movie_info['actors'],
			'synopsis'=>$movie_info['synopsis'],
			'runtime'=>$movie_info['runtime'],
			'released'=>$movie_info['released'],
			'rating'=>$movie_info['rating'],
			'imdb_rating'=>$movie_info['imdb_rating'],
			'rating'=>$movie_info['rating'],
			'imdb_votes'=>$movie_info['imdb_votes'],
			'image'=>$movie_info['image'],
			'imdb_link'=>$movie_info['imdb'],
		);

		$movies_html.= replaceMappings($movies_template, $array_map);
	}
}else{
	$movies_html = '<tr>
					    <td style="vertical-align:top;background-color:#E8E8E8;padding:5px 10px 5px 10px;" colspan="2">
					    	<h2 style="padding-bottom: 2px;">No New Movies</h2>
					    </td>
					</tr>';
}


if(count($new_episodes)>0){
	foreach($new_episodes as $key=>$season){
		$episodes_text = array();

		foreach($season as $season_key=>$season_value){
			$episodes_text[$season_key]="Season ".$season_key.' (E'.implode(",E", array_keys($season_value)).')';
		}

		$episodes_text = implode(", ", $episodes_text);

		$episode_info = getEpisodeInfo($key);

		$array_map = array(
			'title'=>$episode_info['title'],
			'year'=>$episode_info['year'],
			'genre'=>$episode_info['genre'],
			'director'=>$episode_info['director'],
			'actors'=>$episode_info['actors'],
			'synopsis'=>$episode_info['synopsis'],
			'runtime'=>$episode_info['runtime'],
			'released'=>$episode_info['released'],
			'rating'=>$episode_info['rating'],
			'imdb_rating'=>$episode_info['imdb_rating'],
			'rating'=>$episode_info['rating'],
			'imdb_votes'=>$episode_info['imdb_votes'],
			'image'=>$episode_info['image'],
			'episodes_text'=>$episodes_text,
			'imdb_link'=>$episode_info['imdb'],
		);

		$shows_html.= replaceMappings($shows_template, $array_map);
	}
}else{
	$shows_html = '<tr>
					    <td style="vertical-align:top;background-color:#E8E8E8;padding:5px 10px 5px 10px;" colspan="2">
					    	<h2 style="padding-bottom: 2px;">No New TV Shows</h2>
					    </td>
					</tr>';
}

$array_map = array(
	'report_title'=>REPORT_TITLE,
	'report_subtitle'=>REPORT_SUBTITLE,
	'movies'=>$movies_html,
	'shows'=>$shows_html,
);

$html = replaceMappings($html_template, $array_map);


echo $html;
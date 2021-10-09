<?php

function exec_plex(){
	$timer_start = microtime(true);
	$plex_export_version = 1;
	ini_set('memory_limit', '512M');
	set_error_handler('plex_error_handler');
	error_reporting(E_ALL ^ E_NOTICE | E_WARNING);

	$Core = new Core;

	// Set-up
	$defaults = array(
		'plex-url' => 'http://localhost:32400',
		'data-dir' => '../plex-data',
		'thumbnail-width' => 150,
		'thumbnail-height' => 250,
		'sections' => 'all',
		'sort-skip-words' => 'a,the,der,die,das',
		'token' => ''
	);

	global $options;
	$options = hl_parse_arguments($options, $defaults);
	
	if(substr($options['plex-url'],-1)!='/') $options['plex-url'] .= '/'; // Always have a trailing slash
	$options['absolute-data-dir'] = dirname(__FILE__).'/'.$options['data-dir']; // Run in current dir (PHP CLI defect)
	$options['sort-skip-words'] = (array) explode(',', $options['sort-skip-words']); # comma separated list of words to skip for sorting titles
	
	// Create the http header with a X-Plex-Token in it	if specified
	if (strlen($options['token']) == 0){
		$headers = array(
			'http'=>array(
    	'method'=>"GET"                 
			)
		);
	} else {
		$headers = array(
		'http'=>array(
		  'method'=>"GET",
		  'header'=>"X-Plex-Token: ".$options['token']              
			)
		);
	}

	global $context;
	$context = stream_context_create($headers);
	
	check_dependancies(); // Check everything is enabled as necessary


	// Load details about all sections
	$all_sections = load_all_sections();
	if(!$all_sections) {
		plex_error('Could not load section data, aborting');
		exit();
	}

	// If user wants to show all (supported) sections...
	if($options['sections'] == 'all') {
		$sections = $all_sections;
	} else {
		// Otherwise, match sections by Title first, then ID
		$sections_to_show = array_filter(explode(',',$options['sections']));
				
		$section_titles = array();
		foreach($all_sections as $i=>$section) $section_titles[strtolower($section['title'])] = $i;
		foreach($sections_to_show as $section_key_or_title) {
			
			$section_title = strtolower(trim($section_key_or_title));
			if(array_key_exists($section_title, $section_titles)) {
				$section_id = $section_titles[$section_title];
				//$sections[$section_id] = $all_sections[$section_id];

				$sections[$all_sections[$section_id]['type']] = $all_sections[$section_id];
				continue;
			}
						
			$section_id = intval($section_key_or_title);
			if(array_key_exists($section_id, $all_sections)) {
				$sections[$section_id] = $all_sections[$section_id];
				continue;
			}
			
			plex_error('Could not find section: '.$section_key_or_title);
			
		} // end foreach: $sections_to_show
	} // end if: !all sections


	// If no sections found (or matched)
	$num_sections = count($sections);
	if($num_sections==0) {
		plex_error('No sections were found to scan');
		exit();
	}


	// Load details about each section
	$total_items = 0;
	foreach($sections as $i=>$section) {
		plex_log('Scanning section: '.$section['title']);

		$items = load_items_for_section($section);

		if(!$items) {
			plex_error('No items were added for '.$section['title'].', skipping');
			$sections[$i]['num_items'] = 0;
			$sections[$i]['items'] = array();
			continue;
		}
		
		$num_items = count($items);
		if($section['type']=='show') {
			$num_items_episodes = 0;
			foreach($items as $item) $num_items_episodes += $item['num_episodes'];
			$total_items += $num_items_episodes;
		} else {
			$total_items += $num_items;	
		}

		$sorts_title = $sorts_release = $sorts_added_at = array();
		$raw_section_genres = array();

		foreach($items as $key=>$item) {
			$title_sort = strtolower($item['title']);
			$title_first_space = strpos($title_sort, ' ');
			if($title_first_space>0) {
				$title_first_word = substr($title_sort, 0, $title_first_space);
				if(in_array($title_first_word, $options['sort-skip-words'])) {
					$title_sort = substr($title_sort, $title_first_space+1);
				}
			}
			$sorts_title[$key] = $title_sort;
			$sorts_release[$key] = @strtotime($item['release_date']);
			if(is_array($item['genre']) and count($item['genre'])>0) {
				foreach($item['genre'] as $genre) {
					$raw_section_genres[$genre]++;
				}
			}
			$sorts_added_at[$key] = $item['addedAt'];
		} // end foreach: $items (for sorting)

		$section_genres = array();
		if(count($raw_section_genres)>0) {
			arsort($raw_section_genres);
			foreach($raw_section_genres as $genre=>$genre_count) {
				$section_genres[] = array(
					'genre' => $genre,
					'count' => $genre_count,
				);
			}
		}
		
		$sections[$i]['num_items'] = $num_items;
		$sections[$i]['items'] = $items;
		$sections[$i]['genres'] = $section_genres;

	} // end foreach: $sections_to_export





	// Added to grab additional info and cleanup
	$new_movies = $sections['movie']['items'];
	$new_shows = $sections['show']['items'];

	$movies_array = array();
	$shows_array = array();


	if(count($new_movies)>0){
		foreach($new_movies as $movie){
			$media_info = $Core->getMediaInfo($movie['imdb']);

			$movie['image'] = $media_info['image'];
			$movie['imdb_rating'] = $media_info['imdb_rating'];
			$movie['runtime'] = $media_info['runtime'];
			$movie['director'] = $media_info['director'];

			$movies_array[] = $movie;
		}
	}

	if(count($new_shows)>0){
		foreach($new_shows as $show_key=>$show){
			$episodes_text = array();

			foreach($show['seasons'] as $season_id=>$season){
				$episodes_text[$season_id]=$season['title'].' (E'.implode(", E", array_values(array_column($season['episodes'], 'index'))).')';
			}
			
			$episodes_text = implode(", ", $episodes_text);
			$media_info = $Core->getMediaInfo($show['imdb']);
			
			$show['image'] = $media_info['image'];
			$show['imdb_rating'] = $media_info['imdb_rating'];
			$show['runtime'] = $media_info['runtime'];
			$show['director'] = $media_info['director'];


			$show['episodes_text'] = $episodes_text;
			
			$shows_array[] = $show;
		}
	}

	// Output all data
	$duration = microtime(true) - $timer_start;
	$duration = round(($duration/60),2);

	$output = array(
		'status' => 'success',
		'version' => $plex_export_version,
		'last_generated' => time()*1000,
		'last_updated' => date('Y-m-d H:i:s',time()),
		'total_items' => $total_items,
		'num_sections' => $num_sections,
		'sections' => $sections,
		'movies'=>$movies_array,
		'shows'=>$shows_array,
		'duration' => $duration,
	);

	$packed_js = json_encode($output);
	
	$filename = $options['absolute-data-dir'].'/data.json';
	$bytes_written = file_put_contents($filename, $packed_js);
	if(!$bytes_written) {
		plex_error('Could not save JSON data to '.$filename.', please make sure directory is writeable');
		exit();
	}

	plex_log('Plex Export completed in '.$duration.' minutes');
}

function email_plex(){
	// Get the html report
	$html = file_get_contents(PLEX_REPORT_URL);

	// get emails
	$Core = new Core;

	$emails = $Core->getPlexEmails();
	$subject = EMAIL_SUBJECT;

	foreach($emails as $email){
		$Core->sendEmail($subject, $html, $email);
	}
}

/**
 * Parse a Movie
 **/
function load_data_for_movie($el) {
	global $options;
	global $context;

	$_el = $el->attributes();
	$key = intval($_el->ratingKey);
	if($key<=0) return false;
	$title = strval($_el->title);
	plex_log('Scanning movie: '.$title);

	$added_at = date("Y-m-d H:i:s", strval($_el->addedAt));
	if(!($added_at>= date("Y-m-d H:i:s", strtotime("-1 week")))) return array();
	
	$url = $options['plex-url'].'library/metadata/'.$key;
	$xml = load_xml_from_url($url)->Video;

	$guids = [];
	foreach($xml->xpath('Guid') as $path) {
		$guid = strval($path->attributes()->id);

		if(strpos($guid, 'imdb://')!==FALSE){
			$re = '/^imdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['imdb'] = $matches['id'] ?? false;
		}

		if(strpos($guid, 'tmdb://')!==FALSE){
			$re = '/^tmdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['tmdb'] = $matches['id'] ?? false;
		}

		if(strpos($guid, 'tvdb://')!==FALSE){
			$re = '/^tvdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['tvdb'] = $matches['id'] ?? false;
		}
	}

	$genres = [];
	foreach($xml->xpath('Genre') as $path) {
	    $genres[] = strval($path->attributes()->tag);
	}

	$directors = [];
	foreach($xml->xpath('Director') as $path) {
	    $directors[] = strval($path->attributes()->tag);
	}

	$actors = [];
	foreach($xml->xpath('Role') as $path) {
		if(count($actors)>5) break;

	    $actors[] = strval($path->attributes()->tag);
	}

	$item = array(
		'key' => $key,
		'type' => 'movie',
		'addedAt' => intval($xml->attributes()->addedAt),

		'title'=>strval($_el->title),
		'year'=>($_el->year)?intval($_el->year):NULL,
		'genre'=>($genres) ? implode(', ', $genres) : NULL,
		'director'=>($directors) ? implode(', ', $directors) : NULL,
		'actors'=>($actors) ? implode(', ', $actors) : NULL,
		'synopsis'=>($_el->summary)?strval($_el->summary):NULL,
		'runtime'=>NULL,
		'released'=>($_el->originallyAvailableAt)?date('m/d/Y', strtotime(strval($_el->originallyAvailableAt))):NULL,
		'rating' => ($_el->contentRating)?strval($_el->contentRating):false,
		
		'image'=>NULL,
		'imdb_link'=>($guids['imdb']) ? 'https://www.imdb.com/title/'.$guids['imdb'] : NULL,

		'imdb'=>$guids['imdb'] ?? NULL,
		'tmdb'=>$guids['tmdb'] ?? NULL,
		'tvdb'=>$guids['tvdb'] ?? NULL,
	);

	return $item;

} // end func: load_data_for_movie



/**
 * Parse a TV Show
 **/
function load_data_for_show($el) {
	global $options;

	$_el = $el->attributes();
	$key = intval($_el->ratingKey);
	if($key<=0) return false;
	$title = strval($_el->title);
	plex_log('Scanning show: '.$title);

	$url = $options['plex-url'].'library/metadata/'.$key;
	$xml = load_xml_from_url($url)->Directory;
	
	$guids = [];
	foreach($xml->xpath('Guid') as $path) {
		$guid = strval($path->attributes()->id);

		if(strpos($guid, 'imdb://')!==FALSE){
			$re = '/^imdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['imdb'] = $matches['id'] ?? false;
		}

		if(strpos($guid, 'tmdb://')!==FALSE){
			$re = '/^tmdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['tmdb'] = $matches['id'] ?? false;
		}

		if(strpos($guid, 'tvdb://')!==FALSE){
			$re = '/^tvdb:\/\/(?<id>.+)/';
			preg_match($re, $guid, $matches);

			// Print the entire match result
			$guids['tvdb'] = $matches['id'] ?? false;
		}
	}

	$genres = [];
	foreach($xml->xpath('Genre') as $path) {
	    $genres[] = strval($path->attributes()->tag);
	}

	$directors = [];
	foreach($xml->xpath('Director') as $path) {
	    $directors[] = strval($path->attributes()->tag);
	}

	$actors = [];
	foreach($xml->xpath('Role') as $path) {
		if(count($actors)>5) break;

	    $actors[] = strval($path->attributes()->tag);
	}

	$item = array(
		'key' => $key,
		'type' => 'show',
		'addedAt' => NULL,

		'title'=>strval($_el->title),
		'year'=>($_el->year)?intval($_el->year):NULL,
		'genre'=>($genres) ? implode(', ', $genres) : NULL,
		'director'=>($directors) ? implode(', ', $directors) : NULL,
		'actors'=>($actors) ? implode(', ', $actors) : NULL,
		'synopsis'=>($_el->summary)?strval($_el->summary):NULL,
		'runtime'=>NULL,
		'released'=>($_el->originallyAvailableAt)?date('m/d/Y', strtotime(strval($_el->originallyAvailableAt))):NULL,
		'rating' => ($_el->contentRating)?strval($_el->contentRating):NULL,
		
		'image'=>NULL,
		'imdb_link'=>($guids['imdb']??NULL) ? 'https://www.imdb.com/title/'.$guids['imdb'] : NULL,

		'imdb'=>$guids['imdb'] ?? NULL,
		'tmdb'=>$guids['tmdb'] ?? NULL,
		'tvdb'=>$guids['tvdb'] ?? NULL,

		'num_episodes' => intval($_el->leafCount),
		'num_seasons' => NULL,
		'seasons' => [],
	);
	
	$url = $options['plex-url'].'library/metadata/'.$key.'/children';
	$xml = load_xml_from_url($url);
	if(!$xml) {
		plex_error('Could not load additional metadata for '.$title);
		return $item;
	}
	
	$seasons = array();
	$season_sort_order = array();
	foreach($xml->Directory as $el2) {
		if($el2->attributes()->type!='season') continue;
		$season_key = intval($el2->attributes()->ratingKey);
		$season_sort_order[intval($el2->attributes()->index)] = $season_key;
		$season = array(
			'key' => $season_key,
			'title' => strval($el2->attributes()->title),
			'num_episodes' => intval($el2->attributes()->leafCount),
			'actual_episodes' => 0,
			'episodes' => array(),
			'index' => intval($el2->attributes()->index)
		);
		
		$url = $options['plex-url'].'library/metadata/'.$season_key.'/children';
		$xml2 = load_xml_from_url($url);
		if(!$xml2) {
			plex_error('Could not load season data for '.$item['title'].' : '.$season['title']);
		}
		
		foreach($xml2->Video as $el3) {
			if($el3->attributes()->type!='episode') continue;
			
			$added_at = date("Y-m-d H:i:s", strval($el3->attributes()->addedAt));
			if(!($added_at>= date("Y-m-d H:i:s", strtotime("-1 week")))) continue;

			$episode_key = intval($el3->attributes()->ratingKey);

			$episode = array(
				'key' => $episode_key,
				'title' => strval($el3->attributes()->title),
				'index' => intval($el3->attributes()->index),
				'summary' => strval($el3->attributes()->summary),
				'rating' => floatval($el3->attributes()->rating),
				'duration' => floatval($el3->attributes()->duration),
				'view_count' => intval($el3->attributes()->viewCount)
			);
			$season['episodes'][$episode_key] = $episode;
			$season['actual_episodes']++;
		}
				
		if($season['actual_episodes']>0) $seasons[$season_key] = $season;
	}	
	ksort($season_sort_order);
	$item['season_sort_order'] = array_values($season_sort_order);
	$item['num_seasons'] = count($seasons);
	if($item['num_seasons']>0) $item['seasons'] = $seasons;
	
	if(empty($item['num_seasons'])) return array();
	
	$item['addedAt'] = intval($xml->attributes()->addedAt);

	return $item;
} // end func: load_data_for_show


/**
 * Load all supported sections from given Plex API endpoint
 **/
function load_all_sections() {
	global $options;
	$url = $options['plex-url'].'library/sections';

	$xml = load_xml_from_url($url);
	if(!$xml) return false;

	$total_sections = intval($xml->attributes()->size);
	if($total_sections<=0) {
		plex_error('No sections were found in this Plex library');
		return false;
	}

	$sections = array();
	$num_sections = 0;

	foreach($xml->Directory as $el) {
		$_el = $el->attributes();
		$key = intval($_el->key);
		$type = strval($_el->type);
		$title = strval($_el->title);
		if($type=='movie' or $type=='show') {
			$sections[$key] = array('key'=>$key, 'type'=>$type, 'title'=>$title);
			$num_sections++;
		} else {
			plex_error('Skipping section of unknown type: '.$type);
		}
	}

	if($num_sections==0) {
		plex_error('No valid sections found, aborting');
		return false;
	}

	return $sections;

} // end func: load_all_sections



/**
 * Load all items present in a section
 **/
function load_items_for_section($section) {

	global $options;
	$url = $options['plex-url'].'library/sections/'.$section['key'].'/all';

	$xml = load_xml_from_url($url);
	if(!$xml) return false;

	$num_items = intval($xml->attributes()->size);
	if($num_items<=0) {
		plex_error('No items were found in this section, skipping');
		return false;
	}

	switch($section['type']) {
		case 'movie':
			$object_to_loop = $xml->Video;
			$object_parser = 'load_data_for_movie';
			break;
		case 'show':
			$object_to_loop = $xml->Directory;
			$object_parser = 'load_data_for_show';
			break;
		default:
			plex_error('Unknown section type provided to parse: '.$section['type']);
			return false;
	}

	$items = array();
	foreach($object_to_loop as $el) {
		$item = $object_parser($el);
		if($item) $items[$item['key']] = $item;

	}

	return $items;

} // end func: load_items_for_section



/**
 * Load URL and parse as XML
 **/
function load_xml_from_url($url) {
	global $options;
	global $context;
	
	if(!@fopen($url, 'r', false, $context)) {
		plex_error('The Plex library could not be found at '.$options['plex-url']);
		return false;
	}

	$xml = file_get_contents($url, false, $context);
	$xml = @simplexml_load_string($xml);
	if(!$xml) {
		plex_error('Data could not be read from the Plex server at '.$url);
		return false;
	}

	if(!$xml) {
		plex_error('Invalid XML returned by the Plex server, aborting');
		return false;
	}

	return $xml;

} // end func: load_xml_from_url


/**
 * Output a message to STDOUT
 **/
function plex_log($str) {
	$str = @date('H:i:s')." $str\n";
	fwrite(STDOUT, $str);
} // end func: plex_log



/**
 * Output an error to STDERR
 **/
function plex_error($str) {
	$str = @date('H:i:s')." Error: $str\n";
	fwrite(STDERR, $str);
} // end func: plex_error



/**
 * Capture PHP error events
 **/
function plex_error_handler($errno, $errstr, $errfile=null, $errline=null) {
	if(!(error_reporting() & $errno)) return;
	$str = @date('H:i:s')." Error: $errstr". ($errline?' on line '.$errline:'') ."\n";
	fwrite(STDERR, $str);
} // end func: plex_error_handler



/**
 * Check environment meets dependancies, exit() if not
 **/
function check_dependancies() {
	global $options;
	$errors = false;

	if(!extension_loaded('simplexml')) {
		plex_error('SimpleXML is not enabled');
		$errors = true;
	}

	if(!ini_get('allow_url_fopen')) {
		plex_error('Remote URL access is disabled (allow_url_fopen)');
		$errors = true;
	}

	if(!is_writable($options['absolute-data-dir'])) {
		plex_error('Data directory is not writeable at '.$options['absolute-data-dir']);
		$errors = true;
	}

	if($errors) {
		plex_error('Failed one or more dependancy checks; aborting');
		exit();
	}

} // end func: check_dependancies



/**
 * Produce output array from merger of inputs and defaults
 **/
function hl_parse_arguments($cli_args, $defaults) {
	$output = (array) $defaults;
	foreach($cli_args as $key=>$value) {
		/*
		if(substr($str,0,1)!='-') continue;
		print_r2($cli_args);exit;
		$eq_pos = strpos($str, '=');
		$key = substr($str, 1, $eq_pos-1);
		if(!array_key_exists($key, $output)) continue;
		*/
		if(!empty($value)){
			$output[$key] = $value;
		}
	}
	
	return $output;
} // end func: hl_parse_arguments



/**
 * Return plural form if !=1
 **/
function hl_inflect($num, $single, $plural=false) {
	if($num==1) return $single;
	if($plural) return $plural;
	return $single.'s';
} // end func: hl_inflect

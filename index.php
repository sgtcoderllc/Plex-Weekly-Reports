<?php
require_once("includes/common.php");

$data=json_decode(file_get_contents(PLEX_REPORT_URL.'/plex-data/data.json'), TRUE);

$new_movies = (array)$data['movies'];
$new_shows = (array)$data['shows'];

$html_template = file_get_contents("templates/template.php");
$shows_template = file_get_contents("templates/shows.php");
$movies_template = file_get_contents("templates/movies.php");


if(count($new_movies)>0){
	foreach($new_movies as $key => $movie){
		$shows_html.= $Core->replaceMappings($movies_template, $movie);
	}
}else{
	$movies_html = '
		<tr>
		    <td style="padding-bottom: 30px;">
		        <table role="presentation" border="2" cellpadding="10" cellspacing="0" width="100%" class="bg_light">
			        <td valign="middle">
			          	<div class="text-blog" style="text-align: left; padding-left:25px;">
			                <h2>No New Movies</h2>
			          	</div>
			        </td>
		        </table>
		    </td>
		</tr>
	';
}


if(count($new_shows)>0){
	foreach($new_shows as $key=>$show){
		$shows_html.= $Core->replaceMappings($shows_template, $show);
	}
}else{
	$shows_html = '
		<tr>
		    <td style="padding-bottom: 30px;">
		        <table role="presentation" border="2" cellpadding="10" cellspacing="0" width="100%" class="bg_light">
			        <td valign="middle">
			          	<div class="text-blog" style="text-align: left; padding-left:25px;">
			                <h2>No New TV Shows</h2>
			          	</div>
			        </td>
		        </table>
		    </td>
		</tr>
	';
}

$array_map = array(
	'report_title'=>REPORT_TITLE,
	'report_subtitle'=>REPORT_SUBTITLE,
	'movies'=>$movies_html,
	'shows'=>$shows_html,
	'last_updated'=>$data['last_updated'],
	'duration'=>$data['duration'],
);

$html = $Core->replaceMappings($html_template, $array_map);

echo $html;
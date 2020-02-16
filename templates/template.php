<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>{{report_title}}</title>
    </head>
    <body>
        <table style="border:0px;width:100%; text-align:center;">
            <tr>
                <th colspan="2" style="width:100%; background-color:#CCCCCC;">
                    <h1 style="width:100%; background-color:#CCCCCC;">{{report_title}}</h1>
                    <h3 style="width:100%; background-color:#CCCCCC;">{{report_subtitle}}</h3>
                    <h4 style="padding-top:2px;">By MJB Code LLC</h4>
                </th>
            </tr>
        </table>
        <table style="border:0px;width:100%; text-align:left;">
            <tr>
                <th colspan="2" style="width:100%; background-color:#CCCCCC; text-align:center;vertical-align:middle;"><h1>New Movies</h1></th>
            </tr>
            {{movies}}
        </table>
        <table style="border:0px;width:100%; text-align:left;">
            <tr>
                <th colspan="2" style="width:100%; background-color:#CCCCCC; text-align:center;vertical-align:middle;"><h1>New TV Shows</h1></th>
            </tr>
            {{shows}}
        </table>
        <table style="border:0px;width:100%; text-align:center;">
			<tr>
                <th colspan="2" style="width:100%; background-color:#CCCCCC; ">
                    <p style="width:100%; background-color:#CCCCCC; text-align:center;">
                        Summary<br />
                       	Last Updated: {{last_updated}}<br />
						Duration: {{duration}} minutes
                    </p>
                </th>
            </tr>
            <tr>
                <th colspan="2" style="width:100%; background-color:#CCCCCC; ">
                    <p style="width:100%; background-color:#CCCCCC; text-align:center;">
                        Credits<br />
                        <a href="https://www.themoviedb.org/" target="_blank">The Movie DB</a><br />
                        <a href="https://www.thetvdb.com/" target="_blank">The TV DB</a><br />
                        <a href="http://www.omdbapi.com/" target="_blank">OMBD API</a><br />
                        <a href="https://github.com/Dachande663/Plex-Export/blob/master/cli.php" target="_blank">Plex Export CLI</a>
                    </p>
                </th>
            </tr>
        </table>
    </body>
</html>
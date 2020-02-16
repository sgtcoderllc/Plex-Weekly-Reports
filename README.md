# Plex Weekly Reports
> Plex Weekly Reports done the right way.

[![GitHub](https://img.shields.io/github/license/MJBCode/Plex-Weekly-Reports.svg)](https://opensource.org/licenses/GPL-3.0)
[![GitHub last commit](https://img.shields.io/github/last-commit/MJBCode/Plex-Weekly-Reports.svg)](https://github.com/MJBCode/Plex-Weekly-Reports/commits/master)
[![GitHub tag](https://img.shields.io/github/tag/MJBCode/Plex-Weekly-Reports.svg)](https://github.com/MJBCode/Plex-Weekly-Reports/tags)

## Overview
I started this project after messing with plexReports and Tautulli Newsletters. plexReports has many bugs and the HTML is too big for GMail. It was also written in Ruby and was not easy to modify. Even after fixing it up it still had problems. Tautulli is still too beta in order to be used as a Newsletter and due to the lack of customization. I also do not like the format of the emails and they are not responsive.

I have decided to then build a reporting tool for recently added media coded in a LAMP stack environment. The reports should work in all email clients and are mobile responsive. I have tied into the Plex API, PlexTV API, OMDB, TheTVDB, and TheMovieDB to pull the reports in the past week along with all the meta data. I have also integrated with MailGun API to send out the emails.

## Installation
This project assumes that you have general knowledge of a LAMP stack environment.
- Simply upload the project to your public root, rename configs.php.sample to configs.php and fillout the missing information.
- PLEX URL is your Plex URL
- Plex API is the API token of your Plex server. Login to your plex, click on a video and click on the 3 dots and select get info, then click view XML. In the URL, your token will be in: X-Plex-Token.
- /index.php is the Plex report that you can view.
- /cron.php is the cron job to send out the email. You can set this every Friday at about 0800 or a time of your choice.

## Screenshots
#### Email Preview
[![Image from Gyazo](https://i.gyazo.com/ca15001b568eebbc558aa130536ebaf6.png)](https://i.gyazo.com/ca15001b568eebbc558aa130536ebaf6.png)

## Future Plans
- Mandrill Integration
- SMTP Integration
- Direct Linking to the Plex Server to watch the media
- Improvement and cleanup of the project
- Integration to other API's to pull more data

## Meta
Michael J Brancato – [@theveterancoder](https://github.com/theveterancoder) – mike@mjbcode.com

Distributed under the GNU GENERAL PUBLIC LICENSE Version 3. See ``LICENSE`` for more information.

[https://github.com/MJBCode/Plex-Weekly-Reports](https://github.com/MJBCode/Plex-Weekly-Reports)

## Contributing

1. Fork it (<https://github.com/MJBCode/Plex-Weekly-Reports/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

## Questions
If you have any questions or feedback, please email me at mike@mjbcode.com

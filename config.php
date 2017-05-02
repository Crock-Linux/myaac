<?php
/**
 * This is MyAAC's Main Configuration file
 *
 * All the default values are kept here, you should not modify it but use
 * a config.local.php file instead to override the settings from here.
 *
 * This is a piece of PHP code so PHP syntax applies!
 * For boolean values please use true/false.
 *
 * Minimally 'server_path' directive have to be filled, other options are optional.
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.0.2
 * @link      http://my-aac.org
 */

$config = array(
	// directories & files
	'server_path' => '', // path to the server directory (same directory where config file is located)

	'template' => 'kathrine', // template used by website (kathrine, tibiacom)
	'template_allow_change' => true, // allow users to choose their own template while browsing website?

	// what client version are you using on this OT?
	// used for the Downloads page and some templates aswell
	'client' => 1098, // 954 = client 9.54

	'friendly_urls' => false, // mod_rewrite is required for this, it makes links looks more elegant to eye, and also are SEO friendly (example: http://my-aac.org/guilds/Testing instead of http://my-aac.org/?subtopic=guilds&name=Testing)
	'gzip_output' => false, // gzip page content before sending it to the browser, uses less bandwidth but more cpu cycles

	// gesior backward support (templates & pages)
	// allows using gesior templates and pages with myaac
	'backward_support' => true,

	// head options (html)
	'meta_description' => 'Tibia is a free massive multiplayer online role playing game (MMORPG).', // description of the site
	'meta_keywords' => 'free online game, free multiplayer game, ots, open tibia server', // keywords list separated by commas
	'title_separator' => ' - ',

	// footer
	'footer' => ''/*'<br/>Your Server &copy; 2016. All rights reserved.'*/,

	// site closed
	'site_closed' => false,
	'site_closed_title' => 'Closed',
	'site_closed_message' => 'Server is under maintance, please visit later.<br/><br/>',

	'debug_level' => 0, // 0 - disabled, 1 - show load time, 2 - show db query counter, 3 - both, 4 - memory usage, 5 - load time & load time, 6 - queries & memory usage, 7 - all

	'language' => 'en', // default language (currently only 'en' available)
	'language_allow_change' => false,

	'visitors_counter' => true,
	'visitors_counter_ttl' => 10, // how long visitor will be marked as online (in minutes)
	'views_counter' => true,

	// cache system. by default file cache is used
	'cache_engine' => 'auto', // apc, eaccelerator, xcache, file, auto, or blank to disable.
	'cache_prefix' => 'myaac_', // have to be unique if running more MyAAC instances on the same server, ignored when using file cache.

	// database details (leave blank for auto detect from config.lua)
	'database_host' => '',
	'database_port' => '', // leave blank to default 3306
	'database_user' => '',
	'database_password' => '',
	'database_name' => '',

	// multiworld system
	'multiworld' => false, // use multiworld system?
	'worlds' => array( // list of worlds
		//'1' => 'Your World Name',
		//'2' => 'Your Second World Name'
	),

	// account
	'account_management' => true, // disable if you're using other method to manage users (fe. tfs account manager)
	'account_mail_verify' => false, // force users to confirm their email addresses when registering account
	'account_mail_unique' => true, // email addresses cannot be duplicated? (one account = one email)
	'account_premium_days' => 0, // default premium days on new account
	'account_premium_points' => 0, // default premium points on new account
	'account_welcome_mail' => true, // send welcome email when user registers
	'account_mail_change' => 2, // how many days user need to change email to account - block hackers
	'account_country' => true, // user will be able to set country of origin when registering account, this information will be viewable in others places aswell

	// mail
	'mail_enabled' => false, // is aac maker configured to send e-mails?
	'mail_address' => 'no-reply@your-server.org', // server e-mail address (from:)
	'mail_admin' => 'your-address@your-server.org', // admin email address, where mails from contact form will be sent
	'mail_signature' => array( // signature that will be included at the end of every message sent using _mail function
		'plain' => "--\nMy Server,\nhttp://www.myserver.com",
		'html' => '<br/>My Server,\n<a href="http://www.myserver.com">myserver.com</a>'
	),
	'smtp_enabled' => false, // send by smtp or mail function (set false if use mail function)
	'smtp_host' => '', // mail host
	'smtp_port' => 25, // 25 (default) / 465 (ssl, e.g. gmail)
	'smtp_auth' => true, // need authorization?
	'smtp_user' => 'admin@example.org',
	'smtp_pass' => '',

	// reCAPTCHA (prevent spam bots)
	'recaptcha_enabled' => false, // enable recaptcha verification code
	'recaptcha_site_key' => '', // get your own public and private keys at https://www.google.com/recaptcha
	'recaptcha_secret_key' => '',
	'recaptcha_theme' => 'light', // light, dark

	//
	'generate_new_reckey' => true,				// let player generate new recovery key, he will receive e-mail with new rec key (not display on page, hacker can't generate rec key)
	'generate_new_reckey_price' => 20,			// price for new recovery key
	'send_mail_when_change_password' => true,	// send e-mail with new password when change password to account
	'send_mail_when_generate_reckey' => true,	// send e-mail with rec key (key is displayed on page anyway when generate)

	// new character config
	'character_samples' => array( // vocations, format: ID_of_vocation => 'Name of Character to copy'
		//0 => 'Rook Sample',
		1 => 'Sorcerer Sample',
		2 => 'Druid Sample',
		3 => 'Paladin Sample',
		4 => 'Knight Sample'
	),

	// town list used when creating character
	// won't be displayed if there is only one item (rookgaard for example)
	'character_towns' => array(1),

	// list of towns
	'towns' => array(
		0 => 'No town',
		1 => 'Sample town'
	),

	'characters_per_account' => 10,	// max. number of characters per account

	// guilds
	'guild_management' => true, // enable guild management system on the site?
	'guild_need_level' => 1, // min. level to form a guild
	'guild_need_premium' => true, // require premium account to form a guild?
	'guild_image_size_kb' => 80, // maximum size of the guild logo image in KB (kilobytes)
	'guild_description_chars_limit' => 1000, // limit of guild description
	'guild_description_lines_limit' => 6, // limit of lines, if description has more lines it will be showed as long text, without 'enters'
	'guild_motd_chars_limit' => 150, // limit of MOTD (message of the day) that is shown later in the game on the guild channel

	'quests' => array(), // quests list (displayed in character view), name => storage

	'signature_enabled' => true,
	'signature_type' => 'tibian', // signature engine to use: tibian, mango, gesior
	'signature_cache_time' => 5, // how long to store cached file (in minutes)

	// online page
	'online_record' => true, // display players record?
	'online_vocations' => false, // display vocation statistics?
	'online_vocations_images' => false, // display vocation images?
	'online_skulls' => false, // display skull images
	'online_afk' => false,

	// support list page
	'team_style' => 2, // 1/2 (1 - normal table, 2 - in boxes, grouped by group id)
	'team_display_status' => true,
	'team_display_lastlogin' => true,
	'team_display_world' => false,

	// bans page
	'bans_limit' => 50,
	'bans_display_all' => true, // should all bans be displayed? (sorted page by page)

	// highscores page
	'highscores_vocation_box' => true, // show 'Choose a vocation' box on the highscores (allowing peoples to sort highscores by vocation)?
	'highscores_vocation' => true, // show player vocation under his nickname?
	'highscores_frags' => false, // show 'Frags' tab (best fraggers on the server)? Only 0.3
	'highscores_country_box' => false, // doesnt work yet! (not implemented)
	'highscores_groups_hidden' => 4, // this group id and higher won't be shown on the highscores

	// characters page
	'characters' => array( // what things to display on character view page (true/false in each option)
		'level' => true,
		'experience' => false,
		'magic_level' => false,
		'balance' => false,
		'marriage_info' => true, // only 0.3
		'creation_date' => true,
		'quests' => true,
		'skills' => true,
		'equipment' => true,
		'frags' => false
	),

	// news page
	'news_limit' => 5, // limit of news on latest news page
	'news_ticker_limit' => 5, // limit of news in tickers (mini news) (0 to disable)
	'news_date_format' => 'j.n.Y', // check php manual date() function for more info about this
	'news_author' => true,

	// gifts/shop system
	'gifts_system' => false,

	// forum
	'forum' => 'site', // link to the server forum, set to "site" if you want to use build in forum system, otherwise leave empty if you aren't going to use any forum
	'forum_level_required' => 0, // level required to post, 0 to disable
	'forum_post_interval' => 30, // in seconds
	'forum_posts_per_page' => 20,
	'forum_threads_per_page' => 20,

	// last kills
	'last_kills_limit' => 50, // max. number of deaths shown on the last kills page

	// status, took automatically from config file if empty
	'status_ip' => '',
	'status_port' => '',

	// other
	'email_lai_sec_interval' => 60, // time in seconds between e-mails to one account from lost account interface, block spam
	'google_analytics_id' => '', // e.g.: UA-XXXXXXX-X
	'experiencetable_columns' => 5, // how many columns to display in experience table page. * 100, 5 = 500 (will show up to 500 level)

	'monsters' => array(),
	'npc' => array()
);

// download link to client.
$config['client_download'] = 'http://clients.halfaway.net/windows.php?tibia='. $config['client'] .'';
$config['client_download_linux'] = 'http://clients.halfaway.net/linux.php?tibia='. $config['client'] .'';

?>
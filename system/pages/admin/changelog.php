<?php
/**
 * CHANGELOG viewer
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.4.2
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'MyAAC Changelog';

if(!file_exists(BASE . 'CHANGELOG')) {
	echo 'File CHANGELOG doesn\'t exist.';
	return;
}

$changelog = file_get_contents(BASE . 'CHANGELOG');
$changelog = nl2br(htmlspecialchars($changelog));

// replace URLs with <a href...> elements
$changelog = preg_replace('/\s(\w+:\/\/)(\S+)/', ' <a href="\\1\\2" target="_blank">\\1\\2</a>', $changelog);

echo '<div>' . $changelog . '</div>';
?>

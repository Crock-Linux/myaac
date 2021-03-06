<?php
/**
 * Plugins
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.4.2
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Plugin manager';

require(SYSTEM . 'hooks.php');

echo $twig->render('admin.plugins.form.html.twig');

$message = '';
if(isset($_FILES["plugin"]["name"]))
{
	$file = $_FILES["plugin"];
	$filename = $file["name"];
	$tmp_name = $file["tmp_name"];
	$type = $file["type"];

	$name = explode(".", $filename);
	$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream', 'application/zip-compressed');

	if(isset($file['error'])) {
		$error = 'Error uploading file';
		switch( $file['error'] ) {
			case UPLOAD_ERR_OK:
				$error = false;
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$error .= ' - file too large (limit of '.ini_get('upload_max_filesize').' bytes).';
				break;
			case UPLOAD_ERR_PARTIAL:
				$error .= ' - file upload was not completed.';
				break;
			case UPLOAD_ERR_NO_FILE:
				$error .= ' - zero-length file uploaded.';
				break;
			default:
				$error .= ' - internal error #' . $file['error'];
				break;
		}
	}

	if(isset($error) && $error != false) {
		error($error);
	}
	else {
		if(is_uploaded_file($file['tmp_name']) ) {
			if(strtolower($name[1]) == 'zip') // check if it is zipped/compressed file
			{
				$targetdir = BASE;
				$targetzip = BASE . 'plugins/' . $name[0] . '.zip';

				if(move_uploaded_file($tmp_name, $targetzip)) { // move uploaded file
					$zip = new ZipArchive();
					$x = $zip->open($targetzip);  // open the zip file to extract
					if ($x === true) {
						for ($i = 0; $i < $zip->numFiles; $i++) {
							$tmp = $zip->getNameIndex($i);
							if(pathinfo($tmp, PATHINFO_DIRNAME) == 'plugins' && pathinfo($tmp, PATHINFO_EXTENSION) == 'json')
								$json_file = $tmp;
						}
						
						if(!isset($json_file)) {
							error('Cannot find plugin info .json file. Installation is discontinued.');
						}
						else if($zip->extractTo($targetdir)) { // place in the directory with same name
							$file_name = BASE . $json_file;
							if(!file_exists($file_name))
								warning("Cannot load " . $file_name . ". File doesn't exist.");
							else {
								$string = file_get_contents($file_name);
								$plugin = json_decode($string, true);
								if ($plugin == null) {
									warning('Cannot load ' . $file_name . '. File might be not valid json code.');
								}
								
								if(isset($plugin['install'])) {
									if(file_exists(BASE . $plugin['install']))
										require(BASE . $plugin['install']);
									else
										warning('Cannot load install script. Your plugin might be not working correctly.');
								}
								
								if(isset($plugin['hooks'])) {
									foreach($plugin['hooks'] as $_name => $info) {
										if(isset($hook_types[$info['type']])) {
											$query = $db->query('SELECT `id` FROM `' . TABLE_PREFIX . 'hooks` WHERE `name` = ' . $db->quote($_name) . ';');
											if($query->rowCount() == 1) { // found something
												$query = $query->fetch();
												$db->query('UPDATE `' . TABLE_PREFIX . 'hooks` SET `type` = ' . $hook_types[$info['type']] . ', `file` = ' . $db->quote($info['file']) . ' WHERE `id` = ' . (int)$query['id'] . ';');
											}
											else {
												$db->query('INSERT INTO `' . TABLE_PREFIX . 'hooks` (`id`, `name`, `type`, `file`) VALUES (NULL, ' . $db->quote($_name) . ', ' . $hook_types[$info['type']] . ', ' . $db->quote($info['file']) . ');');
											}
										}
										else
											warning('Unknown event type: ' . $info['type']);
									}
								}
								success('<strong>' . $plugin['name'] . '</strong> plugin has been successfully installed.');
							}
						}
						else {
							error('There was a problem with extracting zip archive.');
						}
						
						$zip->close();
						unlink($targetzip); // delete the Zipped file
					}
					else {
						error('There was a problem with opening zip archive.');
					}
				}
				else
					error('There was a problem with the upload. Please try again.');
			}
			else {
				error('The file you are trying to upload is not a .zip file. Please try again.');
			}
		}
		else {
			error('Error uploading file - unknown error.');
		}
	}
}

echo $message;

$plugins = array();
$rows = array();

$path = PLUGINS;
foreach(scandir($path) as $file)
{
	$file_info = explode('.', $file);
	if($file == '.' || $file == '..' || $file == 'disabled' || $file == 'example.json' || is_dir($path . $file) || !$file_info[1] || $file_info[1] != 'json')
		continue;
	
	$string = file_get_contents(BASE . 'plugins/' . $file_info[0] . '.json');
	$plugin_info = json_decode($string, true);
	$rows[] = array(
		'name' => $plugin_info['name'],
		'description' => $plugin_info['description'],
		'version' => $plugin_info['version'],
		'author' => $plugin_info['author'],
		'contact' => $plugin_info['contact'],
		'file' => $file,
	);
}

echo $twig->render('admin.plugins.html.twig', array(
	'plugins' => $rows
));
?>
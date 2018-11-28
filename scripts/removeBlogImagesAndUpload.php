<?php
///////////////////////////////////////////////////////////////
/// Bootstrap
///////////////////////////////////////////////////////////////
// error_reporting(E_ERROR);

$_SERVER['SERVER_NAME'] = 'baileysc.local.com';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
global $assign_to_config, $system_path, $debug, $CFG, $URI, $IN, $OUT, $LANG, $SEC, $loader;
if (!isset($system_path)) {
  $system_path = "system";
}
$assign_to_config['enable_query_strings'] = TRUE;
$assign_to_config['subclass_prefix']      = 'EE_';

if (realpath($system_path) !== FALSE) {
  $system_path = realpath($system_path) . '/';
}
// ensure there's a trailing slash
$system_path = rtrim($system_path, '/').'/';

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path.'codeigniter/system/'));
define('APPPATH', $system_path.'expressionengine/');
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", $system_path), '/'), '/'), '/'));
define('CI_VERSION', '2.0');
define('DEBUG', isset($debug) ? $debug : 0);

require BASEPATH.'core/Common.php';
require APPPATH.'config/constants.php';

$CFG    =& load_class('Config', 'core');
$CFG->_assign_to_config($assign_to_config);

$UNI    =& load_class('Utf8', 'core');
$URI    =& load_class('URI', 'core');
$SEC    =& load_class('Security', 'core');
$IN     =& load_class('Input', 'core');	
$OUT    =& load_class('Output', 'core');
$LANG   =& load_class('Lang', 'core');
$loader = load_class('Loader', 'core');

require BASEPATH.'core/Controller.php';

function &get_instance() {
  return CI_Controller::get_instance();
}
function ee() {
  return get_instance();
}

new CI_Controller();
ee()->load->library('core');
if (method_exists(ee()->core, 'bootstrap')) {
  ee()->core->bootstrap();
}

ee()->core->native_plugins = array('magpie', 'markdown', 'rss_parser', 'xml_encode');
ee()->core->native_modules = array('blacklist', 'channel', 'comment', 'commerce', 'email', 'emoticon', 'file', 'forum', 'ip_to_nation', 'jquery', 'mailinglist', 'member', 'metaweblog_api', 'moblog', 'pages', 'query', 'referrer', 'rss', 'rte', 'search', 'simple_commerce', 'stats', 'wiki');
ee()->load->library('remember');
ee()->load->library('localize');
ee()->load->library('session');
ee()->load->library('user_agent');
ee()->lang->loadfile('core');
ee()->load->helper('compat');
///////////////////////////////////////////////////////////////

function getImageInfo($path, $imageName, $new = true) {
	$imageInfo         = getimagesize($path);
	$imageInfo['name'] = $imageName;
	$imageInfo['size'] = filesize($path);
	$imageInfo['new']  = $new;
	return $imageInfo;
}

function saveImage($url) {
	// Extract filename from url
	$explode   = explode('/', $url);
	$imageName = $explode[count($explode) - 1];

	$path = 'images/uploads/images/' . $imageName;
	// Check to see if we have already save this file
	if (file_exists($path)) {
		echo '-- Image Already Exists - ' . $imageName . PHP_EOL;
		return getImageInfo($path, $imageName, false);
	}

	// Save file locally
	if (copy($url, $path)) {
		echo '-- New Image Saved - ' . $imageName . PHP_EOL;
		return getImageInfo($path, $imageName);
	} else {
		echo '-- Image Save Failure - ' . $imageName . PHP_EOL;
		return false;
	}

	echo '-- New Image Saved' . PHP_EOL;

	return getImageInfo($path, $imageName);
}

function insertImage($entryId, $imageInfo) {
	if ($imageInfo['new']) {
		// Insert into assets files
		$row = [
			'folder_id'       => 1,
			'source_type'     => 'ee',
			'filedir_id'      => 1,
			'file_name'       => $imageInfo['name'],
			'date'            => time(),
			'date_modified'   => time(),
			'kind'            => 'image',
			'width'           => $imageInfo[0],
			'height'          => $imageInfo[1],
			'size'            => $imageInfo['size'],
			'search_keywords' => $imageInfo['name']
		];
		if (!ee()->db->insert('exp_assets_files', $row)) {
			$fileId = ee()->db
						->from('exp_assets_files')
						->where('file_name', $imageInfo['name'])
						->get()->result()[0];
		} else {
			$fileId = ee()->db->insert_id();
		}

		echo '-- New Image Inserted into exp_assets_files ID - ' . $fileId . PHP_EOL;
	} else {
		$fileId = ee()->db
					->from('exp_assets_files')
					->where('file_name', $imageInfo['name'])
					->get()->result()[0]->file_id;
		echo '-- Using old Image from exp_assets_files ID - ' . $fileId . PHP_EOL;
	}

	// Now we insert into assets selections
	$row = [
		'file_id'    => $fileId,
		'entry_id'   => $entryId,
		'field_id'   => 2,
		'sort_order' => 0,
		'is_draft'   => 0
	];

	if (ee()->db->insert('exp_assets_selections', $row)) {
		echo '-- Image association made in exp_assets_selections' . PHP_EOL;
		return true;
	} else {
		echo '-- Image association failure' . PHP_EOL;
		return false;
	}
}

function updateEntry($entryId, $newContent, $imageInfo) {
	$update = [
		'field_id_2' => $imageInfo['name'],
		'field_id_3' => $newContent
	];

	if (ee()->db->where('entry_id', $entryId)->update('exp_channel_data', $update)) {
		echo '-- Entry Updated' . PHP_EOL;
		return true;
	} else {
		echo '-- Entry Update Failure' . PHP_EOL;
		return false;
	}
}



//////////////////////////////////////////
/// Begin the script
//////////////////////////////////////////
$query = ee()->db
			->from('exp_channel_data')
			->where('field_id_3 IS NOT NULL')
			->where('field_id_3 != ""')
			->get();

$errors = [];
foreach ($query->result() as $row) {
	// Grab the source
	if (!preg_match("/\< *[img].*src=\"([^\"]*)\"[^\>]*\>/i", $row->field_id_3, $matches))
		continue;
	
	echo 'Fixing entry - ' . $row->entry_id . PHP_EOL;	
	$src = $matches[1];
	// Now remove image tag
	// $newContent = preg_replace("/\< *[img].*src=\"([^\"]*)\"[^\>]*\>/i", "", $row->field_id_3, 1);

	// echo $newContent;
	// We need to save the image
	// var_dump(saveImage($src));
	if (!$imageInfo = saveImage($src)) {
		$errors[] = 'Save Image ERROR - ' . $row->entry_id;
		continue;
	}

	// Insert image into database
	if (!insertImage($row->entry_id, $imageInfo)) {
		$errors[] = 'Insert Image ERROR - ' . $row->entry_id;
		continue;
	}

	// // Update post with new content
	// if (!updateEntry($row->entry_id, $newContent, $imageInfo)) {
	// 	$errors[] = 'Save Content ERROR - ' . $row->entry_id;
	// 	continue;
	// }
}

if (count($errors))
	var_dump($errors);
<?php
///////////////////////////////////////////////////////////////
/// Bootstrap
///////////////////////////////////////////////////////////////
error_reporting(E_ERROR);
// Set up our server info
$_SERVER['SERVER_NAME'] = 'baileysc.local.com';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
global $assign_to_config, $system_path, $debug, $CFG, $URI, $IN, $OUT, $LANG, $SEC, $loader;
if (!isset($system_path)) {$system_path = "system";}
$assign_to_config['enable_query_strings'] = TRUE;
$assign_to_config['subclass_prefix']      = 'EE_';
// Make sure the system path is real
if (realpath($system_path) !== FALSE) {$system_path = realpath($system_path) . '/';}
// Ensure there's a trailing slash
$system_path = rtrim($system_path, '/').'/';
// Define necessary constants
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path.'codeigniter/system/'));
define('APPPATH', $system_path.'expressionengine/');
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(str_replace("\\", "/", $system_path), '/'), '/'), '/'));
define('CI_VERSION', '2.0');
define('DEBUG', isset($debug) ? $debug : 0);
// Include some necessities
require BASEPATH . 'core/Common.php';
require APPPATH  . 'config/constants.php';
// Load some classes
$CFG    =& load_class('Config', 'core');
$CFG->_assign_to_config($assign_to_config);
$UNI    =& load_class('Utf8', 'core');
$URI    =& load_class('URI', 'core');
$SEC    =& load_class('Security', 'core');
$IN     =& load_class('Input', 'core');	
$OUT    =& load_class('Output', 'core');
$LANG   =& load_class('Lang', 'core');
$loader = load_class('Loader', 'core');
// Get ready to instantiate EE
require BASEPATH.'core/Controller.php';
function &get_instance() {return CI_Controller::get_instance();}
function ee() {return get_instance();}
// Instantiate EE
new CI_Controller();
ee()->load->library('core');
if (method_exists(ee()->core, 'bootstrap')) {ee()->core->bootstrap();}
// Load some libraries and plugins and modules
ee()->core->native_plugins = array('magpie', 'markdown', 'rss_parser', 'xml_encode');
ee()->core->native_modules = array('blacklist', 'channel', 'comment', 'commerce', 'email', 'emoticon', 'file', 'forum', 'ip_to_nation', 'jquery', 'mailinglist', 'member', 'metaweblog_api', 'moblog', 'pages', 'query', 'referrer', 'rss', 'rte', 'search', 'simple_commerce', 'stats', 'wiki');
ee()->load->library('remember');
ee()->load->library('localize');
ee()->load->library('session');
ee()->load->library('user_agent');
ee()->lang->loadfile('core');
ee()->load->helper('compat');
///////////////////////////////////////////////////////////////

/**
 * Update the entry in the db
 * @param  string $entryId    Entry Id
 * @param  string $newContent The new content
 * @return bool             Whether we were successful or not
 */
function updateEntry($entryId, $newContent) {
	$update = [
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
	if (!preg_match("/\[caption[^\]]*\](.*)\[\/caption\]/i", $row->field_id_3, $matches))
		continue;
	
	echo 'Fixing entry - ' . $row->entry_id . PHP_EOL;	
	$src = $matches[1];
	// Now remove image tag
	$newContent = preg_replace("/\[caption[^\]]*\](.*)\[\/caption\]/i", "$1", $row->field_id_3);

	// Update post with new content
	if (!updateEntry($row->entry_id, $newContent)) {
		$errors[] = 'Save Content ERROR - ' . $row->entry_id;
		continue;
	}
}

if (count($errors))
	var_dump($errors);
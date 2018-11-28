<?php

require_once('app/Mage.php');
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('display_errors', 1);
umask(0);
set_time_limit(0);
ini_set('memory_limit','1024M');

$dryRun = false;
// $dryRun = true;

$mediaDir = Mage::getBaseDir('media') . '/catalog/product';

echo 'Querying database for images' . PHP_EOL;

$query = 'SELECT value FROM catalog_product_entity_media_gallery';
$data  = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);

$dbData = [];
foreach ($data as $item) {
  $dbData[$item['value']] = $item['value'];
}

echo 'Images found in database:' . count($dbData) . PHP_EOL;
echo 'Search images in media directory' . PHP_EOL;

$images = findFiles($mediaDir, ['jpg', 'png', 'jpeg', 'gif']);
$counts = array_map(function($extensionImages) {
	return count($extensionImages);
}, $images);

echo 'Images found under directory ' . $mediaDir . ' : ' . array_sum($counts) . PHP_EOL;

echo 'Start removing images' .PHP_EOL;
$removedCount = 0;
$skippedCount = 0;
foreach ($images['jpg'] as $image) {
	if (strpos($image, 'cache') !== false)
    	continue;

	$imageCleanup = str_replace($mediaDir, '' , $image);
	if (isset($dbData[$imageCleanup])) {
		echo 'Skip image is in database : ' . $imageCleanup . PHP_EOL;
		$skippedCount++;
		continue;
	} else {
		echo 'Remove image : ' . $imageCleanup . PHP_EOL;
		if (!$dryRun)
			unlink($image);
		$removedCount++;
	}
}

echo 'Done, removed '. $removedCount . ' images and skipped ' . $skippedCount . ' images' . PHP_EOL;

function findFiles($directory, $extensions = []) {
	function glob_recursive($directory, &$directories = []) {
		foreach (glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
			$directories[] = $folder;
			glob_recursive($folder . '/*', $directories);
		}
	}
	glob_recursive($directory, $directories);
	$files = [];
	foreach ($directories as $directory) {
		foreach ($extensions as $extension) {
			foreach (glob($directory . '/*.' . $extension) as $file) {
				$files[$extension][] = $file;
			}
		}
	}
	return $files;
}

?>
<?php
include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// So we don't time out hopefully
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

// Essentials for later
$entityTypeId    = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
$mediaCatalogDir = Mage::getBaseDir('media') . '/catalog/product';

$productCount = 0;
$errors       = [];
$batchSize    = 500;
$start        = microtime(true);
$output  	  = true;

// Product count so we can batch without exhausting memory
$productsCount = Mage::getModel('catalog/product')->getCollection()
					->addAttributeToFilter('type_id', ['eq' => 'configurable'])
					->getSize();

// Do in batches so we don't exhaust memory
for ($batch = 1, $batchCount = ceil($productsCount / $batchSize); $batch <= $batchCount; $batch++) {
	$products = Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToFilter('type_id', ['eq' => 'configurable'])
	    		->setPageSize($batchSize)
	    		->setCurPage($batch);
	// Loop through the products to delete the images
    foreach ($products as $product) {
    	// Load model so we have media gallery
    	$product->load('media_gallery');

    	$productCount++;
    	echo $output ? $productCount . ' - ' . $product->getName() . PHP_EOL : '';

    	// Get the media gallery for this product
    	if (!$mediaGallery = $product->getMediaGallery())
    		continue;

    	// Ignore items that have no images or only one image
    	if (!isset($mediaGallery['images']))
    		continue;

    	// Collect the bad paths
		$deletePaths   = [];
		$_mediaGallery = $mediaGallery;

    	foreach ($mediaGallery['images'] as $originalIndex => $image) {
			// If the file doesn't exist, we need to delete
    		if (!file_exists($mediaCatalogDir . $image['file']))
				$deletePaths[$originalIndex] = $image['file'];
		}

		// Get Media Gallery API
		$mediaGalleryAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode($entityTypeId, 'media_gallery');

		// Delete them out of Magento and off the disk
		foreach ($deletePaths as $imagePath) {
			try {
			    $mediaGalleryAttribute->getBackend()->removeImage($product, $imagePath);
			    if (!unlink($mediaCatalogDir . $imagePath))
			    	throw new Exception('---- Unable to unlink file at ' . $mediaCatalogDir . $imagePath);
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}

		try {
			$product->save();
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}
		echo $output ? '---- ' . count($deletePaths) . ' Images Deleted' . PHP_EOL : '';
    }
    $difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' . PHP_EOL : '';
}
$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' . PHP_EOL : '';
echo $output ? 'Errors: ' . PHP_EOL . var_export($errors, true) : '';
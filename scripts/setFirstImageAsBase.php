<?php

if (!in_array(PHP_SAPI, ['cli', 'cli-server']))
	exit('Not Authorized');

require ('app/Mage.php');
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
	// Loop through the products to find the images
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

		foreach ($mediaGallery['images'] as $image){
			//set the first image as the base image
			Mage::getSingleton('catalog/product_action')
				->updateAttributes(
					[$product->getId()], 
					[
						'image'       => $image['file'],
						'thumbnail'   => $image['file'],
						'small_image' => $image['file']
					], 
					0
				);
			break;
		}
		
	    $product->save();
    }
    $difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' . PHP_EOL : '';
}
$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' . PHP_EOL : '';
echo $output ? 'Errors: ' . PHP_EOL . var_export($errors, true) : '';
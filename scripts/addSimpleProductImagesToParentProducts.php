<?php
require 'app/Mage.php';
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

function fixImages($output) {
	$start = microtime(true);

	$totalProductCount = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('type_id', ['eq' => 'configurable'])
						->getSize();

	$productCount = 1;
	$batchSize    = 100;

	for ($batch = 1, $totalBatches = ceil($totalProductCount / $batchSize); $batch <= $totalBatches; $batch++) {
		$products = Mage::getModel("catalog/product")
					->getCollection()
					->addAttributeToFilter('type_id', ['eq' => 'configurable'])
		    		->addAttributeToSelect('*')
		    		->setPageSize($batchSize)
		    		->setCurPage($batch);
	   	
	    foreach ($products as $product) {
	    	$product->load('media_gallery');
	    	////////////////////////////////
	    	// $product = Mage::getModel('catalog/product')->load(9556);
	    	////////////////////////////////

			echo $output ? $productCount . ' - ' . 'SKU - ' . $product->getSku() . PHP_EOL : '';

			// Now we need to find the child products
			$childProducts = Mage::getModel('catalog/product_type_configurable')
								->getUsedProducts(null, $product);

			foreach($childProducts as $child) {
				echo $output ? ' ---- Child SKU - ' . $child->getSku() . PHP_EOL : '';

				$child->load('media_gallery');
				$mediaGallery = $child->getMediaGallery();
				foreach ($mediaGallery['images'] as $image) {
					echo $output ? ' ------- Image - ' . $image['file'] . PHP_EOL : '';

					$filePath = Mage::getBaseDir('media') . '/catalog/product' .  $image['file'];
					if (!file_exists($filePath)) {
						echo $output ? ' -------! Image doesn\'t exist ' . PHP_EOL : '';
					} else {
						echo $output ? ' ---------- Added to parent' . PHP_EOL : '';
						$product->addImageToMediaGallery($filePath, null, false, false)->save();
					}
					
				}
			}

		    $productCount++;

		    ////////////////////////////////
		    // break 2;
		    ////////////////////////////////
		}
		$difference = microtime(true) - $start;
		echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
	}
	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';
}


fixImages(true);
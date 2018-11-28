<?php
require('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

function go($output) {
	$start = microtime(true);

	$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

	$productCount = 1;
	$batchSize    = 100;

	for ($batch = 1, $totalBatches = ceil($totalProductCount / $batchSize); $batch <= $totalBatches; $batch++) {
		$products = Mage::getModel("catalog/product")
					->getCollection()
		    		->setPageSize($batchSize)
		    		->setCurPage($batch);
	   	
	    foreach ($products as $product) {
	    	////////////////////////////////
	    	// $product = Mage::getModel('catalog/product')->load(74844);
	    	////////////////////////////////

			echo $output ? $productCount . ' - ' . 'SKU - ' . $product->getSku() . PHP_EOL : '';

			// Now we need to get the categories
			$categoryIds = $product->getCategoryIds();
			foreach ($categoryIds as $categoryId) {
				$categoryPath = Mage::getModel('catalog/category')->load($categoryId)->getPath();
				// Explode the path and add to categoryIds array if it's not the root category
				foreach (explode('/', $categoryPath) as $_categoryId)
					if ($_categoryId !== '1' && !in_array($_categoryId, $categoryIds))
						$categoryIds[] = $_categoryId;
			}
			
			// Save the product with the new ids
			$product->setCategoryIds($categoryIds)->save();

		    ////////////////////////////////
		    // break 2;
		    ////////////////////////////////
		    
		    $productCount++;
		}
		$difference = microtime(true) - $start;
		echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' . PHP_EOL : '';
	}
	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' . PHP_EOL : '';
}


go(true);
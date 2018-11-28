<?php
/**
 * This script will replace all product names with their short description and all product 
 * short descriptions with their name.
 *
 * After using this script, it is recommended to truncate the url tables
 * and use the saveAllProductUrlKeys script to rebuild urls
 */
$output = true;

require 'app/Mage.php';
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(0);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

$start = microtime(true);

$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

$productCount = 1;
for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
	$products = Mage::getModel("catalog/product")
				->getCollection()
	    		->addAttributeToSelect(['name', 'short_description'])
	    		->setPageSize(1000)
	    		->setCurPage($batch);
   	
    foreach ($products as $product) {
		echo $output ? 
				$productCount 
				. ' - SKU - ' . $product->getSku() . PHP_EOL 
				. 'Name: ' . $product->getName() . PHP_EOL 
				. 'SD: ' . $product->getShortDescription() . PHP_EOL . PHP_EOL 
			: '';

		// Much faster way of saving attributes
	    Mage::getSingleton('catalog/product_action')->updateAttributes(
	         [$product->getId()], 					//products to update
	         [
				'name'              => $product->getShortDescription(),
				'short_description' => $product->getName()
	         ],									 	//attributes to update
	         0 										//store to update. 0 means global values
	    );
	    $productCount++;
	}
	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
}
$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';
<?php
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
				->addAttributeToSelect(['name'])
				->setPageSize(1000)
				->setCurPage($batch);
	
	foreach ($products as $product) {
		echo $output ? 
				$productCount . ' - SKU - ' . $product->getSku() . PHP_EOL 
				. 'Name: ' . $product->getName() . PHP_EOL
			: '';
		
		try {
			Mage::getSingleton('catalog/product_action')->updateAttributes(
				[$product->getId()], 	  // Products to update
				[
					'description'       => '<!--empty-->',
					'short_description' => '<!--empty-->'
				],     // Attributes to update
				0 						  // Store to update. 0 means global values
			);
		} catch (Exception $e) {
			echo $e->getMessage() . PHP_EOL;
		}

		$productCount++;
	}

	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
}

$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';
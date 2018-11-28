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

$productCount   = 1;
$duplicateCount = 0;
for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
	$products = Mage::getModel("catalog/product")
				->getCollection()
	    		->setPageSize(1000)
	    		->setCurPage($batch);
   	
    foreach ($products as $product) {
    	$product->load('options');

    	if (!(bool)$product->getHasOptions())
    		continue;

		echo $output ? 
				$productCount 
				. ' - SKU - ' . $product->getSku() . PHP_EOL 
				. 'Name: ' . $product->getName() . PHP_EOL 
			: '';

		foreach ($product->getOptions() as $option) {
			$firstFound = false;
			foreach ($option->getValues() as $value) {
				$title = strtolower(trim($value->getTitle()));
				if ($title === 'reconditioned' && !$firstFound) {
					$firstFound = true;
				} else if ($title === 'reconditioned' && $firstFound && !$value->getPrice()) {
					echo '---- Duplicate value found, deleting' . PHP_EOL;
					$duplicateCount++;
					$value->delete();
				}
			}
		}
	    $productCount++;
	}
	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
}
$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';
echo $output ? '===========' . PHP_EOL . $duplicateCount . ' Total Duplicated Fixed' . PHP_EOL . '==========' : '';
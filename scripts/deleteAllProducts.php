<?php

require 'app/Mage.php';
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(0);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

$productCount = 1;
for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
	$products = Mage::getModel("catalog/product")
				->getCollection()
				->setPageSize(1000)
				->setCurPage($batch);
	
	foreach ($products as $product)
		try {
			$product->delete();
		} catch (Exception $e) {
			echo $e->getMessage() . PHP_EOL;
		}
}
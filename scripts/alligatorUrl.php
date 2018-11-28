<?php
/**
 * This script will slugify all product's names and save them as URL keys
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

function slugify($text) {
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	$text = preg_replace('~[^-\w]+~', '', $text);
	$text = trim($text, '-');
	$text = preg_replace('~-+~', '-', $text);
	$text = strtolower($text);

	if (empty($text))
		return 'n-a';

	return $text;
}

function saveUrl($product, $slug) {
	// Much faster way of saving attributes
	try {
		Mage::getSingleton('catalog/product_action')->updateAttributes(
			[$product->getId()], 	  // Products to update
			['url_key' => $slug],     // Attributes to update
			0 						  // Store to update. 0 means global values
		);
		return true;
	} catch (Exception $e) {
		if (strstr($e->getMessage(), 'Duplicate entry'))
			return false;
		else
			echo $e->getMessage();exit;
	}
}

$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

$productCount = 1;
for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
	$products = Mage::getModel("catalog/product")
				->getCollection()
				->addAttributeToSelect(['name'])
				->setPageSize(1000)
				->setCurPage($batch);
	
	foreach ($products as $product) {
		$slug = slugify($product->getName());

		echo $output ? 
				$productCount . ' - SKU - ' . $product->getSku() . PHP_EOL 
				. 'Name: ' . $product->getName() . PHP_EOL 
				. 'Url: ' . $slug . PHP_EOL
			: '';

		for ($i = 0; $i <= 99; $i++) {
			if ($i === 0)
				$_slug = $slug;
			else
				$_slug = $slug . '-' . $i;
			
			if (saveUrl($product, $_slug))
				break;
		}

		$productCount++;
	}
	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
}
$difference = microtime(true) - $start;
echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';

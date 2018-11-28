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

echo 'Getting products and fixing stock items' . PHP_EOL;

$productCollection = Mage::getModel('catalog/product')->getCollection();

foreach ($productCollection as $product) {
	$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());

	if ($stockItem->getId())
		continue;

	$stockItem = Mage::getModel('cataloginventory/stock_item');

	$stockItem
		->setData('manage_stock', 1)
		->setData('is_in_stock', 1)
		->setData('use_config_manage_stock', 0)
		->setData('stock_id', 1)
		->setData('product_id', $product->getId())
		->setData('qty', 0)
		->save();

	echo 'New stock item saved for product ' . $product->getId() . PHP_EOL;
}

echo 'Done';
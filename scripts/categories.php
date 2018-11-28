<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

$importer = Mage::helper('unleaded_pims/import');

function fixCatMenu() {
	$map = [
		'admin'   => '0',
		'default' => '3',
		'avs'     => '2',
		'lund'    => '1',
	];

	$resource = Mage::getResourceModel('catalog/category');

	foreach ($map as $code => $id) {
		echo '===================================' . PHP_EOL;
		echo 'Store ' . $code . PHP_EOL;
		echo '===================================' . PHP_EOL;
		$categoryCollection = Mage::getModel('catalog/category')
								->getCollection()
								->setStoreId($id)
								->addAttributeToSelect('include_in_menu')
								->addAttributeToSelect('name');
		foreach ($categoryCollection as $category) {
			echo $category->getIncludeInMenu() . ' ' . $category->getName() . PHP_EOL;
			if ($category->getLevel() == 5) {
				$category->setIncludeInMenu(0);
				$resource->saveAttribute($category, 'include_in_menu');
			}
		}
	}
}
fixCatMenu();

function deleteLegacyCats() {
	$map = [
		'avs'  => 'AVS',
		'lund' => 'Lund'
	];
	$categoryCollection = Mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect('name');
	foreach ($categoryCollection as $category) {
		if (in_array($category->getId(), [1,2]))
			continue;
		if (in_array($category->getName(), array_values($map)))
			continue;
		if ($category->getName() === 'All Products')
			continue;

		$category->delete();
	}
}

function reassignCats($output) {
	$categoryCache = [];

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
			
			if (isset($categoryCache[$product->getName()])) {
				echo $output ? 'Using categories from cache' . PHP_EOL : '';

				try {
					$product->setCategoryIds($categoryCache[$product->getName()])->save();
				} catch (Exception $e) {
					echo $e->getMessage() . PHP_EOL;
				}
			} else {
				$category = Mage::getModel('catalog/category')
								->getCollection()
								->addAttributeToFilter('name', $product->getName())
								->getFirstItem();

				$categoryPath = $category->getPath();

				$categories = explode('/', $categoryPath);

				$categoryCache[$product->getName()] = $categories;

				try {
					$product->setCategoryIds($categories)->save();
				} catch (Exception $e) {
					echo $e->getMessage() . PHP_EOL;
				}
			}

			$productCount++;
		}

		$difference = microtime(true) - $start;
		echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Elapsed' . PHP_EOL . '==========' : '';
	}

	$difference = microtime(true) - $start;
	echo $output ? '===========' . PHP_EOL . ($difference / 60) . ' Minutes Total' . PHP_EOL . '==========' : '';
}
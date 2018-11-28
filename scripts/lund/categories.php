<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(0);

umask(0);

function addBrandAttribute() {
	$installer = new Mage_Sales_Model_Mysql4_Setup;
	$attribute  = array(
	    'group'                     => 'General',
	    'input'                     => 'select',
	    'type'                      => 'int',
	    'label'                     => 'Mobile (api)',
	    'source'                    => 'eav/entity_attribute_source_boolean',
	    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	    'visible'                   => 1,
	    'required'                  => 0,
	    'visible_on_front'          => 0,
	    'is_html_allowed_on_front'  => 0,
	    'is_configurable'           => 0,
	    'searchable'                => 0,
	    'filterable'                => 0,
	    'comparable'                => 0,
	    'unique'                    => false,
	    'user_defined'              => false,
	    'default'           => '0',
	    'is_user_defined'           => false,
	    'used_in_product_listing'   => true
	);
	$installer->addAttribute('catalog_category', 'mobile_api', $attribute);
	$installer->endSetup();
}
















































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


function deleteLegacyCats() {
	$categoryCollection = Mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect('name')
							->addFieldToFilter('level', 3);
	foreach ($categoryCollection as $category) {
		echo $category->getName() . PHP_EOL;
		$category->delete();
	}
}
deleteLegacyCats();
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

function rewriteCategories() {
	// First delete old ones
	foreach (Mage::getModel('enterprise_urlrewrite/redirect')->getCollection() as $rewrite) {
		// var_dump($rewrite->getOptions());
		$rewrite->delete();
	}
	// return;
	
	foreach (Mage::app()->getStores() as $store) {
		// Get category
		$category = Mage::getModel('catalog/category')
					->setStoreId($store->getId())
					->load($store->getRootCategoryId());
		// Build path LIKE
		$like  = $category->getPath() . '/%';

		$categoryCollection = Mage::getModel('catalog/category')
								->getCollection()
								->addAttributeToSelect(['name', 'url_key', 'level'])
								->addAttributeToFilter('path', ['like' => $like]);

		// First sort them based on level
		$level2 = [];
		$level3 = [];
		foreach ($categoryCollection as $category) {
			$category = Mage::getModel('catalog/category')
						->setStoreId($store->getId())
						->load($category->getId());
			if ($category->getLevel() == '2')
				$level2[$category->getId()] = $category;
			if ($category->getLevel() == '3')
				$level3[$category->getId()] = $category;
		}

		// Do level 4 first
		foreach ($level2 as $categoryId => $category) {
			$fromUrl = $category->getUrlKey();
			$toUrl   = 'catalog/category/view/id/' . $categoryId;
			// echo $fromUrl . ' => ' . $toUrl . PHP_EOL;
			// continue;
			$rewrite = Mage::getModel('enterprise_urlrewrite/redirect')
						->setStoreId($store->getId())
				        ->setOptions(NULL)
				        ->setIdentifier($fromUrl)
				        ->setTargetPath($toUrl)
				        ->setEntityType(Mage_Core_Model_Url_Rewrite::TYPE_CATEGORY)
				        ->save();
		}

		foreach ($level3 as $categoryId => $category) {
			// First we need to find parent
			$path = explode('/', $category->getPath());
			$parent = $level2[$path[2]];

			$fromUrl = $parent->getUrlKey() . '/' . $category->getUrlKey();
			$toUrl   = 'catalog/category/view/id/' . $categoryId;
			// echo $fromUrl . ' => ' . $toUrl . PHP_EOL;
			// continue;
			$rewrite = Mage::getModel('enterprise_urlrewrite/redirect')
						->setStoreId($store->getId())
				        ->setOptions(NULL)
				        ->setIdentifier($fromUrl)
				        ->setTargetPath($toUrl)
				        ->setEntityType(Mage_Core_Model_Url_Rewrite::TYPE_CATEGORY)
				        ->save();
		}
	}
}
function fixProductCategories() {
	// Brands
	$childCategories = [];
	foreach (['AVS', 'Lund'] as $storeCategory) {
		// Get category
		$storeCategory = Mage::getModel('catalog/category')->loadByAttribute('name', $storeCategory);
		// Get children and map their ids
		$childrenCategoryCollection = $storeCategory->getChildrenCategories();

		foreach ($childrenCategoryCollection as $childCategory) {
			// We want to go one level deeper for product lines
			$productLineCollection = $childCategory->getChildrenCategories();
			foreach ($productLineCollection as $productLine) {
				$childCategories[$productLine->getName()] = $productLine->getPath();
			}
		}
	}

	// Also do MMY
	$MMYCategory = Mage::getModel('catalog/category')->loadByAttribute('name', 'MMY');
	// Get children and map their ids
	$makeCollection = $MMYCategory->getChildrenCategories();

	foreach ($makeCollection as $makeCategory) {
		if (!isset($childCategories[$makeCategory->getName()]))
			$childCategories[$makeCategory->getName()] = [];

		$modelCollection = $makeCategory->getChildrenCategories();
		foreach ($modelCollection as $modelCategory) {
			if (!isset($childCategories[$makeCategory->getName()][$modelCategory->getName()]))
				$childCategories[$makeCategory->getName()][$modelCategory->getName()] = [];

			$yearCollection = $modelCategory->getChildrenCategories();
			foreach ($yearCollection as $yearCategory) {
				if (!isset($childCategories[$makeCategory->getName()][$modelCategory->getName()][$yearCategory->getName()]))
					$childCategories[$makeCategory->getName()][$modelCategory->getName()][$yearCategory->getName()] = $yearCategory->getPath();
			}
		}
	}

	// Vehicle category cache
	$vehicleCategories = [];

	// Now loop through products
	$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

	$productCount = 1;
	for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
		$products = Mage::getModel("catalog/product")
					->getCollection()
					->addAttributeToSelect('name')
					->addAttributeToSelect('compatible_vehicles')
					->setPageSize(1000)
					->setCurPage($batch);
		
		foreach ($products as $product) {
			if (!isset($childCategories[$product->getName()])) {
				echo 'Couldn\'t find ' . $product->getName() . PHP_EOL;
				continue;
			}
			$categories = explode('/', $childCategories[$product->getName()]);
			// Also add all YMM
			foreach (explode(',', $product->getCompatibleVehicles()) as $vehicleId) {
				if (!isset($vehicleCategories[$vehicleId])) {
					$vehicle = Mage::getModel('vehicle/ulymm')->load($vehicleId);
					if (isset($childCategories[$vehicle->getMake()])) {
						if (isset($childCategories[$vehicle->getMake()][$vehicle->getModel()])) {
							if (isset($childCategories[$vehicle->getMake()][$vehicle->getModel()][$vehicle->getYear()])) {
								$vehicleCategories[$vehicleId] = explode('/', $childCategories[$vehicle->getMake()][$vehicle->getModel()][$vehicle->getYear()]);
								$categories = array_merge($categories, $vehicleCategories[$vehicleId]);
							}
						}
					}
				} else {
					$categories = array_merge($categories, $vehicleCategories[$vehicleId]);
				}
			}
			$categories = array_unique($categories);
			sort($categories);

			$product->setCategoryIds($categories)->save();
			echo ++$productCount . ' - Categories saved for ' . $product->getId() . PHP_EOL;
		}
	}
	// var_dump($childCategories);
}

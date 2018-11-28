<?php

$installer = $this;
$installer->startSetup();

/// Categories setup 
/* Define categories array */
$categories = [
	[
		'name'     => 'Bikes',
		'children' => [
			['name' => 'Demo Bike Sale'],
			['name' => 'Road'],
			['name' => 'Mountain'],
			['name' => 'Cyclocross'],
			['name' => 'Triathlon/TT'],
			['name' => 'Fitness & Urban'],
			['name' => 'Kids'],
		],
	],
	[
		'name'     => 'Apparel',
		'children' => [
			[
				'name'     => 'Core',
				'children' => [
					['name' => 'Jerseys'],
					['name' => 'Jackets & Vests'],
					['name' => 'Base Layers'],
					['name' => 'Casual'],
					['name' => 'Shorts'],
					['name' => 'Tights & Knickers'],
				]
			],
			[
				'name'     => 'Footwear',
				'children' => [
					['name' => 'Shoes'],
					['name' => 'Socks'],
					['name' => 'Shoe Parts & Footbeds'],
					['name' => 'Shoe & Toe Covers'],
				]
			],
			[
				'name'     => 'Headwear',
				'children' => [
					['name' => 'Helmets'],
					['name' => 'Hats & Headbands'],
					['name' => 'Sunglasses'],
				]
			],
			[
				'name'     => 'Accessories',
				'children' => [
					['name' => 'Gloves'],
					['name' => 'Arm, Knee & Leg Warmers'],
					['name' => 'Compression'],
					['name' => 'Body Armor'],
				]
			],
			[
				'name'     => 'Kids',
				'children' => [
					['name' => 'Apparel'],
					['name' => 'Helmets'],
				]
			],
		],
	],
	[
		'name'     => 'Parts',
		'children' => [
			[
				'name'     => 'Wheelgoods',
				'children' => [
					['name' => 'Wheels, Rims & Hubs'],
					['name' => 'Rims/Spokes'],
					['name' => 'Hubs/Skewers'],
					['name' => 'Tires, Tubes & Accessories'],
				],
			],
			[
				'name'     => 'Drivetrain',
				'children' => [
					['name' => 'Shifters'],
					['name' => 'Derailleurs'],
					['name' => 'Hubs/Skewers'],
					['name' => 'Tires, Tubes & Accessories'],
				],
			],
		],
	],
];
 
function slugify($string)
{
	$slug = str_replace([' ', '/', '\'', '"', ','], '', strtolower($string));
	for ($i = 0; $i < 10; $i++)
		$slug = str_replace('--', '-', $slug);
	return $slug;
}
/* Function to save categories */
function saveCategory($parentCategory, $data)
{
	$updateMode      = false;
	$categoriesCheck = Mage::getModel('catalog/category')
						->getCollection()
						->addAttributeToFilter('parent_id', $parentCategory->getId())
						->addAttributeToFilter('name', $data['name'])
						->getFirstItem();
 
	if ($categoryId = $categoriesCheck->getId()) {
		$updateMode = true;
		$category   = Mage::getModel('catalog/category')->load($categoryId);
	} else {
		$category = Mage::getModel('catalog/category')->load(null);
	}
 
	if (!$updateMode)
		$category->setPath($parentCategory->getPath());
	
	$category->setIsAnchor(1);
	$category->setName($data['name']);
	$category->setDisplayMode(array_key_exists('display_mode', $data) ? $data['display_mode'] : 1);	
	$category->setIsActive(array_key_exists('set_is_active', $data) ? $data['set_is_active'] : 1);
	$category->setIncludeInMenu(array_key_exists('set_include_in_menu', $data) ? $data['set_include_in_menu'] : 1);
	$category->setUrlKey(array_key_exists('slug', $data) ? $data['slug'] : slugify($data['name']));
	$category->setPageLayout('one_column');

	try {
		$category->save();
	} catch (Exception $e) {
		Mage::logException($e);
	}
	
 
	if (isset($data['children']))
		foreach ($data['children'] as $subCat)
			saveCategory($category, $subCat);
 
	unset($category);
}

/* Perform necessary Magento mode settings */
$currentStoreId    = Mage::app()->getStore()->getId();
$currentUpdateMode = Mage::app()->getUpdateMode();
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
/* Retrieve root category */
$store          = Mage::getModel('core/store')->load(Mage_Core_Model_App::DISTRO_STORE_ID);
$rootCategoryId = $store->getRootCategoryId();
$rootCategory   = Mage::getModel('catalog/category')->load($rootCategoryId);
 
/* Run category creation */
if (!empty($rootCategory))
	foreach ($categories as $cat)
		saveCategory($rootCategory, $cat);
 
/* Change back Magento settings  */
Mage::app()->setCurrentStore($currentStoreId);
Mage::app()->setUpdateMode($currentUpdateMode);

$installer->endSetup();

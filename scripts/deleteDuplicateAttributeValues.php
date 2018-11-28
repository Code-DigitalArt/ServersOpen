<?php

require_once('app/Mage.php');
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('display_errors', 1);
umask(0);
set_time_limit(0);
ini_set('memory_limit','5000M');

$setup     = new Mage_Eav_Model_Entity_Setup('core_setup');

$attributeCodes = ['material', 'color'];
foreach ($attributeCodes as $code) {
	$attribute     = Mage::getModel('catalog/resource_eav_attribute')
						->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);

	$updates = [
		'delete' => [],
		'value'  => []
	];
	foreach ($attribute->getSource()->getAllOptions(false) as $option) {
		$collection = Mage::getModel('catalog/product')
						->getCollection()
						->addAttributeToSelect($code)
						->addAttributeToFilter($code, $option['value']);

		if ($collection->getSize() === 0) {
			// We need to delete this one
			echo $option['label'] . PHP_EOL;
			$updates['delete'][$option['value']] = true;
			$updates['value'][$option['value']]  = true;
		}
	}

	$setup->addAttributeOption($updates);
}
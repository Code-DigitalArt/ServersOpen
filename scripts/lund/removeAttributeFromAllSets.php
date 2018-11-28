<?php

require 'app/Mage.php';
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(0);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

$attributeSets = Mage::getModel('eav/entity_attribute_set')->getCollection();

foreach ($attributeSets as $attributeSet) {
	foreach (['sub_model', 'sub_detail'] as $attributeCode) {
		$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);

		try {
			Mage::getModel('catalog/product_attribute_set_api')->attributeRemove($attribute->getId(), $attributeSet->getId());
		} catch (Exception $e) {
			
		}
	}
}

<?php

require_once('store/app/Mage.php');
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('display_errors', 1);
umask(0);
set_time_limit(0);
ini_set('memory_limit','1024M');

$attributesToAdd = [
	'vendor_product', 
	'model_year', 
	'vendor_caption', 
	'quick_info', 
	'specifications', 
	'our_take', 
	'final_thoughts'
];
// This is because the you adding the attribute to catalog_products entity 
// ( there is different entities in magento ex : catalog_category, order,invoice... etc )
$attributeSetEntityType	= Mage::getModel('eav/entity_type')
							->getCollection()
							->addFieldToFilter('entity_type_code','catalog_product')
							->getFirstItem();

$attributeSetCollection = $attributeSetEntityType->getAttributeSetCollection();

foreach ($attributesToAdd as $attributeCode) {
	$attribute = Mage::getResourceModel('eav/entity_attribute_collection')
					->setCodeFilter($attributeCode)
					->getFirstItem();

	foreach ($attributeSetCollection as $attributeSet) {
		$group = Mage::getModel('eav/entity_attribute_group')
					->getCollection()
					->addFieldToFilter('attribute_set_id', $attributeSet->getId())
					->addFieldToFilter('attribute_group_name', 'General')
					->getFirstItem();

		$newItem = Mage::getModel('eav/entity_attribute');
		$newItem
			->setEntityTypeId($attributeSetEntityType->getId())
			->setAttributeSetId($attributeSet->getId())
			->setAttributeGroupId($group->getId())
			->setAttributeId($attribute->getId())
			->setSortOrder(10)
			->save();

		echo 'Attribute ' . $attributeCode . ' Added to Attribute Set ' 
			. $attributeSet->getAttributeSetName() 
			. ' in Attribute Group ' . $group->getAttributeGroupName() . PHP_EOL;
	}
}
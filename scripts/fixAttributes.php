<?php

require_once('app/Mage.php');
Mage::setIsDeveloperMode(true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('display_errors', 1);
umask(0);
set_time_limit(0);
ini_set('memory_limit','5000M');

$setup        = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId = $setup->getEntityTypeId('catalog_product');

$attributeCodes = [
	'dim_a', 'dim_b', 'dim_c', 'dim_d', 'dim_e', 'dim_f', 
	'dim_g'
];
foreach ($attributeCodes as $code) {
	$attribute = Mage::getModel('catalog/resource_eav_attribute')
					->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);

	echo 'Clearing out attribute id ' . $attribute->getId() . PHP_EOL;

	$updates = [
		'delete' => [],
		'value'  => []
	];

	foreach ($attribute->getSource()->getAllOptions(false) as $option) {
		$updates['delete'][$option['value']] = true;
		$updates['value'][$option['value']]  = true;
	}

	$setup->addAttributeOption($updates);
}

echo 'Fixing attribute sets' . PHP_EOL;

$attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
$remove        = ['year', 'make', 'model'];

foreach ($remove as $code) {
	$attribute = Mage::getModel('catalog/resource_eav_attribute')
					->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);
	foreach ($attributeSets as $attributeSet) {
		try {
			Mage::getModel('catalog/product_attribute_set_api')->attributeRemove($attribute->getId(), $attributeSet->getId());
		} catch (Exception $e) {

		}
	}
}

// We need to remove these attributes and re create them
$attributeToReset =[
	[
		'code'                          => 'height',
		'label'                         => 'Height',
		'input'                         => 'select',
		'is_searchable'                 => '1',
		'is_visible_in_advanced_search' => '1',
		'is_comparable'                 => '1',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '1',
		'is_filterable'                 => '1',
		'is_filterable_in_search' 	    => '1',
	], [
		'code'                          => 'width',
		'label'                         => 'Width',
		'input'                         => 'select',
		'is_searchable'                 => '1',
		'is_visible_in_advanced_search' => '1',
		'is_comparable'                 => '1',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '1',
		'is_filterable'                 => '1',
		'is_filterable_in_search' 	    => '1',
	], [
		'code'                          => 'length',
		'label'                         => 'Length',
		'input'                         => 'select',
		'is_searchable'                 => '1',
		'is_visible_in_advanced_search' => '1',
		'is_comparable'                 => '1',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '1',
		'is_filterable'                 => '1',
		'is_filterable_in_search' 	    => '1',
	], [
		'code'                          => 'flare_height',
		'label'                         => 'Flare Height',
		'input'                         => 'select',
		'is_searchable'                 => '1',
		'is_visible_in_advanced_search' => '1',
		'is_comparable'                 => '1',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '1',
		'is_filterable'                 => '1',
		'is_filterable_in_search' 	    => '1',
	], [
		'code'                          => 'flare_tire_coverage',
		'label'                         => 'Flare Tire Coverage',
		'input'                         => 'select',
		'is_searchable'                 => '1',
		'is_visible_in_advanced_search' => '1',
		'is_comparable'                 => '1',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '1',
		'is_filterable'                 => '1',
		'is_filterable_in_search' 	    => '1',
	]
];

foreach ($attributeToReset as $attributeData) {
	$attribute = Mage::getModel('catalog/resource_eav_attribute')
					->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attributeData['code'])
					->delete();
	$_attribute = array(
		'attribute_code'                => $attributeData['code'],
		'is_global'                     => '1',
		'frontend_input'                => $attributeData['input'],
		'is_unique'                     => '0',
		'is_required'                   => '0',
		'is_configurable'               => '0',
		'is_searchable'                 => $attributeData['is_searchable'],
		'is_visible_in_advanced_search' => $attributeData['is_visible_in_advanced_search'],
		'is_comparable'                 => $attributeData['is_comparable'],
		'is_used_for_price_rules'       => '0',
		'is_wysiwyg_enabled'            => '0',
		'is_html_allowed_on_front'      => '1',
		'is_visible_on_front'           => $attributeData['is_visible_on_front'],
		'used_in_product_listing'       => $attributeData['used_in_product_listing'],
		'used_for_sort_by'              => $attributeData['used_for_sort_by'],
		'frontend_label'                => $attributeData['label'],
		'is_filterable'                 => $attributeData['is_filterable'],
		'is_filterable_in_search' 	    => $attributeData['is_filterable_in_search']
	);


	$model = Mage::getModel('catalog/resource_eav_attribute');

	if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0)
		$_attribute['backend_type'] = $model->getBackendTypeByInput($_attribute['frontend_input']);
	
	$model
		->setEntityTypeId($entityTypeId)
		->addData($_attribute)
		->setIsUserDefined(1);

	try {
		$model->save();
	} catch (Exception $e) { 
		Mage::logException($e);
	}

	// Now add them back to all sets
	foreach ($attributeSets as $attributeSet) {
		$groups = Mage::getModel('eav/entity_attribute_group')
	            ->getResourceCollection()
	            ->setAttributeSetFilter($attributeSet->getId())
	            ->addFieldToFilter('attribute_group_name', 'PIMS Data')
	            ->setSortOrder()
	            ->load();
	    foreach ($groups as $group) {
	    	Mage::getModel('catalog/product_attribute_set_api')->attributeAdd($model->getId(), $attributeSet->getId(), $group->getId());
	    }
	}
}
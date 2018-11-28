<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('catalog_category', 'small_image', array(
	'group'         => 'General Information',
	'input'         => 'image',
	'type'          => 'varchar',
	'label'         => 'Small Image',
	'backend'       => 'catalog/category_attribute_backend_image',
	'visible'       => 1,
	'required'        => 0,
	'user_defined' => 1,
	'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
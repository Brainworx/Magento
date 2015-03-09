<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
/* $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$attribute  = array(
	'type'			=> 'text',
	'label'			=> 'Hearedfrom',
	'visible'		=> true,
	'required'		=> false,
	'user_defined'	=> true,
	'searchable'	=> true,
	'filterable'	=> true,
	'comparable'	=> false,
);

$installer->addAttribute('order', 'hearedfrom', $attribute);

$installer->endSetup();
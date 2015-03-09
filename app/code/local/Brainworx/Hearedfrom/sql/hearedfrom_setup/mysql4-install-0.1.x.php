<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
/* $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$sql = "SELECT entity_type_id FROM ".$this->getTable('eav_entity_type')." WHERE entity_type_code='order'";
Mage::Log("Hearedfrom: " .$sql);
$row = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($sql);

$attribute  = array(
	'type'			=> 'text',
	'backend_type'  => 'text',
	'frontend_input' => 'text',
	'label'			=> 'Hearedfrom',
	'visible'		=> true,
	'required'		=> false,
	'user_defined'	=> false,
	'searchable'	=> false,
	'filterable'	=> false,
	'comparable'	=> false,
);

$installer->addAttribute($row['entity_type_id'], 'hearedfrom', $attribute);

$installer->endSetup();
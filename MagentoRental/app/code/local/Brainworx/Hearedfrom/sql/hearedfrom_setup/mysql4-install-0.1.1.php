<?php

$installer = $this;

$installer->startSetup();

//get the entity type for orders
$sql = "SELECT entity_type_id FROM ".$this->getTable('eav_entity_type')." WHERE entity_type_code='order'";
Mage::Log("Hearedfrom: " .$sql);
$row = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($sql);
   
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
//add attribute for the sale/order
$installer->addAttribute($row['entity_type_id'], 'hearedfrom', $attribute);

$installer->endSetup();
<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
/* $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

//START Add order attribute by Branko Ajzele
//‘entity_type_id’  => 11, - 11 is the id of the entity model “sales/order”. This could be different on our system!
$sql = "SELECT entity_type_id FROM ".$this->getTable('eav_entity_type')." WHERE entity_type_code='order'";
Mage::Log("Hearedfrom: " .$sql);
$row = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchRow($sql);
   
$attribute  = array(
	'type'			=> 'text',
	'label'			=> 'Hearedfrom',
	'visible'		=> false,
	'required'		=> false,
	'user_defined'	=> false,
	'searchable'	=> true,
	'filterable'	=> true,
	'comparable'	=> false,
);

$installer->addAttribute($row['entity_type_id'], 'hearedfrom', $attribute);
//END Add customer attribute Branko Ajzele

$installer->endSetup();
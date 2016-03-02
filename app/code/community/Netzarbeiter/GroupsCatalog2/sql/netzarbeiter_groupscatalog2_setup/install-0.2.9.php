<?php
/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupsCatalog2
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Netzarbeiter_GroupsCatalog2_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Just to be sure the latest version of the attributes is installed
$installer->deleteTableRow(
    'eav/attribute', 'attribute_code', Netzarbeiter_GroupsCatalog2_Helper_Data::HIDE_GROUPS_ATTRIBUTE
);

foreach (array('catalog_product', 'catalog_category') as $entityType) {
    $installer->addGroupsCatalogAttribute($entityType);
    $installer->dropIndexTable($entityType);
    $installer->createIndexTable($entityType);
}

$installer->endSetup();

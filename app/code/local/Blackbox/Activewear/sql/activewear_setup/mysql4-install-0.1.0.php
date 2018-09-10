<?php
$this->startSetup();

$this->addAttribute('catalog_product', 'aw_price', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Activewear Price',
    'backend'       => '',
    'visible'       => false,
    'required'      => false,
    'visible_on_front' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order'        => 100,
));

$this->endSetup();
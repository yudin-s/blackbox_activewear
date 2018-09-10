<?php
class Blackbox_Activewear_Model_Category{

    /**
     * Api gateway
     * @var Blackbox_Activewear_Model_Api_Web
     */
    private $api = null;

    /***
     * @var array Assoc array ID Cat Activewear - > Cat Magento
     */
    private static $assoc = [];
    private $sumator = 1000;

    public function __construct($id,$requiredFields=[])
    {
        $this->api = Mage::getModel('blackbox_activewear/api_web');

    }

    public function loadByName($name){
        $_category = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', $name)
            ->getFirstItem();

        $categoryId = $_category->getId();
    }
    public function toMagentoCat(){
        $category = Mage::getModel('catalog/category');
        $category->setName($this->name);
        $category->setUrlKey($this->url);
        $category->setIsActive(1);
        $category->setDisplayMode('PRODUCTS');
        $category->setIsAnchor(1); //for active anchor
        $category->setStoreId(Mage::app()->getStore()->getId());
        $parentCategory = Mage::getModel('catalog/category')->load(1);
        $category->setPath($parentCategory->getPath());
        return $category;
    }

}
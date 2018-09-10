<?php

/**
 * Class Blackbox_Activewear_Model_Style
 * @link https://api.ssactivewear.com/V2/Styles.aspx
 */
class Blackbox_Activewear_Model_Style extends Blackbox_Supplier_Model_Product_Abstract {
    /**
     * Api gateway
     * @var Blackbox_Activewear_Model_Api_Web
     */
    private $api = null;

    protected $helper;
    protected $baseUrl = 'https://api.ssactivewear.com/';
    protected $imageUrl = 'https://www.ssactivewear.com/';

    protected $gender = '';

    protected $products = null;

    public function __construct($id,$requiredFields=[])
    {
        $this->api = Mage::getModel('blackbox_activewear/api_web');
        $this->helper = Mage::helper('blackbox_supplier');
    }

    /**
     * Return products related to this style
     * @return Blackbox_Activewear_Model_Product[] array of Activewear products
     */
    public function getRelatedProducts(){
        if(!isset($this->styleID)){
            Mage::throwException('SS Activewear style empty');
        }

        $factory = Mage::getModel('blackbox_activewear/product');
        if ($this->products == null) {
            $this->products = $this->api->getProducts(['style' => "$this->styleID",
                'fields' => 'UnitWeight,PiecePrice,Sku,ColorFrontImage,ColorSideImage,ColorBackImage,ColorName,sizeName,color1,color2,gtin']);
        }

        $result = [];
        $alreadyColored = [];
        $cats  = $this->toMageCats();
        foreach ($this->products as $product){
            $prod = $factory->factory($product);
            if ($alreadyColored[$prod->colorName] == true) continue;
            else $alreadyColored[$prod->colorName] = true;

            $prod->name = "$this->brandName - $prod->colorName $this->title";
            $prod->description = "$this->description";
            $prod->cats = $cats;
            $result[] = $prod;

        }
        return $result;
    }

    public function getData() {
        $images = array();
        if (($path = $this->helper->downloadImage($this->baseUrl, $this->styleImage, 'products'))) {
            $images[$path] = array('image', 'small_image', 'thumbnail');
        }
        if (($path = $this->helper->downloadImage($this->baseUrl, $this->brandImage, 'products'))) {
            $images[$path] = array();
        }
        $result =  array(
            'name' => $this->title,
            'sku' => $this->styleName,
            'price' => '0chan',
            'description' => $this->description,
            'short_description' => $this->description,
            'Images' => $images,
        );

        if ($this->brandName) {
            $result['brand'] = $this->brandName;
        }

        $result['gender'] = $this->_getGender();

        return $result;
    }

    public function getRelatedProductsData() {
        if(!isset($this->styleID)){
            Mage::throwException('SS Activewear style empty');
        }

        if ($this->products == null) {
            $this->products = $this->api->getProducts(['style' => "$this->styleID",
                'fields' => 'UnitWeight,PiecePrice,Sku,ColorFrontImage,ColorSideImage,ColorBackImage,ColorName,sizeName,color1,color2,gtin,brandName']);
        }

        $result = array();

        foreach($this->products as $product) {
            $colorCode = $product->color1;
            if ($product->color2) {
                $colorCode .= ',' . $product->color2;
            }

            $images = array();
            $temp = array(
                'colorFrontImage' => array('image', 'small_image', 'thumbnail', 'label' => 'Front'),
                'colorSideImage' => array('label' => 'Side'),
                'colorBackImage' => array('label' => 'Back')
            );

            foreach($temp as $key => $value) {
                if ($product->$key) {
                    if ($path = $this->helper->downloadImage($this->baseUrl, $product->$key, 'products')) {
                        $images[$path] = $value;
                    } else {
                        echo 'Can\'t download image ' . $product->$key . PHP_EOL;
                    }
                }
            }

            $resultCur = array(
                'name' => "$this->brandName $this->title - $product->colorName $product->sizeName",
                'sku' => $product->sku,
                'weight' => $product->unitWeight,
                'gtin' => $product->gtin,
                'size' => $product->sizeName,
                'color' => $product->colorName,
                'color_code' => $colorCode,
                'price' => $product->piecePrice,
                'aw_price' => $product->piecePrice,
                'description' => $this->description,
                'short_description' => $this->description,
                'Images' => $images
            );

            if ($product->brandName) {
                $resultCur['brand'] = $product->brandName;
            }

            $gender = $this->_getGender();
            if ($gender) {
                $resultCur['gender'] = $gender;
            }

            $result[] = $resultCur;
        }

        return $result;
    }

    public function getRawProducts()
    {
        if(!isset($this->styleID)) {
            Mage::throwException('SS Activewear style empty');
        }

        if ($this->products == null) {
            return $this->api->getProducts(['style' => "$this->styleID",
                'fields' => 'UnitWeight,PiecePrice,Sku,ColorFrontImage,ColorSideImage,ColorBackImage,ColorName,sizeName,color1,color2,gtin,brandName']);
        } else {
            return $this->products;
        }
    }

    public function getCategory()
    {
        return $this->baseCategory;
    }

    private function toMageCats(){
        $startCats = ['Activewear',$this->brandName,$this->baseCategory];
        $result = [];
        print_r($startCats);
        foreach($startCats as $catName) {
            $category = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name', $catName)
                ->getFirstItem(); // The child category

            $categoryId = $category->getId();

            if (!$categoryId) {
                $category = Mage::getModel('catalog/category');
                $category->setName($catName);
                $category->setUrlKey(time());
                $category->setIsActive(1);
                $category->setDisplayMode('PRODUCTS');
                $category->setIsAnchor(1); //for active anchor
                $category->setStoreId(Mage::app()->getStore()->getId());
                $parentCategory = Mage::getModel('catalog/category')->load(Mage_Catalog_Model_Category::TREE_ROOT_ID);
                $category->setPath($parentCategory->getPath());
                $category->save();

                $category = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('name', $catName)
                    ->getFirstItem(); // The child category

                $categoryId = $category->getId();

            }
            $result[] = $categoryId;
        }
        return $result;
    }

    public function setProducts($products) {
        $this->products = $products;
    }

    public function addProduct($product) {
        if ($this->products == null) {
            $this->products = array($product);
        } else {
            $this->products[] = $product;
        }
    }

    public  function toMageProductModel()
    {
        // TODO: Implement toMageProductModel() method.
    }

    protected function _getGender()
    {
        if ($this->gender === '') {
            $this->gender = Mage::helper('blackbox_supplier')->getGenderFromName($this->title);
        }
        return $this->gender;
    }
}
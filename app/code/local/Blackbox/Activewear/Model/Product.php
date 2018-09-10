<?php

class Blackbox_Activewear_Model_Product extends Blackbox_Supplier_Model_Product_Abstract
{
    /**
     * @var Blackbox_Activewear_Model_Api_Web
     */
    public $api = null;

    private $sizesConvert = [
        'S', 'M', 'L', 'XL', 'XXL'
    ];
    private $SIZE_REGEXP = '/(.+?)-(.+?)$/';

    public function __construct()
    {
        $this->api = Mage::getModel('blackbox_activewear/api_web');

    }

    public function toMageProductModel()
    {

        $img_url = $this->imageToMagento();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = Mage::getModel('catalog/product');

        $product->setStoreId(1)//you can set data in store scope
        ->setWebsiteIds(array(1))//website ID the product is assigned to, as an array
        ->setAttributeSetId(4)//ID of a attribute set named 'default'
        ->setTypeId('simple')//product type
        ->setCreatedAt(strtotime('now'))//product creation time
        ->setSku($this->sku)//SKU
        ->setName($this->name)//product name
        ->setWeight($this->unitWeight)
            ->setStatus(1)//product status (1 - enabled, 2 - disabled)
            ->setTaxClassId(0)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)//catalog and search visibility
            ->setCountryOfManufacture('US')//country of manufacture (2-letter country code)
            ->setPrice($this->piecePrice)//price in form 11.22
            ->setCost($this->piecePrice)//price in form 11.2
            ->setDescription($this->description)
            ->setShortDescription($this->description)
            ->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
            ->addImageToMediaGallery($img_url['side'], array('thumbnail', 'small_image', 'image'), false, false)
            ->addImageToMediaGallery($img_url['back'], array('thumbnail', 'small_image', 'image'), false, false)
            ->addImageToMediaGallery($img_url['front'], array('thumbnail', 'small_image', 'image'), false, false)
            ->setStockData(array(
                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
                    'manage_stock' => 0, //manage stock
                    'is_in_stock' => 1, //Stock Availability
                )
            )
            ->setCategoryIds($this->cats); //assign product to categories
        return $product;
    }

    public function brandToCatId()
    {

    }

    public function imageToMagento()
    {
        $url = $this->api->getBaseImgUrl();
        $result = [];
        $result['front'] = $this->api->uploadRemoteImg($url . $this->colorFrontImage);
        $result['side'] = $this->api->uploadRemoteImg($url . $this->colorSideImage);
        $result['back'] = $this->api->uploadRemoteImg($url . $this->colorBackImage);
        return $result;
    }

    public function stringSizeToArray()
    {
        $array = [];
        $result = [];
        preg_match_all($this->SIZE_REGEXP, $this->sizePriceCodeName, $array, PREG_SET_ORDER, 0);

        $start = $array[0][1];
        $end = $array[0][2];

        $i_start = array_keys($this->sizesConvert, $start);

        if (count($i_start) < 1) {
            return $result;
        }

        $i = $i_start[0];

        for ($i = $i; $this->sizesConvert[$i] != $end; $i++) {
            $result[] = $this->sizesConvert[$i];
        }
        if ($result[count($result) - 1] != $end) {
            $result[] = $end;
        }
        return $result;
    }

}
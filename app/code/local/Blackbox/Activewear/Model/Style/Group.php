<?php

class Blackbox_Activewear_Model_Style_Group
{
    private $api = null;

    protected $helper;
    protected $baseUrl = 'https://api.ssactivewear.com/';
    protected $imageUrl = 'https://www.ssactivewear.com/';

    protected $styles = array();

    protected $styleFactory;

    public function __construct($id,$requiredFields=[])
    {
        $this->api = Mage::getModel('blackbox_activewear/api_web');
        $this->helper = Mage::helper('blackbox_supplier');
        $this->styleFactory = Mage::getModel('blackbox_activewear/style');
    }

    public function addStyle($style)
    {
        $style = $this->styleFactory->factory($style);
        $this->styles[$style->styleID] = $style;
        return $this;
    }

    public function addStyles(array $styles)
    {
        foreach($styles as $style) {
            $this->addStyle($style);
        }
        return $this;
    }

    public function getStylesWithProducts()
    {
        $stylesString = '';
        foreach ($this->styles as $style) {
            if (isset($style->styleID)) {
                if (!empty($stylesString)) {
                    $stylesString .= ',';
                }
                $stylesString .= $style->styleID;
            }
        }

        $products = $this->api->getProducts(['style' => $stylesString,
            'fields' => 'styleID,UnitWeight,PiecePrice,Sku,ColorFrontImage,ColorSideImage,ColorBackImage,ColorName,sizeName,color1,color2,gtin']);

        foreach($products as $product) {
            $this->styles[$product->styleID]->addProduct($product);
        }

        return $this->styles;
    }
}
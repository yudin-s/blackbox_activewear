<?php

class Blackbox_Activewear_Model_Api_Web extends Blackbox_Supplier_Model_Api_Abstract{
    private $_login;
    private $_password;
    private $_gateway;
    const CONFIG_NODE = 'blackbox_supplier_setting/blackbox_activewear/';

    /**
     * Blackbox_Activewear_Model_Api_Web constructor.
     */
    public function __construct()
    {
        $this->setLogin('22831');
        $this->setPassword('49da4fac-9f11-4d7d-8f26-fefaeb28fb14');
        $this->setGateway('https://api.ssactivewear.com/V2/');
    }
    private function getConfig($param){
        $result = Mage::getStoreConfig(self::CONFIG_NODE . $param);
        return Mage::helper('core')->decrypt($result);
    }
    public function sendRequest($action,$additional = '' , $data = []){

        $url = $this->getGateway().'/'.$action ;
        if( $additional!='' ){
            $url .='/'.$additional;
        }
        if(count($data)){
          $url.='?'.  http_build_query($data);
        }

        $username  = $this->getLogin();
        $password  = $this->getPassword();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_PROTOCOLS,CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $return = curl_exec($curl);
        $return = $this->removeBOM($return);
        $obj = json_decode($return);
        return $obj;
    }

    private function removeBOM($data) {
        if (0 === strpos(bin2hex($data), 'efbbbf')) {
            return substr($data, 3);
        }
        return $data;
    }
    /**
     * @param Mage_Catalog_Model_Product $product Configurable product what need to calc
     * @return int Price in USD
     */
    public function getPrice(Mage_Catalog_Model_Product $product)
    {
        // TODO: Implement getPrice() method.
    }

    /**
     * @param Mage_Sales_Model_Quote $order place order to supplier
     * @return mixed Response from API.
     */
    public function placeOrder(Mage_Sales_Model_Quote $order)
    {
        // TODO: Implement placeOrder() method.
    }

    /**
     * @return Mage_Catalog_Model_Product[] product list from supplier
     */
    public function getProducts($data = [])
    {
        return $this->sendRequest('products','', $data);
    }

    /**
     * @return Mage_Catalog_Model_Product[] product list from supplier
     */
    public function getProduct($sku = '',$data = [])
    {
        return $this->sendRequest('products',$sku, $data)[0];
    }

    public function getCategories($catId='', $data = []){
        return $this->sendRequest('categories',$catId, $data);
    }

    public function getStyles($styleId = '', $data =[]){
        return $this->sendRequest('styles',$styleId,$data);
    }
    public  function getBaseImgUrl()
    {
        return 'https://www.ssactivewear.com/';
    }
}
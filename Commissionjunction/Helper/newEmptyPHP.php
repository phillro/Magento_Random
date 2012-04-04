<?php

class Atreus_Commissionjunction_Helper_Data  extends Mage_Core_Helper_Abstract {

    public function getDefaults() {
        return array('available'=>'yes','buyurl'=>'','imageurl'=>'small_image');
    }

    public function getCID(){
        return Mage::getStoreConfig('catalog/atreus_commissionjunction/cid');
    }

    public function getSubID(){
        return Mage::getStoreConfig('catalog/atreus_commissionjunction/subid');
    }

    public function getAID(){
        return Mage::getStoreConfig('catalog/atreus_commissionjunction/aid');
    }



    public function getFields(){
        return array("name"=>"name","keywords"=>"meta_keyword","sku"=>"sku","description"=>"description"
                ,"buyurl"=>"product_url","imageurl"=>"imageurl","available"=>"yes","price"=>"price");
    }

    public function generateProductCSVHeader(){
        $headerString='';
        $headerString.='&CID='.$this->getCID()."\n";
        $headerString.='&SUBID='.$this->getSubID()."\n";
        $headerString.='&AID='.$this->getAID()."\n";
        $headerString.='&PROCESSTYPE=OVERWRITE'."\n";
        $parameters = implode("|",array_keys($this->getFields()))."\n";
        $headerString.='&PARAMETERS='.strtoupper($parameters)."\n";
        return $headerString;
    }


    public function generateProductCSVBody() {
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('meta_keyword')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('name')
                ->joinField('qty',
	                    'cataloginventory/stock_item',
			    'qty',
			    'product_id=entity_id',
			    '{{table}}.stock_id=1',
		'left');
	$collection->addFieldToFilter('qty', 1);
        $collection->addFieldToFilter('status', 1);
        //$collection->getSelect()->limit(15);
        $headerFieldMap = array_keys($this->getFields());
        $out=$this->productCollectionToCsv($collection, $headerFieldMap);
        return $out;

    }

    function formatRow($value){
       $formatted= '"' . addslashes(strip_tags(nl2br(str_replace("\r", "", str_replace("\n", "", stripslashes(ltrim($value))))))) . '"';
       return $formatted;

    }




    function productCollectionToCsv($collection, $headerFieldMap = array()) {
        $csv;

        ## Grab the first element to build the header

        $temp = array();
        foreach( $headerFieldMap as $colHeader => $fieldName ) {
            $temp[] = $fieldName;
        }
        $csv = strtoupper(implode( ',', $temp )) . "\n";

	$defaults= $this->getDefaults();
        ## Add the data for the rest



        foreach( $collection as $item ) {
            $row = array();
            $row[]=$this->formatRow($item->getData('name'));
            $keywords=$item->getData('meta_keyword');
            if(strlen($keywords)<=2){
                       $keywords=$item->getData('name');
            }
            $row[]=$this->formatRow($keywords);
            $row[]=$this->formatRow($item->getData('sku'));
            $row[]=$this->formatRow($item->getData('description'));
            $row[]=$item->getProductUrl();
            $imgHelper = new OnePica_ImageCdn_Helper_Image();
            $image=$imgHelper->init($item,'small_image');
            $row[]=$image;
            $row[]='yes';
            $row[]=$this->formatRow($item->getData('price'));

            $csv.=implode( ',', $row ) . "\n";

        }

        return $csv;
    }




}

?>

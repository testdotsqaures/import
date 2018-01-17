<?php

namespace Dotsquares\Attributes\Controller\Adminhtml\Index;
use Magento\Eav\Model\Config;

class Exportatt extends \Magento\Backend\App\Action
{
    public function execute()
    {
      		
			//$attributeFactory = $this->_attributeCollection->create()->getCollection();
			//$collection = $this->attributeFactory->create()->getCollection();
			//$collection->setOrder('attribute_id');
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$ent_type_id = $objectManager
            ->create('Magento\Eav\Model\Config')
            ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getEntityTypeId();
			
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('catalog/eav_attribute'); // the table name in this example is 'mytest'
			$select_attribs = $connection->select()
            ->from(array('ea'=>$resource->getTableName('eav_attribute')))
            ->join(array('c_ea'=>$resource->getTableName('catalog_eav_attribute')), 'ea.attribute_id = c_ea.attribute_id');
			$select_prod_attribs = $select_attribs->where('ea.entity_type_id = '.$ent_type_id)
                                            ->order('ea.attribute_id ASC');

			$product_attributes = $connection->fetchAll($select_prod_attribs);
		//	$obj = \Magento\Framework\App\ObjectManager::getInstance();

			/** @var \Magento\Catalog\Model\Config $config */
			//$config= $obj->get('Magento\Catalog\Model\Config');

			//$attributeGroupId = $config->getAttributeGroupId(1, 'General');
			//echo "<pre>"; print_r($product_attributes); echo "</pre>";
			//die;
			$select_attrib_option = $select_attribs
                                ->join(array('e_ao'=>$resource->getTableName('eav_attribute_option'), array('option_id')), 'c_ea.attribute_id = e_ao.attribute_id')
                                ->join(array('e_aov'=>$resource->getTableName('eav_attribute_option_value'), array('value')), 'e_ao.option_id = e_aov.option_id and store_id = 0')
                                ->order('e_ao.attribute_id ASC');
			$product_attribute_options = $connection->fetchAll($select_attrib_option);
			$attributesCollection = $this->mergeCollections($product_attributes, $product_attribute_options);
			$this->prepareCsv($attributesCollection);

			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setPath('attributes/index/index');

		
	   
	}
	
		
	function mergeCollections($product_attributes, $product_attribute_options){

		foreach($product_attributes as $key => $_prodAttrib){
			$values = array();
			$attribId = $_prodAttrib['attribute_id'];
			foreach($product_attribute_options as $pao){
				if($pao['attribute_id'] == $attribId){
					$values[] = $pao['value'];
				}
			}
			if(count($values) > 0){
				$values = implode(";", $values);
				$product_attributes[$key]['_options'] = $values;
			}
			else{
				$product_attributes[$key]['_options'] = "";
			}
			/*
				temp
			*/
			$product_attributes[$key]['attribute_code'] = $product_attributes[$key]['attribute_code'];
		}

		return $product_attributes;

	}
	
	function prepareCsv($attributesCollection, $filename = "export_all_attributes.csv"){

    $f = fopen('php://memory', 'w');
    $first = true;
    foreach ($attributesCollection as $line) {
        if($first){
            $titles = array();
            foreach($line as $field => $val){
                $titles[] = $field;
            }
            fputcsv($f, $titles);
            $first = false;
        }
        fputcsv($f, $line); 
    }
    fseek($f, 0);
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="'.$filename.'"');
    fpassthru($f);
	}
	
}
<?php
namespace Dotsquares\Attributes\Controller\Adminhtml\Index;


class Importatt extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
	protected $_logger;	

	
	    public function __construct(
		\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $eavattributeFactory,
        \Magento\Eav\Model\Entity\Attribute $eavattribute,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $eavattributeSetFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
	)
	{
        $this->resultPageFactory = $resultPageFactory;
        $this->_eavattributeFactory = $eavattributeFactory;
        $this->_eavattribute = $eavattribute;
        $this->_eavattributeSetFactory = $eavattributeSetFactory;
        $this->_attributeManagement = $attributeManagement;
        $this->_groupCollectionFactory = $groupCollectionFactory;
		parent::__construct($context);
    }
	
	public function execute()
    {
		$files = $this->getRequest()->getFiles();							
		$csv_mimetypes = array(
			'text/csv',
			'text/plain',
			'application/csv',
			'text/comma-separated-values',
			'application/excel',
			'application/vnd.ms-excel',
			'application/vnd.msexcel',
			'application/octet-stream',
			'text/anytext',
			'application/txt',
		);
		
		
		if(in_array($files['import_fileatt']['type'],$csv_mimetypes)){
			$file = fopen($files['import_fileatt']['tmp_name'], "r");
			$lists = array();
			while(! feof($file))
			{
				$lists[] = fgetcsv($file);
			}
			unset($lists[0]);
			$data = array();
			foreach($lists as $list){
				if(is_array($list)){
					$attributeCode = $list[1];
					$this->_eavattribute->loadByCode('catalog_product', $attributeCode); 
					if($this->_eavattribute->getAttributeId() == ''){
						$attribute_label = $list[0];
						$att_code = Array($attribute_label,$attribute_label);
						$data['frontend_label'] = $att_code;
						$data['attribute_code'] = $attributeCode;
						$data['entity_type_id'] = 4;
						$attribute_type = $list[2];
						if($attribute_type == 'select' || $attribute_type == 'multiselect' ){
							$options_string = $list[3];
							if($options_string != ''){
								$option_array = $this->addoptions($options_string);
								$data['option'] = $option_array;
								$data['dropdown_attribute_validation'] = '';
								$data['is_searchable'] = 1;
							}
							$data['frontend_input'] = $list[2];
							$data['swatch_input_type'] = 'dropdown';
							$data['is_filterable'] = $list[4];
							$data['is_filterable_in_search'] = $list[4];
						}else{
							$data['frontend_input'] = $list[2];
							$data['is_searchable'] = 0;
						}
						$data['is_required'] = 0;
						$data['update_product_preview_image'] = 0;
						$data['use_product_image_for_swatch'] = 0;
						$data['visual_swatch_validation'] = '';
						$data['text_swatch_validation'] = '';
						$data['is_global'] = 1;
						$data['default_value_text'] = '';
						$data['default_value_yesno'] = 0;
						$data['default_value_date'] = '';
						$data['default_value_textarea'] = '';
						$data['is_unique'] = 0;
						$data['frontend_class'] = '';
						$data['is_used_in_grid'] = 1;
						$data['is_visible_in_grid'] = 1;
						$data['is_filterable_in_grid'] = 1;
						$data['search_weight'] = 3;
						$data['is_visible_in_advanced_search'] = 1;
						$data['is_comparable'] = 1;
						$data['is_used_for_promo_rules'] = 1;
						$data['is_html_allowed_on_front'] = 1;
						$data['is_visible_on_front'] = 1;
						$data['used_in_product_listing'] = 0;
						$data['used_for_sort_by'] = 0;
						$data['backend_model'] = '';
						$data['apply_to'] = Array();
						$data['backend_type'] = 'varchar';
						$data['default_value'] = '';
						$data['is_user_defined'] = 1;
						$model = $this->_eavattributeFactory->create();
						$model->addData($data);
						$model->save();
						$setCollection = $this->_eavattributeSetFactory->create()->getCollection();
						$setCollection->addFieldToFilter('entity_type_id', 4);
						$setCollection->addFieldToFilter('attribute_set_name', $list[5]);
						foreach ($setCollection as $attributeSet) {
							$group = $this->_groupCollectionFactory->create()
									->addFieldToFilter('attribute_group_name', ['eq' => 'Product Details'])
									->addFieldToFilter('attribute_set_id', ['eq' => 4])
									->getFirstItem();
							$groupId = $group->getId() ? : $attributeSet->getDefaultGroupId();
							$this->_attributeManagement->assign(
									'catalog_product',
									$attributeSet->getId(), 
									$groupId, 
									$attributeCode, 
									$attributeSet->getCollection()->count() * 10
							);
						}
					}else{
						$this->messageManager->addError(__('An attribute named \'%1\' already exists.', $attributeCode));
						continue;
					}	
				}
			}
			$this->messageManager->addSuccess(
				__('Attributes created successfully.')
			);
			$this->_redirect('*/*/');
			return;
		}else{
			$this->messageManager->addError(
					__('Please upload correct csv format.')
					);
			$this->_redirect('*/*/');
			return;
		}
	}
	
	public function addoptions($options_string){
		$options = explode(',' , $options_string);
		$count = count($options);
		$option_array = array();
		$option_array['order'] = array();
		$option_array['value'] = array();
		for($i = 0; $i < $count; $i++){
			$option_label = array($options[$i]);
			$option_array['order']['option_'.$i] = $i+1; 
			$option_array['value']['option_'.$i] = $option_label;
		}
		return $option_array;
	}

}
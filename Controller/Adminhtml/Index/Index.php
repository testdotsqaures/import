<?php

namespace Dotsquares\Attributes\Controller\Adminhtml\Index;
use Vendor\Module\Model\Config\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\Config;

class Index extends \Magento\Backend\App\Action
{
    protected $_attributeCollection;
    protected $attributeFactory;

	public function __construct(\Magento\Backend\App\Action\Context $context,
	\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection,
	 AttributeFactory $attributeFactory,
	 TypeFactory $typeFactory)
	{ 
		parent::__construct($context);
			$this->_attributeCollection = $attributeCollection;
			$this->attributeFactory = $attributeFactory;
			$this->eavTypeFactory = $typeFactory;


    }
	
	public function execute()
    {
		$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
	}

	

}
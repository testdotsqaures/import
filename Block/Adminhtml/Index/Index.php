<?php

namespace Dotsquares\Attributes\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{


	protected $_backendUrl;
	
    public function __construct(
	\Magento\Backend\Model\UrlInterface $backendUrl,
	\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
         $this->_backendUrl = $backendUrl;
		parent::__construct($context, $data);
    }
	public function getPostUrl()
    {
		$url = $this->_backendUrl->getUrl("attributes/index/exportatt");
		return $url;
    }
	
	public function getImportUrl()
    {
		$url = $this->_backendUrl->getUrl("attributes/index/importatt/type/attributes");
		return $url;
    }



}

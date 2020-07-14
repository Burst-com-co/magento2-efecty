<?php

namespace Burst\Efecty\Model\ResourceModel;

class Efecty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('burst_efecty', 'id');
    }
}
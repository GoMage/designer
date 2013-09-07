<?php
class GoMage_ProductDesigner_Model_Design extends Mage_Core_Model_Abstract {
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY                = 'design';

    const CACHE_TAG             = 'designer_design';

    protected $_urlModel;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix     = 'designer_design';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject     = 'designer_design';

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag        = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('gmpd/design');
    }

    public function getConfig() {
        return Mage::getSingleton('gmpd/design_config');
    }

    public function getCollectionByImageIds($imageIds) {
        /**
         * @var $collection GoMage_ProductDesigner_Model_Mysql4_Design_Collection
         */
        $collection = $this->getResourceCollection();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        } else {
            $sessionId = Mage::helper('designer')->getDesignerSessionId();
            $collection->addFieldToFilter('session_id', $sessionId);
        }
        $collection->addFieldToFilter('image_id', array('in'=>$imageIds));
        $collection->getSelect()->group('image_id');
        $collection->addOrder('create_time');
        return $collection;
    }

    public function getImageUrl() {
        return $this->getConfig()->getBaseMediaUrl() . $this->getDesign();
    }

    public function getImagePath() {
        return $this->getConfig()->getBaseMediaPath() . $this->getDesign();
    }
}
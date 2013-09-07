<?php
class GoMage_ProductDesigner_Model_UploadedImage extends Mage_Core_Model_Abstract {
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY                = 'uploadedImage';

    const CACHE_TAG             = 'designer_uploadedImage';

    protected $_urlModel;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix     = 'designer_uploadedImage';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject     = 'designer_uploadedImage';

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag        = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('gmpd/uploadedImage');
    }

    public function getImagePath($imageUrl) {
        $imageUrl = str_replace($this->getConfig()->getBaseTmpMediaUrl(), '', $imageUrl);
        $imageUrl = str_replace($this->getConfig()->getBaseMediaUrl(), '', $imageUrl);
        return $imageUrl;
    }

    public function getUrl($imagePath) {
        return $this->getConfig()->getBaseTmpMediaUrl() . $imagePath;
    }

    public function getDestinationPath($imagePath) {
        return $this->getConfig()->getBaseMediaPath() . $imagePath;
    }

    public function getTempPath($imagePath) {
        return $this->getConfig()->getBaseTmpMediaPath() . $imagePath;
    }

    public function getDestinationDir($imagePath) {
        $expImagePath = explode('/', $imagePath);
        array_pop($expImagePath);
        $imagePath = implode('/', $expImagePath);
        if(strpos($imagePath, $this->getConfig()->getBaseMediaPath()) === false) {
            $imagePath = $this->getConfig()->getBaseMediaPath() . $imagePath;
        }

        return $imagePath;
    }

    public function getConfig() {
        return Mage::getSingleton('gmpd/uploadedImage_config');
    }

    public function getCustomerUploadedImages() {
        $customerSession = $this->_getCustomerSession();
        $uploadedImages = array();
        if($customerId = $customerSession->getCustomerId()) {
            $uploadedImages = $this->getCustomerImages($customerId);
        }
        if($this->_getDesignerSessionId()) {
            $uploadedSessionImages = $this->_getUploadedImagesFromSession();
            $uploadedImages = array_merge($uploadedSessionImages, $uploadedImages);
        }
        return $uploadedImages;
    }

    private function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    public function getCustomerImages($customerId) {
        /**
         * @var $collection GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection
         */
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collectionArray = $collection->toArray();
        return $collectionArray['items'];
    }

    protected function _getUploadedImagesFromSession() {
        /**
         * @var $collection GoMage_ProductDesigner_Model_Mysql4_UploadedImage_Collection
         */
        $designerSessionId = $this->_getDesignerSessionId();
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('session_id', $designerSessionId);
        $collectionArray = $collection->toArray();
        return $collectionArray['items'];
    }

    protected function _getDesignerSessionId() {
        return $this->_getCustomerSession()->getDesignerSessionId();
    }
}
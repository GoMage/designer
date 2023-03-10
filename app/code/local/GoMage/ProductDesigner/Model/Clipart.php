<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Model_Clipart extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'clipart_image';

    const TREE_ROOT_ID = 1;

    const CACHE_TAG = 'designer_clipart';

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected $defaultCategory;

    protected $_cliparts = null;

    protected function _construct()
    {
        $this->_init('gomage_designer/clipart');
    }

    protected function _beforeSave()
    {
        $clipartEntity = Mage::getModel('eav/entity_type')->loadByCode('clipart_image');
        if ($clipartEntity && $clipartEntity->getEntityTypeId()) {
            $this->setData('entity_type_id', $clipartEntity->getEntityTypeId());
        }
        return parent::_beforeSave();
    }

    public function getImagePath($imageUrl)
    {
        $imageUrl = str_replace($this->getConfig()->getBaseTmpMediaUrl(), '', $imageUrl);
        $imageUrl = str_replace($this->getConfig()->getBaseMediaUrl(), '', $imageUrl);
        return $imageUrl;
    }

    public function getUrl($imagePath)
    {
        return $this->getConfig()->getBaseTmpMediaUrl() . $imagePath;
    }

    public function getDestinationPath($imagePath)
    {
        return $this->getConfig()->getBaseMediaPath() . $imagePath;
    }

    public function getTempPath($imagePath)
    {
        return $this->getConfig()->getBaseTmpMediaPath() . $imagePath;
    }

    public function getDestinationDir($imagePath)
    {
        $expImagePath = explode('/', $imagePath);
        array_pop($expImagePath);
        $imagePath = implode('/', $expImagePath);
        if (strpos($imagePath, $this->getConfig()->getBaseMediaPath()) === false) {
            $imagePath = $this->getConfig()->getBaseMediaPath() . $imagePath;
        }

        return $imagePath;
    }

    public function getConfig()
    {
        return Mage::getSingleton('gomage_designer/clipart_gallery_config');
    }

    /**
     * Return clipart collection filtered by "disabled" attribute
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection
     */
    public function getCliparts()
    {
        if (is_null($this->_cliparts)) {
            $collection      = $this->getCollection()->addFieldToFilter('disabled', 0)
                ->setOrder('main_table.position', 'asc');
            $this->_cliparts = $collection;
        }
        return $this->_cliparts;
    }

}

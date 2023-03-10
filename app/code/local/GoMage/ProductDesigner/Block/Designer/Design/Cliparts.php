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
class GoMage_ProductDesigner_Block_Designer_Design_Cliparts extends Mage_Core_Block_Template
{
    protected $_cliparts = null;

    /**
     * Return clipart collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Clipart_Collection
     */
    public function getCliparts()
    {
        if (is_null($this->_cliparts)) {
            $collection = Mage::getModel('gomage_designer/clipart')->getCliparts();
            if ($subCategoryId = $this->getRequest()->getParam('subCategory')) {
                $collection->addFieldToFilter('main_table.category_id', $subCategoryId);
            }
            if ($categoryId = $this->getRequest()->getParam('mainCategory')) {
                $collection->addCategoryFilter($categoryId);
            }

            if ($tags = $this->getRequest()->getParam('tags')) {
                $collection->addTagsFilter($tags);
            }

            $this->_cliparts = $collection;
        }

        return $this->_cliparts;
    }

    /**
     * Return resized cliaprt image url
     *
     * @param string $image Image
     * @return string
     */
    public function getClipartUrl($image)
    {
        return Mage::helper('gomage_designer/image_cliparts')->init($image)->resize(60, 60)->__toString();
    }

    /**
     * Return original cliaprt image url
     *
     * @param string $image Image
     * @return string
     */
    public function getOriginClipartUrl($image)
    {
        return Mage::getSingleton('gomage_designer/clipart_gallery_config')->getMediaUrl($image);
    }
}

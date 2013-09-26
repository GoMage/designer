<?php 
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Design images block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Adminhtml_Design_View extends Mage_Core_Block_Template
{
    protected $_imageCollection;

    protected $_columnCount = 3;

    /**
     * Return column count
     *
     * @return int|mixed
     */
    public function getColumnCount()
    {
        if ($this->hasData('column_count')) {
            return $this->getData('column_count');
        }

        return $this->getData($this->_columnCount);
    }
    /**
     * Return images collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Image_Collection
     */
    public function getImagesCollection()
    {
        return $this->_initImagesCollection();
    }

    /**
     * Initialize images collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Collection
     */
    protected function _initImagesCollection()
    {
        if (is_null($this->_imageCollection)) {
            $designId = $this->getDesignId();
            $collection = Mage::getModel('gmpd/design_image')->getCollection()
                ->getImageCollectionByDesign($designId);
            $this->_imageCollection = $collection;
        }

        return $this->_imageCollection;
    }

    /**
     * Return design id
     *
     * @return mixed
     */
    public function getDesignId()
    {
        return $this->getRequest()->getParam('design_id', false);
    }

    /**
     * Return image
     *
     * @param GoMage_ProductDesigner_Model_Design_Image $image Image
     * @param array                                     $size  Image Size
     * @return Varien_Image
     */
    public function getImage($image, $size)
    {
        return Mage::helper('designer/image')->init($image->getImage())->resize($size);
    }

    /**
     * Return download url
     *
     * @param int $imageId Image Id
     * @return string
     */
    public function getDownloadUrl($imageId)
    {
        return Mage::helper('adminhtml')->getUrl('*/designer_design/download', array('image_id' => $imageId));
    }
}

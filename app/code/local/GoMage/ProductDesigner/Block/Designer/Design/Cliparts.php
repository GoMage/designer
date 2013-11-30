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
 * GoMage ProductDesigner design clipart block
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
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
     * Return cliaprt image url
     *
     * @param string $image Image
     * @return string
     */
    public function getClipartUrl($image)
    {
        $url = Mage::helper('gomage_designer/image_cliparts')->init($image)->resize(64, 64)->__toString();
        return $url;
    }

    public function getOriginClipartUrl($image)
    {
        return rawurlencode(Mage::getSingleton('gomage_designer/clipart_gallery_config')->getMediaUrl($image));
    }
}

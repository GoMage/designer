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
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Block
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Block_Adminhtml_Sales_Items_Column_Name
    extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    public function getOrderDesignOption()
    {
        $options = $this->getItem()->getProductOptions();
        if (!isset($options['info_buyRequest'])) {
            return false;
        }
        $options = $options['info_buyRequest'];
        if (isset($options['design'])) {
            $designId = (int) $options['design'];
            $design = Mage::getModel('gomage_designer/design')->load($designId);
            if ($design && $design->getId()) {
                $option = array(
                    'price' => $design->getPrice(),
                    'design_id' => $design->getId(),
                    'url' => Mage::helper('adminhtml')->getUrl('*/designer_design/view',
                        array('design_id' => $design->getId()))
                );

                return new Varien_Object($option);
            }
        }

        return false;
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $_remainder = '';
        $value = Mage::helper('core/string')->truncate($value, 55, '', $_remainder);
        $result = array(
            'value' => nl2br($value),
            'remainder' => nl2br($_remainder)
        );

        return $result;
    }
}

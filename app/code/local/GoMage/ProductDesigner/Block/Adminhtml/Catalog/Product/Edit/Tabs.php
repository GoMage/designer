<?php

class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Edit_Tabs
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    /**
     * Prepare layout
     * Add tabs
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $moduleEnabled = Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());

        if($moduleEnabled) {
            $product = Mage::registry('product');
            $type = $product->getTypeId();
            if ($product->getId() !== null && $product->getEnableProductDesigner()
                && in_array($type, array('simple', 'configurable'))) {
                $this->addTab('product_designer', array(
                    'label'   => Mage::helper('designer')->__('Product Designer'),
                    'content' => $this->_translateHtml($this->getLayout()
                        ->createBlock('designer/adminhtml_catalog_product_edit_tabs_productDesigner')->toHtml()),
                ));
            }
        }

      return parent::_prepareLayout();
    }
}
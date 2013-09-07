<?php

class GoMage_ProductDesigner_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        $moduleEnabled = Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());

        if($moduleEnabled) {
            $product = Mage::registry('product');
            $type = $product->getTypeId();
            if ($product->getId() !== null
                && $product->getEnableProductDesigner() == 1
                && in_array($type, array('simple', 'configurable'))) {
                //add new tab

                $this->addTab('product_designer', array(
                    'label'   => Mage::helper('designer')->__('Product Designer'),
                    'content' => $this->getLayout()->createBlock('designer/adminhtml_tabs_productDesigner')->toHtml(),
                ));
            }
        }

      return parent::_prepareLayout();
    }
}
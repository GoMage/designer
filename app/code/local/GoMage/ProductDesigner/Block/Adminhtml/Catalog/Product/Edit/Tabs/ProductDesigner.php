<?php

class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Edit_Tabs_ProductDesigner
    extends Mage_Adminhtml_Block_Widget
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/catalog/product/edit/tabs/product_designer.phtml');
    }
}
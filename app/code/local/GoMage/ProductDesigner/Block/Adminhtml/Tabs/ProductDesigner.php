<?php

class GoMage_ProductDesigner_Block_Adminhtml_Tabs_ProductDesigner extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/productdesigner/settings_tab.phtml');
    }
}
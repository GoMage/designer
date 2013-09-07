<?php

class GoMage_ProductDesigner_Block_Adminhtml_Fonts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_controller  = 'fonts';
        $this->_mode        = 'edit';
        $this->_blockGroup = null;

        parent::__construct();
        $this->setTemplate('gomage/productdesigner/fonts/edit.phtml');
    }
}
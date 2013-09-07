<?php

class GoMage_ProductDesigner_Block_Adminhtml_Cliparts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_controller  = 'cliparts';
        $this->_mode        = 'edit';
        $this->_blockGroup = null;

        parent::__construct();
        $this->setTemplate('gomage/productdesigner/cliparts/edit.phtml');
    }
}
<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.2.0
 * @since        Available since Release 1.0.0
 */

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

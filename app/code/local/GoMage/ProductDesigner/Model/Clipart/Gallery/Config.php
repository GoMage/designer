<?php
class GoMage_ProductDesigner_Model_Clipart_Gallery_Config extends GoMage_ProductDesigner_Model_Config_Abstract {
    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'gomage' . DS . 'productdesigner' . DS . 'cliparts';
    }

    /**
     * Web-based directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'gomage/productdesigner/cliparts';
    }
}
<?php
class GoMage_ProductDesigner_Model_Design_Config extends GoMage_ProductDesigner_Model_Config_Abstract
{
    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'gomage' . DS . 'productdesigner' . DS . 'designs' . DS . 'catalog' . DS . 'product';
    }

    /**
     * Web-based directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'gomage/productdesigner/designs/catalog/product';
    }
}
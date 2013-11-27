<?php
/**
 * GoMage.com
 *
 * GoMage ProductDesigner Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
class GoMage_ProductDesigner_Block_Designer_Navigation extends Mage_Core_Block_Template
{
    /**
     * Return Filters html
     *
     * @return string
     */
    public function getFiltersHtml()
    {
        $filters = $this->getChild('filters');
        return $filters->toHtml();
    }

    /**
     * Return product list html
     *
     * @return string
     */
    public function getProductListHtml()
    {
        $productList = $this->getChild('productNavigatorList');
        return $productList->toHtml();
    }
}
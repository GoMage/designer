<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2017 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.5.0
 * @since        Available since Release 1.0.0
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

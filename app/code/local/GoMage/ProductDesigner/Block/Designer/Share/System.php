<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.3.0
 * @since        Available since Release 2.0.0
 */
class GoMage_ProductDesigner_Block_Designer_Share_System extends Mage_Core_Block_Template
{

    /** @var  GoMage_ProductDesigner_Model_Design */
    protected $design;

    /**
     * @return GoMage_ProductDesigner_Model_Design
     */
    public function getDesign()
    {
        return $this->design;
    }

    /**
     * @param  GoMage_ProductDesigner_Model_Design $design
     * @return GoMage_ProductDesigner_Block_Designer_Share_System
     */
    public function setDesign($design)
    {
        $this->design = $design;
        return $this;
    }

    public function getShareUrl()
    {
        $design  = $this->getDesign();
        $product = $design->getDesignProduct();

        return $product->getDesignedProductUrl($design->getDesignId());
    }

}
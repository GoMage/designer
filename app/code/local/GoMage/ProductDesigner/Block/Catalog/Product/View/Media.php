<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.1.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Block_Catalog_Product_View_Media
    extends Mage_Catalog_Block_Product_View_Media
{
    /**
     * Retrieve gallery url
     *
     * @param null|Varien_Object $image
     * @return string
     */
    public function getGalleryUrl($image = null)
    {
        $params = array('id' => $this->getProduct()->getId());
        if ($image) {
            $params['image'] = $image->getValueId();
        }
        if ($designId = $image->getDesignId()) {
            $params['design_id'] = $designId;
        }

        return $this->getUrl('catalog/product/gallery', $params);
    }


}

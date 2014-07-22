<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 1.0.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Block_Sales_Order_Email_Items_Default
    extends Mage_Sales_Block_Order_Email_Items_Order_Default
{
    /**
     * Return item design option value
     *
     * @return bool|int
     */
    public function getDesignOption()
    {
        $options = $this->getItem()->getProductOptions();
        if (!isset($options['info_buyRequest'])) {
            return false;
        }
        $options = $options['info_buyRequest'];
        if (isset($options['design'])) {
            return (int) $options['design'];
        }

        return false;
    }
}

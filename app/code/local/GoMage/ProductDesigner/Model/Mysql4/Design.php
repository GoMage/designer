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

class GoMage_ProductDesigner_Model_Mysql4_Design extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('gomage_designer/design', 'design_id');
    }
}


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

class GoMage_ProductDesigner_Model_Config_Source_Format
{
    public function toOptionArray()
    {
      return array(
        array('value' => 'pdf', 'label' => 'PDF'),
        array('value' => 'png', 'label' => 'PNG'),
        array('value' => 'jpg', 'label' => 'JPEG, JPG')
      );
    }
}

<?php

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

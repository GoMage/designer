<?php

class GoMage_ProductDesigner_Model_Config_Source_Format
{
    public function toOptionArray()
    {
      return array(
        array('value' => 'PDF', 'label' => 'PDF'),
        array('value' => 'PNG', 'label' => 'PNG'),
        array('value' => 'JPG', 'label' => 'JPEG, JPG'),
        array('value' => 'GIF', 'label' => 'GIF')
      );
    }
}

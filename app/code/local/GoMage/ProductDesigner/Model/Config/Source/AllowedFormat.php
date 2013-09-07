<?php

class GoMage_ProductDesigner_Model_Config_Source_AllowedFormat
{
    public function toOptionArray()
    {
      return array(
        array('value' => 'PNG', 'label' => 'PNG'),
        array('value' => 'JPG', 'label' => 'JPEG, JPG'),
        array('value' => 'GIF', 'label' => 'GIF')
      );
    }
}
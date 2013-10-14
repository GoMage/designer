<?php

class GoMage_ProductDesigner_Model_Config_Source_AllowedFormat
{
    public function toOptionArray()
    {
      return array(
        array('value' => 'png', 'label' => 'PNG'),
        array('value' => 'jpg', 'label' => 'JPEG, JPG'),
        array('value' => 'gif', 'label' => 'GIF')
      );
    }
}
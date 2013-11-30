<?php

class GoMage_ProductDesigner_Model_Config_Source_Font
{
    public function toOptionArray()
    {
      return array(
        array('value' => 'Arial', 'label' => 'Arial'),
        array('value' => 'Times New Roman', 'label' => 'Times New Roman'),
        array('value' => 'Comic Sans MS', 'label' => 'Comic Sans'),
        array('value' => 'Impact', 'label' => 'Impact')
      );
    }
}

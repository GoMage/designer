<?php

class GoMage_ProductDesigner_Model_Config_Source_Font_Size
{
    public function toOptionArray()
    {
        $sizes = array();
        $step = 2;
        for ($size = 12; $size <= 72; $size += $step) {
            $sizes[] = array('value' => $size, 'label' => $size);
            if ($size == 24 || $size == 52) {
                $step *= 2;
            } elseif ($size == 60) {
                $step = 12;
            }
        }
      return $sizes;
    }
}

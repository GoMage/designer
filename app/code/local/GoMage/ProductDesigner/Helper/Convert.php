<?php
class GoMage_ProductDesigner_Helper_Convert extends Mage_Catalog_Helper_Product_Url {
    public function format($string) {
        $formatResult = parent::format($string);
        return preg_replace('#[\s]+#i', '-', $formatResult);
    }
}
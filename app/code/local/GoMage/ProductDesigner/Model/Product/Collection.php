<?php
class GoMage_ProductDesigner_Model_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {
    public function getPreparedCollection() {
        return $this;
    }
}
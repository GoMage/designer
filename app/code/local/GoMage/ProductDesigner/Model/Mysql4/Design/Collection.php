<?php
class GoMage_ProductDesigner_Model_Mysql4_Design_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gmpd/design');
        $this->addFilterToMap('design_id', 'main_table.design_id');
    }

    /**
     * Filter collection by customer
     *
     * @param int $customerId Customer Id
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Collection
     */
    public function getCustomerDesignCollection($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Add products to collection
     *
     * @return GoMage_ProductDesigner_Model_Mysql4_Design_Collection
     */
    public function addProductsToCollection()
    {
        foreach ($this as $_item) {
            $productIds[$_item->getProductId()] = $_item->getProductId();
        }

        $productCollection = $this->_getProductCollection($productIds);

        foreach ($this as $_item) {
            $product = $productCollection->getItemById($_item->getProductId());
            $_item->setProduct($product);
        }

        return $this;
    }

    /**
     * Return product collection
     *
     * @param int|array $productIds Product Ids
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = (array) $productIds;
        }
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addIdFilter($productIds)
            ->addUrlRewrite();
        return $collection;
    }
}
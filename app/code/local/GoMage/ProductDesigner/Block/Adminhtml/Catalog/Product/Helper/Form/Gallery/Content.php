<?php

class GoMage_ProductDesigner_Block_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    const MODULE_ROOT_DIR = '/app/code/local/GoMage/ProductDesigner/';

    const PROD_IMAGES_GALLERY_TPL = 'blocks/Adminhtml/catalog/product/helper/gallery.phtml';

    /**
     * @see http://inchoo.net/ecommerce/magento/how-to-override-magento-admin-view-template-files-quick-and-dirty-way/
     * @see app/code/core/Mage/Core/Block/Mage_Core_Block_Template#fetchView($fileName)
     */
    public function fetchView($fileName)
    {
        $product = Mage::registry("product");

        if (!in_array($product->getTypeId(), array('simple', 'configurable'))) {
          return parent::fetchView($fileName);
        }

        extract ($this->_viewVars);

        $do = $this->getDirectOutput();

        if (!$do) {
          ob_start();
        }

        include getcwd() . self::MODULE_ROOT_DIR . self::PROD_IMAGES_GALLERY_TPL;

        if (!$do) {
          $html = ob_get_clean();
        } else {
          $html = '';
        }

        return $html;
    }
}
<?php
class GoMage_ProductDesigner_Block_Adminhtml_Cliparts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected $defaultCategory;

    protected function _prepareLayout()
    {
        $categoryId = $this->getId();
        // Save button
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save Cliparts'),
                    'onclick'   => "categorySubmit('" . $this->getSaveUrl() . "', true)",
                    'class' => 'save'
                ))
        );

        // Delete button
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete Category'),
                    'onclick'   => "categoryDelete('" . $this->getUrl('*/*/delete', array('_current' => true)) . "', true, {$categoryId})",
                    'class' => 'delete'
                ))
        );

        // Reset button
        $resetPath = $categoryId ? '*/*/edit' : '*/*/add';
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Reset'),
                    'onclick'   => "categoryReset('".$this->getUrl($resetPath, array('_current'=>true))."',true)"
                ))
        );


        return parent::_prepareLayout();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getHeader()
    {
        return Mage::helper('catalog')->__('New Root Category');
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }

    public function getDefaultCategoryId() {
        /* @var $clipartsCategorySingleton GoMage_ProductDesigner_Model_Clipart_Category */
        $clipartsCategorySingleton = Mage::getSingleton('gmpd/clipart_category');
        return $clipartsCategorySingleton->getDefaultCategoryId();
    }

    public function getParentCategoryId() {
        /* @var $currentCategory GoMage_ProductDesigner_Model_Clipart_Category */

        $currentCategory = $this->getCategory();
        if($currentCategory) {
            $parentCategoryId = $this->getCategory()->getParentId();
        } else {
            $parentId = (int)Mage::app()->getRequest()->getParam('parent');
            if($parentId > 0) {
                $parentCategoryId = $parentId;
            } else {
                $parentCategoryId = $this->getDefaultCategoryId();
            }
        }
        return $parentCategoryId;
    }

    public function getParentCategory() {
        $parentCategoryId = $this->getParentCategoryId();
        if($parentCategoryId) {
            /* @var $clipartsCategorySingleton GoMage_ProductDesigner_Model_Clipart_Category */
            $clipartsCategorySingleton = Mage::getSingleton('gmpd/clipart_category');
            return $clipartsCategorySingleton->getCategoryById($parentCategoryId);
        }
    }

    public function getParentCategoryPath() {
        $parentCategory = $this->getParentCategory();
        if($parentCategory && $parentCategory->getPath()) {
            return $parentCategory->getPath();
        }
    }

    public function getCategory() {
        /* @var $category GoMage_ProductDesigner_Model_Clipart_Category */
        if($this->hasData('category')) {
            $category = $this->getData('category');
            return $category;
        }
        $category = Mage::registry('category');
        if($category){
            $this->setData('category', $category);
            return $category;
        }
    }

    public function getName() {
        /* @var $category GoMage_ProductDesigner_Model_Clipart_Category */
        $category = $this->getCategory();
        if($category) {
            return $category->getName();
        }
    }

    public function getIsActive() {
        /* @var $category GoMage_ProductDesigner_Model_Clipart_Category */
        $category = $this->getCategory();
        if($category) {
            return $category->getIsActive();
        }
    }

    public function getId() {
        /* @var $category GoMage_ProductDesigner_Model_Clipart_Category */
        $category = $this->getCategory();
        if($category) {
            return $category->getId();
        }
    }
}
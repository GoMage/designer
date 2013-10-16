<?php
class GoMage_ProductDesigner_Block_Adminhtml_Fonts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected $defaultCategory;

    protected function _prepareLayout()
    {
        $categoryId = $this->getId();
        // Save button
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save Fonts'),
                    'onclick'   => "categorySubmit('" . $this->getSaveUrl() . "', true)",
                    'class' => 'save'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getHeader()
    {
        return Mage::helper('catalog')->__('Fonts');
    }

    public function getSaveUrl(array $args = array())
    {
        $params = array('_current'=>true);
        $params = array_merge($params, $args);
        return $this->getUrl('*/*/save', $params);
    }
}
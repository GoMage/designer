<?php
class GoMage_ProductDesigner_Adminhtml_Designer_DesignController
    extends Mage_Adminhtml_Controller_Action
{
    public function dispatch($action)
    {
        if(!Mage::helper('designer')->isEnabled()) {
            $action = 'noRoute';
        }
        parent::dispatch($action);
    }

    public function viewAction()
    {
        $this->_title($this->__('View Design'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function downloadAction()
    {
        $imageId = (int) $this->getRequest()->getParam('image_id');

        if ($imageId) {
            $image = Mage::getModel('gmpd/design_image')->load($imageId);
            if ($image) {
                $imageName = ltrim($image->getImage(), '/');
                $imageFile = file_get_contents(Mage::getModel('gmpd/design_config')->getMediaPath($image->getImage()));
                $this->_prepareDownloadResponse($imageName, $imageFile);
                return;
            }
        }

        $this->_redirectReferer();
    }
}
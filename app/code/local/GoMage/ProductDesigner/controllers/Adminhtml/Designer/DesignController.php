<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.4.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Adminhtml_Designer_DesignController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    public function dispatch($action)
    {
        if (!Mage::helper('gomage_designer')->isEnabled()) {
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
        $imageId = (int)$this->getRequest()->getParam('image_id');
        $type    = $this->getRequest()->getParam('type');

        if ($imageId) {
            $image = Mage::getModel('gomage_designer/design_image')->load($imageId);
            if ($image) {
                $imageGetter = 'get' . $type;
                $imageName   = ltrim($image->$imageGetter(), '/');
                $imageFile   = file_get_contents(Mage::getModel('gomage_designer/design_config')->getMediaPath($image->$imageGetter()));
                $this->_prepareDownloadResponse($imageName, $imageFile);
                return;
            }
        }

        $this->_redirectReferer();
    }

    public function downloadAllAction()
    {
        if (!extension_loaded('zip')) {
            $this->_redirectReferer();
        }

        $designId = (int)Mage::app()->getRequest()->getParam('design_id');

        if ($designId) {
            $images          = Mage::getModel('gomage_designer/design')->getImages($designId);
            $archiveFileName = Mage::getBaseDir('tmp') . DS . 'design_images.zip';
            if (file_exists($archiveFileName)) {
                unlink($archiveFileName);
            }
            $archive = new ZipArchive();
            $archive->open($archiveFileName, ZipArchive::CREATE);
            foreach ($images as $_image) {
                $archive->addFile(
                    Mage::getModel('gomage_designer/design_config')->getMediaPath($_image->getImage()),
                    ltrim($_image->getImage(), '/')
                );
                if ($_image->getLayer()) {
                    $archive->addFile(
                        Mage::getModel('gomage_designer/design_config')->getMediaPath($_image->getLayer()),
                        ltrim($_image->getLayer(), '/')
                    );
                }
            }
            $archive->close();
            $archiveFile = file_get_contents($archiveFileName);
            $this->_prepareDownloadResponse('design_images.zip', $archiveFile);
            return;
        }

        $this->_redirectReferer();
    }
}

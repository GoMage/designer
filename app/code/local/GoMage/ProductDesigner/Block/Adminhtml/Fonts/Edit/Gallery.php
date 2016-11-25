<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 2.3.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Block_Adminhtml_Fonts_Edit_Gallery
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('gomage/productdesigner/fonts/edit/gallery.phtml');
    }

    protected function _prepareLayout()
    {
        $preparedLayout = parent::_prepareLayout();

        if (Mage::helper('gomage_designer')->isModuleExists('Mage_Uploader')) {
            $this->setChild('uploader',
                $this->getLayout()->createBlock($this->_uploaderType)
            );

            $this->getUploader()->getUploaderConfig()
                ->setFileParameterName('image')
                ->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/uploadImage', array('_current' => true)));

            $browseConfig = $this->getUploader()->getButtonConfig();
            $browseConfig
                ->setAttributes(array(
                    'accept' => $browseConfig->getMimeTypesByExtensions('ttf, otf, woff, eot')
                )
                );
        } else {
            $this->setChild('uploader',
                $this->getLayout()->createBlock('adminhtml/media_uploader')
            );

            $this->getUploader()->getConfig()
                ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/uploadImage', array('_current' => true)))
                ->setFileField('image')
                ->setFilters(array(
                        'images' => array(
                            'label' => Mage::helper('adminhtml')->__('Fonts (.ttf, .otf, .woff, .eot)'),
                            'files' => array('*.ttf', '*.otf', '*.woff', '*.eot')
                        )
                    )
                );
        }

        Mage::dispatchEvent('cliparts_gallery_prepare_layout', array('block' => $this));
        return $preparedLayout;
    }

    public function getHtmlId()
    {
        return 'media_gallery_content';
    }

    public function getImagesJson()
    {
        $values        = array();
        $galleryConfig = Mage::getSingleton('gomage_designer/font_gallery_config');
        $mediaUrl      = $galleryConfig->getBaseMediaUrl();

        foreach ($this->getFontsCollection() as $font) {
            $fontData = $font->getData();
            $fontUrl  = $fontData['font'];
            $valueId  = $fontData['font_id'];

            unset($fontData['font']);
            unset($fontData['font_id']);

            $additionalData['value_id'] = $valueId;
            $additionalData['url']      = $mediaUrl . $fontUrl;
            $additionalData['file']     = $fontUrl;
            $additionalData['removed']  = 0;
            $data                       = array_merge($additionalData, $fontData);
            $values[]                   = $data;
        }
        return Mage::helper('core')->jsonEncode($values);
    }

    public function getImagesValuesJson()
    {
        $values = array();
        return Mage::helper('core')->jsonEncode($values);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getImageTypes()
    {
        return array();
    }

    public function hasUseDefault()
    {
        return false;
    }

    public function getImageTypesJson()
    {
        return json_encode($this->getImageTypes(), JSON_FORCE_OBJECT);
    }

    public function getFontsCollection()
    {
        return Mage::getResourceModel('gomage_designer/font_collection');
    }
}

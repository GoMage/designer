<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2017 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.5.0
 * @since        Available since Release 1.0.0
 */
class GoMage_ProductDesigner_Model_Design_Image extends Mage_Core_Model_Abstract
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gomage_designer/design_image');
    }

    /**
     * Save design image
     *
     * @param array $image Image data
     * @param int $imageId Original image Id
     * @param Mage_Catalog_Model_Product $product Product
     * @param int $designId Design Id
     * @return void
     */
    public function saveImage($image, $imageId, $product, $designId)
    {
        $helper        = Mage::helper('gomage_designer');
        $imageSettings = $helper->getImageSettings($product, $imageId);
        if ($imageSettings) {
            $canvas = $this->createCanvas($imageSettings['original_image']['path']);
            $layer  = $this->createLayer($image);
            if ($canvas && $layer) {
                $canvas = $this->addLayerToCanvas($canvas, $layer, $imageSettings);

                $canvas_file = $this->_prepareFileForSave();
                $layer_file  = $this->_prepareFileForSave();

                $layer->writeImage($layer_file);
                $canvas->writeImage($canvas_file);

                if ($this->_getImageExtensionForSave() == 'pdf') {
                    $canvas->setImageFormat('jpg');
                    $canvas->writeImage(str_replace('.pdf', '.jpg', $canvas_file));
                    $layer->setImageFormat('jpg');
                    $layer->writeImage(str_replace('.pdf', '.jpg', $layer_file));
                }
                $layer->destroy();
                $canvas->destroy();

                $designConfig = Mage::getSingleton('gomage_designer/design_config');
                $configPath   = $designConfig->getBaseMediaPath();

                $this->addData(array(
                        'product_id'   => $product->getId(),
                        'design_id'    => $designId,
                        'image'        => str_replace($configPath, '', $canvas_file),
                        'layer'        => str_replace($configPath, '', $layer_file),
                        'image_id'     => $imageId,
                        'created_date' => Mage::getModel('core/date')->gmtDate(),
                    )
                )->save();

            }
        }
    }

    /**
     * Add Layer to canvas
     *
     * @param resource|Imagick $canvas Canvas
     * @param resource|Imagick $layer Layer
     * @param array $imageSettings Image Settings
     * @return Imagick|resource
     */
    public function addLayerToCanvas($canvas, $layer, $imageSettings)
    {
        $scale_width  = $canvas->getImageWidth() / $imageSettings['dimensions']['width'];
        $scale_height = $canvas->getimageheight() / $imageSettings['dimensions']['height'];
        $offset_x     = floor($imageSettings['l'] * $scale_width);
        $offset_y     = floor($imageSettings['t'] * $scale_height);

        $resized_layer = clone $layer;
        $resized_layer->adaptiveResizeImage(
            floor($imageSettings['w'] * $scale_width),
            floor($imageSettings['h'] * $scale_height)
        );

        $canvas->compositeImage($resized_layer, $resized_layer->getImageCompose(), $offset_x, $offset_y);
        $canvas->flattenImages();

        $resized_layer->destroy();

        return $canvas;
    }

    /**
     * Create layer from image
     *
     * @param string $image Image
     * @return Imagick
     */
    public function createLayer($image)
    {
        $layer = new Imagick();
        $layer->setBackgroundColor(new ImagickPixel('transparent'));
        $layer->readImageBlob($image);
        $layer->setImageFormat($this->_getImageExtensionForSave());
        return $layer;
    }

    /**
     * Create canvas
     *
     * @param string $image Image Path
     * @return Imagick
     */
    public function createCanvas($image)
    {
        $canvas = new Imagick();
        $canvas->readimage($image);
        $canvas->setImageFormat($this->_getImageExtensionForSave());
        return $canvas;
    }

    /**
     * Prepare file for save
     *
     * @return string
     */
    protected function _prepareFileForSave()
    {
        $pathToSave     = $this->_preparePathToSave();
        $imageExtension = $this->_getImageExtensionForSave();
        $fileName       = Mage::helper('core')->getRandomString(16) . '.' . $imageExtension;
        return $pathToSave . DS . $fileName;
    }

    /**
     * Prepare path for save
     *
     * @return string
     */
    protected function _preparePathToSave()
    {
        $pathToSave = Mage::getSingleton('gomage_designer/design_config')->getBaseMediaPath();
        $this->mkDirIfNotExists($pathToSave);
        return $pathToSave;
    }

    /**
     * Create directory if not exist
     *
     * @param string $directory Directory path
     * @return void
     */
    public function mkDirIfNotExists($directory)
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * @return string
     */
    protected function _getImageExtensionForSave()
    {
        return Mage::getStoreConfig('gomage_designer/general/format');
    }

    /**
     * @param  string $filename
     * @return $this
     */
    public function renameImage($filename)
    {
        $extension = strtolower(pathinfo($this->getImage(), PATHINFO_EXTENSION));
        $filename  = '/' . $filename . '.' . $extension;

        Mage::helper('gomage_designer/image_design')
            ->init($this->getImage())
            ->rename($filename);

        $this->setImage($filename);

        return $this;
    }

    public function renameLayer($filename)
    {
        $extension = strtolower(pathinfo($this->getLayer(), PATHINFO_EXTENSION));
        $filename  = '/' . $filename . '.' . $extension;

        Mage::helper('gomage_designer/image_design')
            ->init($this->getLayer())
            ->rename($filename);

        $this->setLayer($filename);

        return $this;
    }

}

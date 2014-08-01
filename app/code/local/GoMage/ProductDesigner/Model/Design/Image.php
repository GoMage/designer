<?php

/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use/
 * @version      Release: 1.0.0
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
        $dataHelper    = Mage::helper('gomage_designer');
        $imageSettings = $dataHelper->getImageSettings($product, $imageId);
        if ($imageSettings) {
            $dimensions = $imageSettings['original_image']['dimensions'];
            $canvas     = $this->createCanvas(
                $imageSettings['original_image']['path'], $dimensions[0], $dimensions[1]
            );
            if ($canvas) {
                $layer = $this->createLayer($image);
                if ($layer) {
                    $canvas = $this->addLayerToCanvas($canvas, $layer, $imageSettings);
                    $this->saveCanvas($canvas, $layer, $imageId, $designId);
                }
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
        $designAreaLeft  = $imageSettings['l'] - $imageSettings['w'] / 2;
        $designAreaTop   = $imageSettings['t'] - $imageSettings['h'] / 2;
        $frameWidth      = $dstWidth = $imageSettings['dimensions']['width'];
        $frameHeight     = $dstHeight = $imageSettings['dimensions']['height'];
        $origImageWidth  = $imageSettings['original_image']['dimensions'][0];
        $origImageHeight = $imageSettings['original_image']['dimensions'][1];
        if ($origImageWidth / $origImageHeight >= $frameWidth / $frameHeight) {
            $dstHeight = floor($frameWidth / $origImageWidth * $origImageHeight);
            $scale     = $origImageWidth / $frameWidth;
        } else {
            $dstWidth = floor($frameHeight / $origImageHeight * $origImageWidth);
            $scale    = $origImageHeight / $frameHeight;
        }
        $widthScale     = $origImageWidth / $dstWidth;
        $heightScale    = $origImageHeight / $dstHeight;
        $designAreaLeft = floor(($designAreaLeft * $scale) - ($frameWidth - $dstWidth));
        $designAreaTop  = floor(($designAreaTop * $scale) - ($frameHeight - $dstHeight));

        $layer->resizeImage(
            floor($imageSettings['w'] * $widthScale),
            floor($imageSettings['h'] * $heightScale),
            Imagick::FILTER_LANCZOS,
            1
        );
        $canvas->compositeImage($layer, $layer->getImageCompose(), $designAreaLeft, $designAreaTop);

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
        $layer->readImageBlob($image);
        return $layer;
    }

    /**
     * @param Imagick $layer Layer
     * @param string $filename Filename
     */
    public function saveLayer($layer, $filename)
    {
        $layer->setImageFormat($this->_getImageExtensionForSave());
        $layer->writeImage($filename);
    }

    /**
     * Create canvas
     *
     * @param string $image Image Path
     * @param float $width Width
     * @param float $height Height
     * @return Imagick|boolean
     */
    public function createCanvas($image, $width, $height)
    {
        $width  = (int)$width;
        $height = (int)$height;
        if ($width && $height) {
            $canvas = new Imagick();
            $canvas->setsize($width, $height);
            $canvas->readimage($image);
            $canvas->setImageFormat($this->_getImageExtensionForSave());
            return $canvas;
        }
        return false;
    }

    /**
     * Save file to DB
     *
     * @param Imagick $canvas canvas
     * @param $layer
     * @param int $imageId Image Id
     * @param int $designId Design Id
     * @return void
     */
    public function saveCanvas($canvas, $layer, $imageId, $designId)
    {
        $currentProduct = Mage::registry('product');
        $designConfig   = Mage::getSingleton('gomage_designer/design_config');
        $configPath     = $designConfig->getBaseMediaPath();

        if ($currentProduct && $currentProduct->getId()) {
            $fileToSave    = $this->_prepareFileForSave();
            $layerFilename = $this->_prepareFileForSave();

            $this->saveLayer($layer, $layerFilename);
            $canvas->writeImage($fileToSave);
            if ($this->_getImageExtensionForSave() == 'pdf') {
                $fileToSaveJpg    = str_replace('.pdf', '.jpg', $fileToSave);
                $layerFilenameJpg = str_replace('.pdf', '.jpg', $layerFilename);
                $canvas->setImageFormat('jpg');
                $canvas->writeImage($fileToSaveJpg);
                $layer->setImageFormat('jpg');
                $this->saveLayer($layer, $layerFilenameJpg);
            }
            $layer->destroy();
            $canvas->destroy();

            $this->addData(array(
                    'product_id'   => $currentProduct->getId(),
                    'design_id'    => $designId,
                    'image'        => str_replace($configPath, '', $fileToSave),
                    'layer'        => str_replace($configPath, '', $layerFilename),
                    'image_id'     => $imageId,
                    'created_date' => Mage::getModel('core/date')->gmtDate(),
                )
            )->save();
        }
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

    protected function _getImageExtensionForSave()
    {
        return Mage::getStoreConfig('gomage_designer/general/format');
    }

}

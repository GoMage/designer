<?php
/**
 * GoMage.com extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the GoMage ProductDesigner module to newer versions in the future.
 * If you wish to customize the GoMage ProductDesigner module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @copyright  Copyright (C) 2013 GoMage.com (http://www.gomage.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design image model
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Model
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Model_Design_Image extends Mage_Core_Model_Abstract
{
    protected $_imageExtension = 'imagick';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gmpd/design_image');
    }

    /**
     * Save design image
     *
     * @param array                      $image    Image data
     * @param int                        $imageId  Original image Id
     * @param Mage_Catalog_Model_Product $product  Product
     * @param int                        $designId Design Id
     * @return void
     */
    public function saveImage($image, $imageId, $product, $designId)
    {
        $dataHelper = Mage::helper('designer');
        $imageSettings = $dataHelper->getImageSettings($product, $imageId);
        if ($imageSettings) {
            $dimensions = $imageSettings['original_image']['dimensions'];
            $canvas = $this->createCanvas(
                $imageSettings['original_image']['path'], $dimensions[0], $dimensions[1]
            );
            if ($canvas) {
                $layer = $this->createLayer($image);
                if ($layer) {
                    $canvas = $this->addLayerToCanvas($canvas, $layer, $imageSettings);
                    $this->saveCanvas($canvas, $imageId, $designId);
                }
            }
        }
    }

    /**
     * Add Layer to canvas
     *
     * @param resource|Imagick $canvas        Canvas
     * @param resource|Imagick $layer         Layer
     * @param array            $imageSettings Image Settings
     * @return Imagick|resource
     */
    public function addLayerToCanvas($canvas, $layer, $imageSettings)
    {
        $designAreaLeft = $imageSettings['l'] - $imageSettings['w']/2;
        $designAreaTop = $imageSettings['t'] - $imageSettings['h']/2;
        $frameWidth = $dstWidth = $imageSettings['dimensions']['width'];
        $frameHeight = $dstHeight = $imageSettings['dimensions']['height'];
        $origImageWidth = $imageSettings['original_image']['dimensions'][0];
        $origImageHeight = $imageSettings['original_image']['dimensions'][1];
        if ($origImageWidth / $origImageHeight >= $frameWidth / $frameHeight) {
            $dstHeight = floor($frameWidth / $origImageWidth * $origImageHeight);
            $scale = $origImageWidth / $frameWidth;
        } else {
            $dstWidth = floor($frameHeight / $origImageHeight * $origImageWidth);
            $scale = $origImageHeight / $frameHeight;
        }
        $widthScale = $origImageWidth / $dstWidth;
        $heightScale = $origImageHeight / $dstHeight;
        $designAreaLeft = floor(($designAreaLeft * $scale) - ($frameWidth - $dstWidth));
        $designAreaTop = floor(($designAreaTop * $scale) - ($frameHeight - $dstHeight));
        if (extension_loaded($this->_imageExtension)) {
            $layer->resizeImage(
                floor($imageSettings['w'] * $widthScale),
                floor($imageSettings['h'] * $heightScale),
                Imagick::FILTER_LANCZOS,
                1
            );
            $canvas->compositeImage($layer, $layer->getImageCompose(), $designAreaLeft, $designAreaTop);
            $layer->destroy();
        } else {
            $layerWidth = floor(imagesx($layer) * $widthScale);
            $layerHeight = floor(imagesy($layer) * $heightScale);
            imagecopyresized(
                $canvas,
                $layer,
                $designAreaLeft,
                $designAreaTop,
                0,
                0,
                $layerWidth,
                $layerHeight,
                imagesx($layer),
                imagesy($layer)
            );
            imagedestroy($layer);
        }

        return $canvas;
    }

    /**
     * Create layer from image
     *
     * @param string $image Image
     * @return Imagick|resource
     */
    public function createLayer($image)
    {
        $image = base64_decode($image);
        if (extension_loaded($this->_imageExtension)) {
            $layer = new Imagick();
            $layer->readimageblob($image);
        } else {
            $layer = imagecreatefromstring($image);
        }

        return $layer;
    }
    /**
     * Create canvas
     *
     * @param string $image Image Path
     * @param float  $width       Width
     * @param float  $height      Height
     * @return Imagick|resource|boolean
     */
    public function createCanvas($image, $width, $height)
    {
        $width = (int) $width;
        $height = (int) $height;
        if ($width > 0 && $height > 0) {
            if (extension_loaded($this->_imageExtension)) {
                $canvas = new Imagick();
                $canvas->setsize($width, $height);
                $canvas->readimage($image);
                $resolution = $this->_getImageExtensionForSave() == 'pdf' ? 300 : 600;
                $canvas->setImageResolution($resolution, $resolution);
                $canvas->setImageFormat($this->_getImageExtensionForSave());
            } else {
                $imageExtension = $this->_prepareImageExtension($image);
                $imageCreateFunction = 'imagecreatefrom' . $imageExtension;
                if (function_exists($imageCreateFunction)) {
                    $canvas = imagecreatetruecolor($width, $height);
                    $srcImage = $imageCreateFunction($image);
                    imagecopyresampled($canvas,$srcImage, 0, 0, 0, 0, $width, $height, $width, $height);
                }
            }

            return $canvas;
        }

        return false;
    }

    /**
     * Save file to DB
     *
     * @param Imagick $canvas   canvas
     * @param int     $imageId  Image Id
     * @param int     $designId Design Id
     * @return void
     */
    public function saveCanvas($canvas, $imageId, $designId)
    {
        $currentProduct = Mage::registry('product');
        $designConfig = Mage::getSingleton('gmpd/design_config');
        $configPath = $designConfig->getBaseMediaPath();

        if($currentProduct && $currentProduct->getId()) {
            $fileToSave = $this->_prepareFileForSave();
            if (extension_loaded($this->_imageExtension)){
                $canvas->writeImage($fileToSave);
                $canvas->destroy();
            } else {
                $imageExtension = strtolower(pathinfo($fileToSave, PATHINFO_EXTENSION));
                if ($imageExtension == 'pdf') {
                    $fileToSave = str_replace($imageExtension, '', $fileToSave);
                    imagejpeg($canvas, $fileToSave . 'jpg', 100);
                    imagedestroy($canvas);
                    $pdf = new Zend_Pdf();
                    $image = Zend_Pdf_Image::imageWithPath($fileToSave . 'jpg');
                    $pdfPage = $pdf->newPage($image->getPixelWidth(). ':'. $image->getPixelHeight());
                    $pdfPage->drawImage($image, 0, 0, $image->getPixelWidth(), $image->getPixelHeight());
                    $pdf->pages[] = $pdfPage;
                    $pdf->save($fileToSave.$imageExtension);
                    unlink($fileToSave. 'jpg');
                } else {
                    if ($imageExtension = 'jpg') {
                        $saveFunction = 'imagejpeg';
                    } else {
                        $saveFunction = 'image' . $imageExtension;
                    }
                    if (function_exists($saveFunction)) {
                        $saveFunction($canvas, $fileToSave, 100);
                        $image = file_get_contents($fileToSave);
                        $image = substr_replace($image, pack("Cnn", 0x01, 300, 300), 13, 5);
                        file_put_contents($fileToSave, $image);
                        imagedestroy($canvas);
                    }
                }
            }

            $this->addData(array(
                'product_id' => $currentProduct->getId(),
                'design_id' => $designId,
                'image' => str_replace($configPath, '', $fileToSave),
                'image_id' => $imageId,
                'created_date' => Mage::getModel('core/date')->gmtDate(),
            ))->save();
        }
    }

    /**
     * Prepare file for save
     *
     * @return string
     */
    protected function _prepareFileForSave()
    {
        $pathToSave = $this->_preparePathToSave();
        $imageExtension = $this->_getImageExtensionForSave();

        $fileName = Mage::helper('core')->getRandomString(16) . '.' . $imageExtension;

        return $pathToSave . DS . $fileName;
    }

    /**
     * Prepare path for save
     *
     * @return string
     */
    protected function _preparePathToSave()
    {
        $pathToSave = Mage::getSingleton('gmpd/design_config')->getBaseMediaPath();
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
        if(!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Return image extension
     *
     * @param string $imagePath Image path
     * @return string
     */
    protected function _prepareImageExtension($imagePath)
    {
        $imageExtension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if(in_array($imageExtension, array('jpg', 'jpeg'))) {
            $imageExtension = 'jpeg';
        }
        return $imageExtension;
    }

    protected function _getImageExtensionForSave()
    {
        return Mage::getStoreConfig('gmpd/general/format');
    }
}

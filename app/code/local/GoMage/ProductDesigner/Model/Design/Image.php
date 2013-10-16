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
        } else {
            $dstWidth = floor($frameHeight / $origImageHeight * $origImageWidth);
        }
        $scaleX = $origImageWidth / $frameWidth;
        $scaleY = $origImageHeight / $frameHeight;
        $widthScale = $origImageWidth / $dstWidth;
        $heightScale = $origImageHeight / $dstHeight;
        $designAreaLeft = floor(($designAreaLeft * $scaleX) - ($frameWidth / 2 * ($frameWidth / $dstWidth - 1)));
        $designAreaTop = floor(($designAreaTop * $scaleY) - ($frameHeight / 2 * ($frameHeight / $dstHeight - 1)));

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
                $canvas->setImageResolution(600, 600);
                $canvas->setImageFormat('jpeg');
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
            $fileToSave = $this->_prepareFileForSave($canvas);
            if (extension_loaded($this->_imageExtension)){
                $canvas->writeImage($fileToSave);
                $canvas->destroy();
            } else {
                imagejpeg($canvas, $fileToSave, 100);
                imagedestroy($canvas);
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
     * @param Imagick $canvas Canvas
     * @return string
     */
    protected function _prepareFileForSave($canvas)
    {
        $pathToSave = $this->_preparePathToSave();
        if (extension_loaded($this->_imageExtension)) {
            $imageExtension = $canvas->getImageFormat();
        } else {
            $imageExtension = 'jpeg';
        }

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
        $imagePathExploded = explode('.', $imagePath);
        $imageExtension = array_pop($imagePathExploded);
        $imageExtension = strtolower($imageExtension);
        if(in_array($imageExtension, array('jpg', 'jpeg'))) {
            $imageExtension = 'jpeg';
        }
        return $imageExtension;
    }
}

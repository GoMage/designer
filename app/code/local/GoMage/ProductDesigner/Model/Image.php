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
 * Image model
 *
 * @category   GoMage
 * @package    GoMage_ProductDesigner
 * @subpackage Model
 * @author     Roman Bublik <rb@gomage.com>
 */
class GoMage_ProductDesigner_Model_Image extends Varien_Object
{
    /**
     * Return image helper
     *
     * @return GoMage_ProductDesigner_Helper_ProductImage
     */
    public function getImageHelper()
    {
        return Mage::helper('designer/productImage');
    }

    /**
     * Create designed product images
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @param array $designAreas Design areas
     * @return void
     */
    public function createProductImages($product, $designAreas)
    {
        $dataHelper = Mage::helper('designer');

        foreach ($designAreas as $designArea => $layers) {
            $designArea = explode('-', $designArea);
            $imageId = array_pop($designArea);
            $imageSettings = $dataHelper->getImageSettings($product, $imageId);
            if ($imageSettings) {
                $imageSettings['image_id'] = $imageId;
                $this->addLayersToCanvas($imageSettings, $layers);
            }
        }
    }

    /**
     * Create canvas
     *
     * @param string $image Image Path
     * @param float $width  Width
     * @param float $height Height
     * @return Imagick|boolean
     */
    public function createCanvas($image, $width, $height)
    {
        $width = (int) $width;
        $height = (int) $height;
        if ($width > 0 && $height > 0) {
            $canvas = new Imagick();
            $canvas->setsize($width, $height);
            $canvas->readimage($image);
            return $canvas;
        }

        return false;
    }

    /**
     * Add Layers to canvas
     *
     * @param array $imageSettings ImageSettings
     * @param array $layers Layers
     * @return void
     */
    public function addLayersToCanvas($imageSettings, $layers)
    {
        $imageHelper = $this->getImageHelper();
        if (count($layers) == 0) {
            return;
        }

        $dimensions = $imageSettings['dimensions'];
        $canvas = $this->createCanvas($imageSettings['path'], $dimensions['width'], $dimensions['height']);
        if ($canvas === false) {
            return;
        }

        foreach ($layers as $layer) {
            $layerData = $this->_prepareLayerData($layer, $imageSettings);
            if ($layerData !== false) {
                if ($layer['type'] == 'image') {
                    $this->addImageToCanvas($canvas, $layerData);
                } else {
                    $this->addTextToCanvas($canvas, $layerData);
                }
            }
        }
        $this->saveCanvas($canvas, $imageSettings['image_id']);
        $canvas->destroy();
    }

    /**
     * Prepare layer data
     *
     * @param array $layer Layer
     * @param array $imageSettings Image settings
     * @return array|bool
     */
    protected function _prepareLayerData($layer, $imageSettings)
    {
        $designAreaLeft = $imageSettings['l'] - $imageSettings['w']/2;
        $designAreaTop = $imageSettings['t'] - $imageSettings['h']/2;
        $imageLeft = $layer['left'] - ($layer['width'] / 2);
        $imageTop = $layer['top'] - ($layer['height'] / 2);
        $imageLeft += $designAreaLeft;
        $imageTop += $designAreaTop;

        if (($designAreaLeft > ($imageLeft + $layer['width'])) &&
            ($designAreaTop > ($imageTop + $layer['height']))) {
            return;
        }

        $layerData = array(
            'width' => $layer['width'],
            'height' => $layer['height'],
            'scaleX' => $layer['scaleX'],
            'scaleY' => $layer['scaleY'],
            'left' => $imageLeft,
            'top' => $imageTop,
            'corner' => isset($layer['corner']) ? $layer['corner'] : null,
            'flip' => $layer['flip'],
            'flop' => $layer['flop'],
        );

        if ($crop = $this->_isImageCrop($layerData, array('left' => $designAreaLeft, 'top' => $designAreaTop))) {
            $layerData['left'] = $crop->getX();
            $layerData['top'] = $crop->getY();
            $layerData['crop'] = $crop;
        }

        if ($layer['type'] == 'image') {
            $layerData['path'] = $this->getImageHelper()->getImagePathFromUrl($layer['imageSrc']);
        } elseif ($layer['type'] == 'text') {
            $layerData['text'] = $layer['text'];
            $layerData['font'] = $layer['fontFamily'];
            $layerData['fontSize'] = $layer['fontSize'];
            $layerData['color'] = $layer['color'];
            if ($layer['fontWeight'] == 'bold') {
                $layerData['fontWeight'] = 600;
            }
            if (isset($layer['fontStyle'])) {
                $layerData['fontStyle'] = Imagick::STYLE_ITALIC;
            }
            if (isset($layer['textDecoration'])) {
                $layerData['decoration'] = Imagick::DECORATION_UNDERLINE;
            }
            if (isset($layer['outline'])) {
                $outline = $layer['outline'];
                $layerData['outline'] = array(
                    'strokeWidth' => $outline['width'],
                    'color' => $outline['color']
                );
            }
            if (isset($layer['shadow'])) {
                $layerData['shadow'] = $layer['shadow'];
            }
        }
        return $layerData;
    }

    /**
     * Prepare layer data if image croped
     *
     * @param array $layer      Layer
     * @param array $designArea Design Area
     * @return bool|Varien_Object
     */
    protected function _isImageCrop($layer, $designArea)
    {
        if ($designArea['left'] > $layer['left'] || $designArea['top'] > $layer['top']) {
            $crop = new Varien_Object();
            if ($designArea['left'] > $layer['left']) {
                $crop->setData(array(
                    'width' => $layer['width'] - ($designArea['left'] - $layer['left']),
                    'x' => $designArea['left']
                ));
            } else {
                $crop->setData(array(
                    'width' => $layer['width'],
                    'x' => $layer['left']
                ));
            }

            if ($designArea['top'] > $layer['top']) {
                $crop->addData(array(
                    'height' => $layer['height'] - ($designArea['top'] - $layer['top']),
                    'y' => $designArea['top']
                ));
            } else {
                $crop->addData(array(
                    'height' => $layer['height'],
                    'y' => $layer['top']
                ));
            }

            return $crop;
        }

        return false;
    }

    /**
     * Add Image to canvas
     *
     * @param Imagick      $canvas Canvas
     * @param array        $layer  Layer
     * @param Imagick|bool $image  Image
     * @return void
     */
    public function addImageToCanvas($canvas, $layer, $image = false)
    {
        if ($image === false) {
            $image = new Imagick($layer['path']);
        }
        $image->scaleImage($layer['width'], $layer['height']);
        if (isset($layer['crop'])) {
            $crop = $layer['crop'];
            $image->cropimage(
                $crop->getWidth(),
                $crop->getHeight(),
                $layer['width'] - $crop->getWidth(),
                $layer['height'] - $crop->getHeight()
            );
        }

        if (isset($layer['corner']) && $layer['corner']) {
            $image->rotateimage(new ImagickPixel('none'), $layer['corner']);
        }

        if (isset($layer['flip']) && $layer['flip'] == true) {
            $image->flipImage();
        }
        if (isset($layer['flop']) && $layer['flop'] == true) {
            $image->flopImage();
        }

        $canvas->compositeImage($image, $image->getImageCompose(), $layer['left'], $layer['top']);
        $image->destroy();
    }

    public function addTextToCanvas($canvas, $layer)
    {
        $text = new ImagickDraw();
        $image = new Imagick();
        $color = new ImagickPixel($layer['color']);
        $background = new ImagickPixel('none');
        $font = $this->_getFontFileByFontName($layer['font']);
        if (!$font) {
            return;
        }

        $text->setFont($font);
        $text->setFontSize($layer['fontSize']);
        $text->setFillColor($color);
        $text->setGravity(Imagick::GRAVITY_NORTHWEST);
        if (isset($layer['fontWeight'])){
            $text->setFontWeight($layer['fontWeight']);
        }
        if (isset($layer['fontStyle'])){
            $text->setFontStyle($layer['fontStyle']);
        }
        if (isset($layer['decoration'])) {
            $text->setTextDecoration($layer['decoration']);
        }

        if (isset($layer['outline'])) {
            $outline = $layer['outline'];
            $text->setStrokeWidth($outline['strokeWidth']);
            $text->setStrokeColor($outline['color']);
        }

        $metrics = $image->queryFontMetrics($text, $layer['text']);
        if (isset($layer['shadow'])) {
            $shadow = $layer['shadow'];
            $image->newImage(
                $metrics['textWidth'] + $shadow['offsetX'],
                $metrics['textHeight'] + $shadow['offsetY'],
                $background
            );
        } else {
            $image->newImage($metrics['textWidth'], $metrics['textHeight'], $background);
        }
        $image->annotateImage($text, 0, 0, 0, $layer['text']);

        if (isset($layer['shadow'])) {
            $image = $this->createTextShadow($image, $layer['shadow']);
        }

        $this->addImageToCanvas($canvas, $layer, $image);
        $text->destroy();
    }

    /**
     * Create text shadow
     *
     * @param Imagick $image Image
     * @param array $shadowData Shadow Params
     * @return mixed
     */
    public function createTextShadow($image, $shadowData)
    {
        $shadowImage = clone $image;
        $shadowImage->setImageBackgroundColor(new ImagickPixel($shadowData['color']));
        $shadowImage->shadowImage(100, $shadowData['blur'] / 10  , $shadowData['offsetX'], $shadowData['offsetY']);
        $shadowImage->compositeimage($image, Imagick::COMPOSITE_OVER, 0, 0);

        return $shadowImage;
    }

    /**
     * Save file to DB
     *
     * @param Imagick $canvas  canvas
     * @param int     $imageId Image Id
     * @return void
     */
    public function saveCanvas($canvas, $imageId)
    {
        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();
        $currentProduct = Mage::registry('current_product');
        $designConfig = Mage::getSingleton('gmpd/design_config');
        $configPath = $designConfig->getBaseMediaPath();

        if($currentProduct && $currentProduct->getId()) {
            $fileToSave = $this->_prepareFileForSave($canvas);
            $canvas->writeImage($fileToSave);

            $design = Mage::getModel('gmpd/design');
            $design->setData(array(
                'customer_id' => $customerId,
                'session_id' => $customerId ? null : Mage::helper('designer')->getDesignerSessionId(),
                'product_id' => $currentProduct->getId(),
                'design' => str_replace($configPath, '', $fileToSave),
                'image_id' => $imageId,
                'create_time' => Mage::getModel('core/date')->date(),
            ));
            $design->save();
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
        $imageExtension = $canvas->getImageFormat();
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

    protected function _getFontFileByFontName($fontFamily)
    {
        $font = Mage::getModel('gmpd/font');
        $font->loadFontByFile($fontFamily);
        if ($font && $font->getId()) {
            $fontPath = $font->getTempPath($font->getFont());
            $fontPath = str_replace(Mage::getBaseDir(), '', $fontPath);
            return trim($fontPath, '/');
        }

        return false;
    }
}

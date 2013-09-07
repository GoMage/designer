<?php
/*
 * @todo implement other font formats (now only "ttf")
 * @todo remove font family hardcode
 */
class GoMage_ProductDesigner_Helper_ProductImage extends Mage_Core_Helper_Abstract 
{
    /**
     * Create image and add layers
     * 
     * @param array $imageSettings Base Image Settings
     * @param array $layers        Layers
     * @return  void
     */
    public function addLayersToImage($imageSettings, $layers) 
    {
        //If image has no added layers
        if(count($layers) == 0) {
            return false;
        }
        $dimensions = $imageSettings['dimensions'];
        $container = $this->createContainer($dimensions['width'], $dimensions['height']);

        if($container) {
            $imageData = array(
                'path' => $imageSettings['path'],
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
            );
            $this->addImageToContainer($container, $imageData);

            foreach($layers as $layer) {
                $designAreaLeft = $imageSettings['l']-$imageSettings['w']/2;
                $designAreaTop = $imageSettings['t']-$imageSettings['h']/2;

                $imageWidth = $layer['width'];
                $imageHeight = $layer['height'];
                $imageCenterX = $layer['left'];
                $imageCenterY = $layer['top'];
                $imageLeft = $imageCenterX - ($imageWidth/2);
                $imageTop = $imageCenterY - ($imageHeight/2);
                $xCoord = $imageLeft < 0 ? abs($imageLeft) : 0;
                $yCoord = $imageTop < 0 ? abs($imageTop) : 0;

                if(!(($imageLeft < 0 && abs($imageLeft) >= $imageWidth) 
                    || ($imageTop < 0 && abs($imageTop) >= $imageHeight))) {
                    if($imageLeft < 0) {
                        $imageLeft = $designAreaLeft;
                    } else {
                        $imageLeft += $designAreaLeft;
                    }

                    if($imageTop < 0) {
                        $imageTop = $designAreaTop;
                    } else {
                        $imageTop += $designAreaTop;
                    }

                    $layerData = array(
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                        'top' => $imageTop,
                        'left' => $imageLeft,
                        'xCoord' => $xCoord,
                        'yCoord' => $yCoord,
                        'corner' => isset($layer['corner']) ? $layer['corner'] : null
                    );

                    if($layer['type'] == 'image') {
                        $baseUrl = Mage::getBaseUrl('media');
                        $baseDir = Mage::getBaseDir('media') . DS;
                        $imagePath = urldecode(str_replace($baseUrl, $baseDir, $layer['imageSrc']));

                        if(file_exists($imagePath)) {
                            $imageOriginalWidth = $layer['originalWidth'];
                            $imageOriginalHeight = $layer['originalHeight'];

                            $imageData = array_merge(
                                array(
                                    'path' => $imagePath,
                                    'originalWidth' => $imageOriginalWidth,
                                    'originalHeight' => $imageOriginalHeight,
                                ),
                                $layerData
                            );

                            $this->addImageToContainer($container, $imageData);
                        }
                    } elseif ($layer['type'] == 'text') {
                        $textData = array_merge($layer, $layerData);
                        $this->addTextToContainer($container, $textData);
                    }
                }
            }            
            $this->savePreparedContainerImages($container, $imageSettings['path'], $imageSettings['image_id']);
        }
    }
    
    /**
     * Save Image
     * 
     * @param resource $container Image Container
     * @param string $fileToSave  Base Image Path
     * @param int    $imageId     Image Id
     * @return void
     */
    public function savePreparedContainerImages($container, $fileToSave, $imageId) 
    {
        $imageExtension = $this->_prepareImageExtension($fileToSave);        
        if ($imageExtension == 'png') {
            $imageExtension = 'jpeg';
        }
        $imageCreateFunction = 'image'.$imageExtension;

        $fileToSave = $this->_prepareFileForSave($imageExtension);
        $this->saveFileToDb($fileToSave, $imageId);

        $imageCreateFunction($container, $fileToSave, 100);
        imagedestroy($container);
    }

    /**
     * Add Image to container
     * 
     * @param resource $container Image Container
     * @param array    $imageData Image Data
     * @return void
     */
    public function addImageToContainer($container, $imageData) 
    {
        $imagePath = (string) $imageData['path'];
        $imageWidth = (float) $imageData['width'];
        $imageHeight = (float) $imageData['height'];
        $imageOriginalWidth = (float) $imageData['originalWidth'];
        $imageOriginalHeight = (float) $imageData['originalHeight'];
        $imageTop = (float) $imageData['top'];
        $imageLeft = (float) $imageData['left'];
        $xCoord = (float) $imageData['xCoord'];
        $yCoord = (float) $imageData['yCoord'];
        $angle = isset($imageData['corner']) ? $imageData['corner'] : null;

        $imageExtension = $this->_prepareImageExtension($imagePath);

        if(gettype($container) == 'resource' && $imagePath !== ''
            && $imageWidth > 0 && $imageHeight > 0
        ) {
            $imageCreateFunction = 'imagecreatefrom'.$imageExtension;
            $image = $imageCreateFunction($imagePath);

            if($imageOriginalHeight > 0 && $imageOriginalWidth > 0
                && $imageOriginalHeight != $imageHeight && $imageOriginalWidth != $imageWidth
            ) {
                $newImageContainer = imagecreatetruecolor($imageWidth, $imageHeight);
                imagealphablending($newImageContainer, false);
                imagesavealpha($newImageContainer,true);
                $transparent = imagecolorallocatealpha($newImageContainer, 255, 255, 255, 127);
                imagefilledrectangle($newImageContainer, 0, 0, $imageWidth, $imageHeight, $transparent);
                imagecopyresized($newImageContainer, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageOriginalWidth, $imageOriginalHeight);
                $image = $newImageContainer;
                if ($angle) {
                    $image = imagerotate($image, $angle, $transparent);
                }
                
            }
            imagecopy($container, $image, $imageLeft, $imageTop, $xCoord, $yCoord, $imageWidth-$xCoord, $imageHeight-$yCoord);
            imagedestroy($image);
        }
    }

    /**
     * Add text to container
     * 
     * @param resource $container Container
     * @param array    $textData  Text Settings
     * @return void
     */
    public function addTextToContainer($container, $textData) 
    {        
        if(gettype($container) == 'resource') {
            $fontHelper = Mage::helper('designer/font');

            $fontSize = $textData['fontSize'];
            $outlineWidth = isset($textData['outline']['width']) ? $textData['outline']['width'] : null;
            $outlineColor = isset($textData['outline']['color']) ? $textData['outline']['color'] : null;
            $angle = 0;
            $x = 0;
            $y = $fontSize;

            $text = $textData['text'];

            $fontFamily = $textData['fontFamily'];
            $font  = $fontHelper->getFontFileByFamilyName($fontFamily);

            if ($font) {
                $fontFile = Mage::getSingleton('gmpd/font_gallery_config')->getBaseMediaPath() . $font->getFont();
                $containerWidth = $textData['width']+$textData['width']/2;
                $containerHeight = $textData['height'];
                $containerLeft = $textData['left'];
                $containerTop = $textData['top'];

                $xCoord = (float) $textData['xCoord'];
                $yCoord = (float) $textData['yCoord'];

                $textContainer = imagecreatetruecolor($containerWidth, $containerHeight);

                $fontColor = $fontHelper->hex2rgb(@$textData['color']);
                $outlineColor = $fontHelper->hex2rgb($outlineColor);

                $fontColor = imagecolorallocate($textContainer, $fontColor['red'], $fontColor['green'], $fontColor['blue']);
                $outlineColor = imagecolorallocate($textContainer, $outlineColor['red'], $outlineColor['green'], $outlineColor['blue']);

                $textBackground = imagecolorallocate($textContainer, 255, 255, 255);
                imagefilledrectangle($textContainer, 0, 0, $containerWidth, $containerHeight, $textBackground);

                $fontHelper->imagettftextoutline($textContainer, $fontSize, $angle, $x, $y,
                    $fontColor, $outlineColor, $fontFile, $text, $outlineWidth);

                imagecopy($container, $textContainer, $containerLeft, $containerTop, $xCoord, $yCoord, $containerWidth-$xCoord, $containerHeight-$yCoord);
            }
        }
    }

    /**
     * Create container
     * 
     * @param float $width  Width
     * @param float $height Height
     * @return resource|boolean
     */
    public function createContainer($width, $height) 
    {
        $width = (int) $width;
        $height = (int) $height;
        if($width > 0 && $height > 0) {
            $container = imagecreatetruecolor($width, $height);
            return $container;
        }
        return false;
    }

    /**
     * Return image extension
     * 
     * @param string $imagePath Image path
     * @return string
     */
    public function _prepareImageExtension($imagePath)
    {
        $imagePathExploded = explode('.', $imagePath);
        $imageExtension = array_pop($imagePathExploded);
        $imageExtension = strtolower($imageExtension);
        if(in_array($imageExtension, array('jpg', 'jpeg'))) {
            $imageExtension = 'jpeg';
        }
        return $imageExtension;
    }

    /**
     * Prepare file for save
     *
     * @param string $extension File extension
     * @return string
     */
    public function prepareFileForSave($extension)
    {
        $pathToSave = $this->_preparePathToSave();
        $fileName = Mage::helper('core')->getRandomString(16) . '.' . $extension;

        return $pathToSave . DS . $fileName;
    }

    /**
     * Prepare path for save
     *
     * @return string
     */
    protected function _preparePathToSave()
    {
        $pathToSave = Mage::getSingleton('gmpd/design_config')->getBaseMediaPath()
                . DS . 'catalog' . DS . 'product';
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
     * Save file to DB
     * 
     * @param string $fileToSave File
     * @param int    $imageId    Image Id
     * @return void
     */
    public function saveFileToDb($fileToSave, $imageId) 
    {
        $customerId = (int) Mage::getSingleton('customer/session')->getCustomerId();
        $currentProduct = Mage::registry('current_designer_product');
        $designConfig = Mage::getSingleton('gmpd/design_config');
        $configPath = $designConfig->getBaseMediaPath();
        $designFile = str_replace($configPath, '', $fileToSave);

        if($currentProduct && $currentProduct->getId()) {
            $design = Mage::getModel('gmpd/design');
            $design->setData(array(
                'customer_id' => $customerId,
                'session_id' => $customerId ? null : Mage::helper('designer')->getDesignerSessionId(),
                'product_id' => $currentProduct->getId(),
                'design' => $designFile,
                'image_id' => $imageId,
                'create_time' => Mage::getModel('core/date')->date(),
            ));
            $design->save();
        }
    }

    /**
     * Return image path from url
     *
     * @param string $url Url
     * @return string
     */
    public function getImagePathFromUrl($url)
    {
        $baseUrl = Mage::getBaseUrl('media');
        $baseDir = Mage::getBaseDir('media') . DS;

        return urldecode(str_replace($baseUrl, $baseDir, $url));
    }
}
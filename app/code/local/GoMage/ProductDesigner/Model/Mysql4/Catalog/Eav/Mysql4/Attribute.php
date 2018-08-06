<?php
/**
 * GoMage Product Designer Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2013-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use/
 * @version      Release: 2.6.0
 * @since        Available since Release 1.0.0
 */

class GoMage_ProductDesigner_Model_Mysql4_Catalog_Eav_Mysql4_Attribute
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute
{
    protected $_navigationAttributeModel;

    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        if ($navigationResourceModel = $this->_getNavigationAttributeResourceModel()) {
            $navigationResourceModel->_saveOption($object);
        }
        $colorAttributeCode = Mage::getStoreConfig('gomage_designer/navigation/color_attribute');
        if (!$colorAttributeCode || $object->getAttributeCode() != $colorAttributeCode) {
            return parent::_saveOption($object);
        }

        $option = $object->getOption();
        if (is_array($option)) {
            if (isset($option['color'])) {
                $write = $this->_getWriteAdapter();
                $optionTable        = $this->getTable('attribute_option');
                $optionValueTable   = $this->getTable('attribute_option_value');
                $stores = Mage::getModel('core/store')
                    ->getResourceCollection()
                    ->setLoadDefault(true)
                    ->load();
                $destinationDirectory = $this->getDestinationDirectory();
                $attributeDefaultValue = array();
                $table = Mage::getSingleton('core/resource')->getTableName('gomage_productdesigner_attribute_option');
                $connection = $this->_getReadAdapter();

                if (!is_array($object->getDefault())) {
                    $object->setDefault(array());
                }

                foreach ($option['color'] as $optionId => $colorHex) {
                    $optionIdInt = (int)$optionId;
                    if (!empty($option['delete'][$optionId])) {
                        if ($optionIdInt) {
                            $write->delete($optionTable, array('option_id = ?' => $optionIdInt));
                        }
                        continue;
                    }
                    if (!$optionIdInt) {
                        $data = array(
                            'attribute_id'  => $object->getId(),
                            'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        $write->insert($optionTable, $data);
                        $optionIdInt = $write->lastInsertId();
                    } else {
                        $data = array(
                            'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        $write->update($optionTable, $data, $write->quoteInto('option_id=?', $optionIdInt));
                    }

                    $fileName = $option['value'][$optionId][0] . '_' . uniqid() . '.png';

                    $this->saveImage($colorHex, $destinationDirectory . DS . $fileName);
                    $attributeId = $object->getId();
                    $isNotNew = $connection->fetchOne("SELECT COUNT(*) FROM {$table}"
                            . " WHERE `attribute_id` = {$attributeId} AND `option_id` = '{$optionIdInt}';") > 0;

                    if ($isNotNew) {
                        $connection->query("UPDATE {$table} SET `filename` = '{$fileName}', `color_hex` = '{$colorHex}'"
                            . " WHERE `attribute_id` = {$attributeId} AND `option_id` = {$optionIdInt}; ");
                    } else {
                        $connection->query("INSERT INTO {$table}" .
                            " VALUES ({$optionIdInt},{$attributeId},'{$fileName}','{$colorHex}')");
                    }

                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $optionIdInt;
                        } else if ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = array($optionIdInt);
                        }
                    }

                    // Default value
                    if (!isset($option['value'][$optionId][0])) {
                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined.'));
                    }

                    $write->delete($optionValueTable, $write->quoteInto('option_id=?', $optionIdInt));
                    foreach ($stores as $store) {
                        if (isset($option['value'][$optionId][$store->getId()])
                            && (!empty($option['value'][$optionId][$store->getId()])
                                || $option['value'][$optionId][$store->getId()] == "0")) {
                            $data = array(
                                'option_id' => $optionIdInt,
                                'store_id'  => $store->getId(),
                                'value'     => $option['value'][$optionId][$store->getId()],
                            );
                            $write->insert($optionValueTable, $data);
                        }
                    }
                }

                $write->update($this->getMainTable(), array(
                    'default_value' => implode(',', $attributeDefaultValue)
                ), $write->quoteInto($this->getIdFieldName() . '=?', $object->getId()));
            }
        }

        return $this;
    }

    protected function _getNavigationAttributeResourceModel()
    {
        if (is_null($this->_navigationAttributeModel)) {
            if (Mage::helper('gomage_designer')->advancedNavigationEnabled()
                && class_exists('GoMage_Navigation_Model_Resource_Eav_Mysql4_Entity_Attribute')) {
                $this->_navigationAttributeModel = Mage::getModel('gomage_navigation/resource_eav_mysql4_entity_attribute');
            } else {
                $this->_navigationAttributeModel = false;
            }
        }

        return $this->_navigationAttributeModel;
    }

    /**
     * @param $color
     * @param $destination
     *
     * @return void
     */
    private function saveImage($color, $destination)
    {
        $color = (int)hexdec($color);
        $red = ($color >> 16) & 0xFF;
        $green = ($color >> 8) & 0xFF;
        $blue = $color & 0xFF;

        $image = imagecreatetruecolor(100, 100);

        $imageColor = imagecolorallocate($image, $red, $green, $blue);
        imagefill($image, 0, 0, $imageColor);

        imagepng($image, $destination);
        imagedestroy($image);
    }

    /**
     * @return string
     */
    private function getDestinationDirectory()
    {
        $ioObject = new Varien_Io_File();
        $destinationDirectory = Mage::getBaseDir('media') . '/option_image';

        try {
            $ioObject->open(array('path' => $destinationDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destinationDirectory, 0777, true);
            $ioObject->open(array('path' => $destinationDirectory));
        }

        return $destinationDirectory;
    }
}

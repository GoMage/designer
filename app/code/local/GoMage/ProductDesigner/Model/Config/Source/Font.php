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

class GoMage_ProductDesigner_Model_Config_Source_Font
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'Liberation Mono', 'label' => 'Liberation Mono'),
            array('value' => 'Liberation Sans', 'label' => 'Liberation Sans'),
            array('value' => 'Liberation Sans Narrow', 'label' => 'Liberation Sans Narrow'),
            array('value' => 'Liberation Serif', 'label' => 'Liberation Serif'),
        );
    }

    public function toOptionHash()
    {
        return array(
            'Liberation Mono'        => 'Liberation Mono',
            'Liberation Sans'        => 'Liberation Sans',
            'Liberation Sans Narrow' => 'Liberation Sans Narrow',
            'Liberation Serif'       => 'Liberation Serif',
        );
    }
}
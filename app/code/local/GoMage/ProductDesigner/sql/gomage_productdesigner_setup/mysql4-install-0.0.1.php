<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(<<<SQL
  -- CLIPARTS CATEGORIES TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_category')}`;

  CREATE TABLE `{$this->getTable('gomage_productdesigner_category')}` (
    `category_id` SMALLINT(6) NOT NULL AUTO_INCREMENT,
    `parent_id` SMALLINT(6) NOT NULL DEFAULT '0',
    `name` VARCHAR(255) DEFAULT '',
    `sort_order` SMALLINT(5) DEFAULT '0',
    `is_active` TINYINT(1) DEFAULT '1',
    `is_default` TINYINT(1) DEFAULT '0',
    PRIMARY KEY (`category_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cliparts categories' ;

  -- CLIPARTS TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_clipart')}`;

  -- TODO:
  -- for implement tags better add two more tables
  -- cliparts_tags [id, tag] and cliparts_to_tags [clipart_id, tag_id]
  -- but solution with `tag` field is simpliest
  CREATE TABLE `{$this->getTable('gomage_productdesigner_clipart')}` (
    `clipart_id` SMALLINT(6) NOT NULL AUTO_INCREMENT,
    `category_id` SMALLINT(6) NOT NULL,
    `label` VARCHAR(255) DEFAULT '',
    `image` VARCHAR(255) DEFAULT '',
    `tags` VARCHAR(1000) DEFAULT '',
    PRIMARY KEY (`clipart_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cliparts gallery' ;

  -- UPLOADED IMAGES TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_uploaded_image')}`;

  CREATE TABLE `{$this->getTable('gomage_productdesigner_uploaded_image')}` (
    `image_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `image` VARCHAR(255) DEFAULT '',
    `customer_id` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`image_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Uploaded images' ;


  -- FONTS TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_font')}`;

  CREATE TABLE `{$this->getTable('gomage_productdesigner_font')}` (
    `font_id` SMALLINT(6) NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(255) DEFAULT '',
    `font` VARCHAR(255) DEFAULT '',
    `tags` VARCHAR(1000) DEFAULT '',
    PRIMARY KEY (`font_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fonts gallery';

  -- SAVED DESIGNS TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_design')}`;

  CREATE TABLE `{$this->getTable('gomage_productdesigner_design')}` (
    `design_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT(10) UNSIGNED NOT NULL,
    `product_id` INT(10) UNSIGNED NOT NULL,
    `comment` VARCHAR(1000) DEFAULT NULL,
    `design` VARCHAR(100) NOT NULL,
    `settings` TEXT NOT NULL,
    `is_shared` TINYINT(1) DEFAULT '0',
    PRIMARY KEY (`design_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Saved designs' ;

  -- PUBLISHED DESIGNS TABLE
  DROP TABLE IF EXISTS `{$this->getTable('gomage_productdesigner_published_design')}`;

  CREATE TABLE `{$this->getTable('gomage_productdesigner_published_design')}` (
    `published_design_id` INT(6) NOT NULL AUTO_INCREMENT,
    `design_id` INT(6) NOT NULL,
    `category_id` INT(10) UNSIGNED NOT NULL,
    `customer_id` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`published_design_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Saved designs' ;
SQL
);

$installer->endSetup();

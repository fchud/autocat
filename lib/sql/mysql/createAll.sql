CREATE TABLE `dict_body` (
  `body_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(32) NOT NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`body_id`),
  UNIQUE INDEX `body_name_UNIQUE` (`name` ASC))
COMMENT = 'body types dictionary';

CREATE TABLE `dict_color` (
  `color_id` INT(3) ZEROFILL NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(32) NOT NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`color_id`),
  UNIQUE INDEX `color_name_UNIQUE` (`name` ASC))
COMMENT = 'colors dictionary';

CREATE TABLE `dict_brand` (
  `brand_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(24) NOT NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`brand_id`),
  UNIQUE INDEX `brand_name_UNIQUE` (`name` ASC))
COMMENT = 'brands dictionary';

CREATE TABLE `dict_model` (
  `model_id` INT NOT NULL AUTO_INCREMENT,
  `brand_id` INT NOT NULL,
  `name` VARCHAR(16) NOT NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`model_id`),
  UNIQUE KEY `uk_model_brand_id_name` (`brand_id`, `name`),
  INDEX `fk_model_brand_id_idx` (`brand_id` ASC),
  CONSTRAINT `fk_model_brand_id`
    FOREIGN KEY (`brand_id`)
    REFERENCES `dict_brand` (`brand_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION)
COMMENT = 'brand models dictionary';

CREATE TABLE `catalog` (
  `cat_id` INT ZEROFILL NOT NULL AUTO_INCREMENT,
  `model_id` INT NOT NULL,
  `body_id` INT NOT NULL,
  `price` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(4096) NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`cat_id`),
  INDEX `fk_cat_model_id_idx` (`model_id` ASC),
  INDEX `fk_cat_body_id_idx` (`body_id` ASC),
  CONSTRAINT `fk_cat_model_id`
    FOREIGN KEY (`model_id`)
    REFERENCES `dict_model` (`model_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cat_body_id`
    FOREIGN KEY (`body_id`)
    REFERENCES `dict_body` (`body_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION);

CREATE TABLE `catcolor` (
  `catcolor_id` INT NOT NULL AUTO_INCREMENT,
  `cat_id` INT UNSIGNED NOT NULL,
  `color_id` INT(3) UNSIGNED NOT NULL,
  `comment` VARCHAR(128) NULL,
  PRIMARY KEY (`catcolor_id`),
  UNIQUE KEY `uk_catcol_cat_id_color_id` (`cat_id`, `color_id`),
  INDEX `fk_catcol_cat_id_idx` (`cat_id` ASC),
  INDEX `fk_catcol_color_id_idx` (`color_id` ASC),
  CONSTRAINT `fk_catcol_cat_id`
    FOREIGN KEY (`cat_id`)
    REFERENCES `catalog` (`cat_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_catcol_color_id`
    FOREIGN KEY (`color_id`)
    REFERENCES `dict_color` (`color_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION);

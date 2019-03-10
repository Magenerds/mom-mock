-- --------
-- ORDER --
-- --------
CREATE TABLE `order` (
	id INT(6) UNSIGNED AUTO_INCREMENT UNIQUE PRIMARY KEY,
	increment_id VARCHAR(30) NOT NULL,
	store VARCHAR(30) NOT NULL,
	status VARCHAR(50),
	status_reason VARCHAR(50),
	origin_date VARCHAR(50),
  address_type VARCHAR(30),
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  address1 VARCHAR(80),
  city VARCHAR(80),
  zip VARCHAR(10),
  country_code VARCHAR(10),
  email VARCHAR(50),
  segment VARCHAR(10),
  type VARCHAR(10)
);

CREATE TABLE `order_item` (
	order_id INT(6) UNSIGNED,
	id VARCHAR(30),
	line_number INT(6) UNSIGNED,
	product_type VARCHAR(30),
	sku VARCHAR(30),
	product_name VARCHAR(50),
	image_url VARCHAR(100),
	custom_attributes VARCHAR(250),
	status VARCHAR(30),
	net_amount VARCHAR(30),
	gross_amount VARCHAR(30),
	taxes_amount VARCHAR(30),
	taxes_rate VARCHAR(30),
	source_id VARCHAR(255),
	FOREIGN KEY (order_id) REFERENCES `order`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ------
-- RMA --
-- ------
CREATE TABLE `rma` (
	id INT(6) UNSIGNED AUTO_INCREMENT UNIQUE PRIMARY KEY,
  order_id INT(6) UNSIGNED,
  increment_id VARCHAR(30) NOT NULL,
	rma_id VARCHAR(30) NOT NULL,
	source VARCHAR(255) NOT NULL,
	sales_channel VARCHAR(30) NOT NULL,
	status VARCHAR(50),
  tracking_number VARCHAR(30),
  carrier VARCHAR(50),
  label VARCHAR(255),
  credit_note VARCHAR(50),
  FOREIGN KEY (order_id) REFERENCES `order`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE `rma_item` (
	id int(6) UNSIGNED AUTO_INCREMENT UNIQUE PRIMARY KEY,
	rma_id INT(6) UNSIGNED,
	line_number INT(6) UNSIGNED,
	sku VARCHAR(30),
	product_name VARCHAR(50),
	status VARCHAR(30),
	reason VARCHAR(255),
	reason_description VARCHAR(255),
	base_condition VARCHAR(100),
	condition_description VARCHAR(255),
	net_amount VARCHAR(30),
	gross_amount VARCHAR(30),
	taxes_amount VARCHAR(30),
	taxes_rate VARCHAR(30),
	FOREIGN KEY (rma_id) REFERENCES `rma`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ---------
-- SYSTEM --
-- ---------
CREATE TABLE `integration` (
	id VARCHAR(10) UNIQUE PRIMARY KEY,
	url VARCHAR(60) NOT NULL,
	secret VARCHAR(60) NOT NULL
);

CREATE TABLE `flags` (
  id INT(6) UNSIGNED AUTO_INCREMENT UNIQUE PRIMARY KEY,
  name VARCHAR(50),
  value VARCHAR(255)
);

INSERT INTO `flags` (`name`, `value`) VALUES ('credit_note_counter', 0);

-- ----------
-- PRODUCT --
-- ----------
CREATE TABLE `product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `sku` varchar(64) DEFAULT NULL COMMENT 'SKU',
  `type` varchar(255) DEFAULT NULL COMMENT 'Type',
  `name` varchar(255) DEFAULT NULL COMMENT 'Name',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `attributes` longtext COMMENT 'Attributes',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created At',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated At',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=2244 DEFAULT CHARSET=utf8;

CREATE TABLE `product_child` (
  `product_id` int(11) unsigned NOT NULL,
  `child_sku` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `child_id` (`child_sku`,`product_id`),
  KEY `PRODUCT_CHILD_PRODUCT_ID_PRODUCT` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------
-- SHIPPING LABEL --
-- -----------------
CREATE TABLE `shipping_package` (
  `id` int(11) unsigned NOT NULL,
  `carrier` varchar(255) NOT NULL,
  `tracking_number` varchar(255) NOT NULL,
  `tracking_link` varchar(1024) NOT NULL,
  `tracking_comment` varchar(1024) NOT NULL,
  `shipping_label_link` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `shipping_package_item` (
  `package_id` int(11) unsigned NOT NULL,
  `order_item_id` int(6) unsigned NOT NULL,
  FOREIGN KEY (package_id) REFERENCES `shipping_package`(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------
-- JOURNAL --
-- ----------
CREATE TABLE `journal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Message Id',
  `delivery_id` varchar(64) DEFAULT NULL COMMENT 'Delivery Id',
  `status` varchar(32) DEFAULT NULL COMMENT 'Status',
  `topic` varchar(255) DEFAULT NULL COMMENT 'Topic of the message',
  `body` longtext COMMENT 'Body of the message',
  `result` longtext COMMENT 'Result',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Sent At',
  `retried_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Retried At',
  `tries` int(10) unsigned DEFAULT NULL COMMENT 'Number of tries',
  `direction` varchar(32) DEFAULT 'outgoing' COMMENT 'Direction in which the message is going',
  `to` varchar(64) DEFAULT '*' COMMENT 'To which the message is being sent',
  `protocol` varchar(64) DEFAULT 'unknown' COMMENT 'In which protocol the message is being sent or receive',
  PRIMARY KEY (`id`)
);

-- -------------------------------
-- STOCK AGGREGATES AND SOURCES --
-- -------------------------------
CREATE TABLE `aggregate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `source` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
  `source_id` varchar(255) NOT NULL,
  `aggregate_id` int(11) unsigned,
  FOREIGN KEY (aggregate_id) REFERENCES `aggregate`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ------------
-- INVENTORY --
-- ------------
CREATE TABLE `inventory` (
  `sku` VARCHAR(255) NOT NULL,
  `source_id` int(11) unsigned NOT NULL,
  `qty` int(6) NOT NULL,
  UNIQUE KEY `sku_source_id` (`sku`,`source_id`),
  FOREIGN KEY (sku) REFERENCES `product`(sku)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (source_id) REFERENCES `source`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
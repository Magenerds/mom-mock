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
	status VARCHAR(30),
	net_amount VARCHAR(30),
	gross_amount VARCHAR(30),
	taxes_amount VARCHAR(30),
	taxes_rate VARCHAR(30),
	FOREIGN KEY (order_id) REFERENCES `order`(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

CREATE TABLE `integration` (
	id VARCHAR(10) UNIQUE PRIMARY KEY,
	url VARCHAR(60) NOT NULL,
	secret VARCHAR(60) NOT NULL
);

-- Create syntax for TABLE 'product'
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

-- Create syntax for TABLE 'product_child'
CREATE TABLE `product_child` (
  `product_id` int(11) unsigned NOT NULL,
  `child_sku` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `child_id` (`child_sku`,`product_id`),
  KEY `PRODUCT_CHILD_PRODUCT_ID_PRODUCT` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

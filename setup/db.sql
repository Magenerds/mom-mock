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
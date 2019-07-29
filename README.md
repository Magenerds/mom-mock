# Magento Order Management Mock

## Overview
This mock simulates the Magento Order Management system. It is designed to be used for 
local development or test systems which can not connect to a real Magento Order Management
instance.

It supports the basic order workflow interaction between MDC and MOM. This includes the
following functionality:



## Valet + Setup
Run the following commands to set up the mock with Valet +:

```bash
composer install
cd pub
valet link mom
cd ..
valet db create mom
valet db import setup/db.sql mom
```

## Setup without Valet
1. Start with running composer:

    ```bash
    composer install
    ```

2. Create an empty database 

3. Open `pub/web/index.php` in your MDC instance and edit the following to match the created database:

    ```php
    $container['db'] = function($c) {
        return DriverManager::getConnection([
            'dbname' => 'mommock',
            'user' => 'magento',
            'password' => 'magento',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ]);
    };
    ```

4. Execute the .sql script in `setup/db.sql`

5. Set your webserver to serve the folder `pub`

## Connect M2 with MOM-MOCK

1. Install MCOM connector

2. Open `app/etc/env.php` in your MDC instance and edit the following
credentials (Taking into account your DB settings from before):

    ```php
    'serviceBus' => 
      array (
        'url' => 'http://mom.test/',
        'oauth_server_url' => 'http://mom.test/',
        'oauth_client_id' => 'mom',
        'oauth_client_secret' => 'mom',
        'application_id' => 'mdc',
        'secret' => 'mom',
        'secure_endpoint' => true,
      )
    ```

3. Run `bin/magento setup:upgrade --keep-generated` in your MDC instance
to register your MDC instance to the MOM mock and to request your first
OAuth token.

## Supported Messages 
| Message | Endpoint | Description |
|----|----|----|
| magento.service_bus.remote.register | oms | Register integration |
| magento.service_bus.remote.unregister | oms | Unregister integration |
| magento.sales.order_management.create | oms | Create an order in MOM |
| magento.logistics.fulfillment_management.customer_shipment_done | mdc | Complete Shipment |
| magento.sales.order_management.updated | mdc | Update Order Status in MDC |
| magento.logistics.carrier_management.request_shipping_details | mdc | Request Shipping label from MDC |
| magento.postsales.return_management.authorize | oms | Request a RMA |
| magento.catalog.product_management.updated | oms | Export Product to MOM |
| magento.postsales.return_management.updated | mdc | Update RMA status |
| magento.postsales.refund_management.updated | mdc | Creates a creditmemo |
| magento.inventory.aggregate_stock_management.updated | mdc | Stock update from MOM |
| magento.logistics.warehouse_management.request_shipment| mdc | Request shipment from warehouse |
| magento.inventory.source_repository.search| oms | Request source information |

## Features
- Order overview and detail page
- Ship/Cancel line items
- Request a shipping label from a carrier integration
- RMA overview and approval
- Trigger refund
- API journal
- Send a stock snapshot
- Stock aggregate and source management
- Product and inventory overview
- Request shipments from a source or warehouse
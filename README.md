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
valet link mom --secure
cd ..
valet db create mom
valet db import setup/db.sql mom
```

Open `app/etc/env.php` in your MDC instance and edit the following
credentials:

```php
'serviceBus' => 
  array (
    'url' => 'https://mom.test/',
    'oauth_server_url' => 'https://mom.test/',
    'oauth_client_id' => 'mom',
    'oauth_client_secret' => 'mom',
    'application_id' => 'mdc',
    'secret' => 'mom',
    'secure_endpoint' => true,
  )
```

Run `bin/magento setup:upgrade --keep-generated` in your MDC instance
to register your MDC instance to the MOM mock and to request your first
OAuth token.

## Supported Messages 
| Message | Endpoint | Description | Implemented |
|----|----|----|----|
| magento.service_bus.remote.register | oms | Register Remote | ✓ |
| magento.sales.order_management.create | oms | Create an order in MCOM  | ✓ |
| magento.logistics.fulfillment_management.customer_shipment_done | mdc | Complete Shipment  | ✓ |
| magento.sales.order_management.updated | mdc | Update Order Status in MDC | ✓ |
| magento.logistics.carrier_management.request_shipping_details | mdc | Request Shipping label from MDC  | X |
| magento.postsales.return_management.authorize | oms | Request a RMA | X |
| magento.postsales.return_management.updated | mdc | Update RMA status | X |
| magento.postsales.refund_management.updated | mdc | Creates a creditmemo | X |

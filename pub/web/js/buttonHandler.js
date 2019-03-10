/**
 * Copyright (c) 2018 Magenerds
 * All rights reserved
 *
 * This product includes proprietary software developed at Magenerds, Germany
 * For more information see http://www.magenerds.com/
 *
 * To obtain a valid license for using this software please contact us at
 * info@magenerds.com
 */
var buttonHandler = {
    CONST_STATUS_OK: 200,
    CONST_STATE_DONE: 4,

    /**
     * Object to mapp button class to js function (class: function)
     */
    buttons: {
        'return-approve': 'returnApprove',
        'return-cancel': 'returnCancel',
        'shipment-btn': 'shipment',
        'shipment-details-btn': 'requestShipmentLabel',
        'shipment-request-btn': 'requestShipment',
        'set-inventory': 'setInventory',
        'random-inventory': 'setRandomInventory',
        'set-inventory-for-product': 'setInventoryForProduct',
        'random-inventory-for-product': 'setRandomInventoryForProduct'
    },

    /**
     * Inital all events for buttons.
     */
    initial: function() {
        var self = this;
        for (var button in this.buttons) {
            var buttonFunction = this.buttons[button],
                elements = document.getElementsByClassName(button);

            for (var i = 0; i < elements.length; i++) {
                elements[i].addEventListener('click', buttonHandler[buttonFunction]);

            }
        };

        var snapshotForm = document.getElementById('aggregate-snapshot-form');

        if (snapshotForm) {
            snapshotForm.addEventListener('submit', self.snapshotForAggregate);
        }
    },

    /**
     * Ajax function for return approve.
     *
     * @param e
     */
    returnApprove: function(e) {
        e.preventDefault();
        var rmaId = e.currentTarget.parentNode.getAttribute('data-rma-id');

        buttonHandler._ajaxRequest('/rma/approve?rma_id=' + rmaId, '_genericResultHandler');
    },

    /**
     * Ajax function for return cancel.
     * TODO Implement this function when implementing return cancel.
     *
     * @param e
     */
    returnCancel: function(e) {
        e.preventDefault();
    },

    /**
     * Return array with IDs of selected order items.
     * 
     * @returns int[]
     * @private
     */
    _getSelectedItemsIds: function () {
        var allItems = document.getElementsByClassName('item-select'),
            orderItemIds = [];

        for (var i = 0; i < allItems.length; i++) {
            if(allItems[i].checked) {
                orderItemIds.push(allItems[i].getAttribute('value'));
            }
        }
        return orderItemIds;
    },

    /**
     * Function which will be registered for shipment button.
     *
     * @param e
     */
    shipment: function(e) {
        e.preventDefault();
        var orderId = e.currentTarget.parentNode.getAttribute('data-id'),
            orderItemIds = buttonHandler._getSelectedItemsIds();

        if (0 === orderItemIds.length) {
            alert('Please select the line item(s) to request the shipment for');
            return;
        }

        var select = document.getElementById('source-select');
        var sourceId = select.options[select.selectedIndex].value;
        if (sourceId === 'select-source') {
            alert('Please select a source');
            return;
        }

        buttonHandler._ajaxRequest(
            '/shipment/create?order_id=' + orderId + '&source_id=' + sourceId + '&order_item_ids=' + orderItemIds.join(','),
            '_shipmentResultHandler'
        );

    },
    
    /**
     * Send Ajax request that will trigger Magento to return shipment details including a shipping label
     *
     * @param e
     * @private
     */
    requestShipmentLabel: function(e) {
        e.preventDefault();
        const orderId = e.currentTarget.parentNode.getAttribute('data-id');
        const orderItemIds = buttonHandler._getSelectedItemsIds();

        if (0 === orderItemIds.length) {
            alert('Please select the line item(s) to request shipping label(s) for');
            return;
        }

        var select = document.getElementById('source-select');
        var sourceId = select.options[select.selectedIndex].value;
        if (sourceId === 'select-source') {
            alert('Please select a source');
            return;
        }

        buttonHandler._ajaxRequest(
            '/shipment/labels?order_id=' + orderId + '&source_id=' + sourceId + '&order_item_ids=' + orderItemIds.join(','),
            '_genericResultHandler'
        );

    },

    /**
     * Send Ajax request that will trigger a request shipment message
     *
     * @param e
     * @private
     */
    requestShipment: function(e) {
        console.log('test');
        e.preventDefault();
        const orderId = e.currentTarget.parentNode.getAttribute('data-id');
        const orderItemIds = buttonHandler._getSelectedItemsIds();

        if (0 === orderItemIds.length) {
            alert('Please select the line item(s) to request shipment for');
            return;
        }

        var select = document.getElementById('source-select');
        var sourceId = select.options[select.selectedIndex].value;
        if (sourceId === 'select-source') {
            alert('Please select a source');
            return;
        }

        buttonHandler._ajaxRequest(
            '/shipment/request?order_id=' + orderId + '&source_id=' + sourceId + '&order_item_ids=' + orderItemIds.join(','),
            '_genericResultHandler'
        );

    },

    /**
     * Function to handle shipment result.
     *
     * @param responseObject
     * @private
     */
    _shipmentResultHandler: function(responseObject) {
        buttonHandler._genericResultHandler(responseObject, "Please select an item.");
    },

    /**
     * General function to handle results.
     *
     * @param responseObject
     * @param message
     * @private
     */
    _genericResultHandler: function(responseObject, message) {
        message = message || "An error occured.";
        if (responseObject.status === buttonHandler.CONST_STATUS_OK) {
            window.location.reload();
        } else {
            alert(message);
        }
    },

    /**
     * Function to handle ajax requests.
     *
     * @param url String
     * @param resultHandler String
     * @private
     */
    _ajaxRequest: function(url, resultHandler = null) {
        var xhr = new XMLHttpRequest();

        xhr.open('GET', url);
        xhr.send(null);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === buttonHandler.CONST_STATE_DONE) {
                if(resultHandler) {
                    buttonHandler[resultHandler](xhr);
                }
            }
        }
    },

    /**
     * Function which will be registered for aggregate snapshot button
     *
     * @param e
     */
    snapshotForAggregate: function(e) {
        e.preventDefault();

        var aggregateId = e.target.getAttribute('data-id'),
            qty = e.target.elements["overwrite-qty"].value,
            mode = e.target.elements["mode"].value;

        buttonHandler._ajaxRequest(
            '/snapshot/aggregate?id=' + aggregateId + '&qty=' + qty + '&mode=' + mode,
            '_genericResultHandler'
        );

    },

    /**
     * Sets the given inventory for all products and sources
     *
     * @param e
     */
    setInventory: function(e) {
        e.preventDefault();

        var quantity = document.getElementById('quantity').value;

        buttonHandler._ajaxRequest(
            'inventory/add?qty=' + quantity,
            '_genericResultHandler'
        )
    },

    /**
     * Sets a random inventory for all products and sources
     *
     * @param e
     */
    setRandomInventory: function(e) {
        e.preventDefault();

        buttonHandler._ajaxRequest(
            'inventory/add?qty=random',
            '_genericResultHandler'
        )
    },

    /**
     * Sets the given inventory for one product and all sources
     *
     * @param e
     */
    setInventoryForProduct: function(e) {
        e.preventDefault();

        var quantity = document.getElementById('quantity').value,
            id = document.getElementById('product-id').getAttribute('data-id');

        buttonHandler._ajaxRequest(
            'inventory/add?qty=' + quantity + '&product_id=' + id,
            '_genericResultHandler'
        )
    },

    /**
     * Sets a random inventory for one product and all sources
     *
     * @param e
     */
    setRandomInventoryForProduct: function(e) {
        e.preventDefault();

        var id = document.getElementById('product-id').getAttribute('data-id');

        buttonHandler._ajaxRequest(
            'inventory/add?qty=random&product_id=' + id,
            '_genericResultHandler'
        )
    }
};

buttonHandler.initial();

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
        'shipment-details-btn': 'requestShipmentLabel'
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

        document.getElementById('aggregate-snapshot-form').addEventListener('submit', self.snapshotForAggregate);
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
        
        buttonHandler._ajaxRequest(
            '/shipment/create?order_id=' + orderId + '&order_item_ids=' + orderItemIds.join(','),
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

        buttonHandler._ajaxRequest(
            '/shipment/labels?order_id=' + orderId + '&order_item_ids=' + orderItemIds.join(','),
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
};

buttonHandler.initial();

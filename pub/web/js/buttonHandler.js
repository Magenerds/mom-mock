/**
 * /**
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
        'shipment-btn': 'shipment'
    },

    /**
     * Inital all events for buttons.
     */
    inital: function() {
        for (var button in this.buttons) {
            var buttonFunction = this.buttons[button],
                elements = document.getElementsByClassName(button);

            for (var i = 0; i < elements.length; i++) {
                elements[i].addEventListener('click', buttonHandler[buttonFunction]);

            }
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

        buttonHandler._ajaxRequest('/rma/approve?rma_id=' + rmaId, '_generecResultHandler');
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
     * Function which will be registered for shipment button.
     *
     * @param e
     */
    shipment: function(e) {
        e.preventDefault();
        var orderId = e.currentTarget.parentNode.getAttribute('data-id'),
            orderItemIds = [];

        var allItems = document.getElementsByClassName('item-select');

        for (var i = 0; i < allItems.length; i++) {
            if(allItems[i].checked) {
                orderItemIds.push(allItems[i].getAttribute('value'));
            }
        }

        buttonHandler._ajaxRequest(
            '/shipment/create?order_id=' + orderId + '&order_item_ids=' + orderItemIds.join(','),
            '_shipmentResultHandler'
        );

    },

    /**
     * Function to handle shipment result.
     *
     * @param responseObject
     * @private
     */
    _shipmentResultHandler: function(responseObject) {
        buttonHandler._generecResultHandler(responseObject, "Please select an item.");
    },

    /**
     * General function to handle results.
     *
     * @param responseObject
     * @param message
     * @private
     */
    _generecResultHandler: function(responseObject, message) {
        message = message || "An error occure.";
        if (responseObject.status === buttonHandler.CONST_STATUS_OK) {
            window.location.reload();
        } else {
            alert(message);
        }
    },

    /**
     * Function to handle ajax requests.
     *
     * @param url
     * @param resultHandler
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
    }
};

buttonHandler.inital();
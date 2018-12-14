
var shippingLabelButtonHandler = {
    CONST_STATUS_OK: 200,
    CONST_STATE_DONE: 4,

    buttons: {
        'shipment-details-btn': 'requestShipmentLabel'
    },

    /**
     * Inital all events for buttons.
     */
    initial: function() {
        for (var button in this.buttons) {
            var buttonFunction = this.buttons[button],
                elements = document.getElementsByClassName(button);

            for (var i = 0; i < elements.length; i++) {
                elements[i].addEventListener('click', shippingLabelButtonHandler[buttonFunction]);

            }
        }
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
        const orderItemIds = [];
        const allItems = document.getElementsByClassName('item-select');

        for (let i = 0; i < allItems.length; i++) {
            if(allItems[i].checked) {
                orderItemIds.push(allItems[i].getAttribute('value'));
            }
        }

        if (0 === orderItemIds.length) {
            alert('Please select the line item(s) to request shipping label(s) for');
            return;
        }
        
        shippingLabelButtonHandler._ajaxRequest(
            '/shipment/labels?order_id=' + orderId + '&order_item_ids=' + orderItemIds.join(','),
            '_shipmentDetailsResultHandler'
        );

    },

    /**
     * Function to handle shipment details result.
     *
     * @param responseObject
     * @private
     */
    _shipmentDetailsResultHandler: function(responseObject) {
        if (responseObject.status === shippingLabelButtonHandler.CONST_STATUS_OK) {
            window.location.reload();
        } else {
            alert("That didn't work o_O");
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
            if (xhr.readyState === shippingLabelButtonHandler.CONST_STATE_DONE) {
                if(resultHandler) {
                    shippingLabelButtonHandler[resultHandler](xhr);
                }
            }
        }
    }
};

shippingLabelButtonHandler.initial();

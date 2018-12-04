<?php
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
namespace MomMock\Method\Postsales\ReturnManagement;

use MomMock\Method\Postsales\AbstractUpdated;

/**
 * Class Updated
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Updated extends AbstractUpdated
{

    /**
     * Send data to registered integrations
     *
     * @param $data
     * @return mixed
     */
    public function send($data)
    {
        $rmaId = $data['rma_id'];

        $rma = $this->getRmaById($rmaId);
        $rmaItems = $this->getRmaItemsByRmaId($rmaId);

        // insert order data to updated template
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        $rma['date'] = date('c');

        // insert order data
        foreach ($rma as $key => $value) {
            $template = str_replace(sprintf('{{rma.%s}}', $key), $value, $template);
        }

        // insert order item data
        $updatedData = json_decode($template, true);

        $lines = [];

        foreach ($rmaItems as $rmaItem) {
            $lineTemplate = json_encode($updatedData['return']['lines'], true);

            foreach ($rmaItem as $key => $value) {
                $lineTemplate = str_replace(sprintf('{{rma_item.%s}}', $key), $value, $lineTemplate);
            }

            $lines = array_merge($lines, json_decode($lineTemplate, true));
        }

        $updatedData['return']['lines'] = $lines;
        $result = $this->rpcClient->send($updatedData, $method);

        $this->setRmaStatus($rmaId);
        $this->setRmaItemStatus($rmaItems);

        return $result;
    }
}
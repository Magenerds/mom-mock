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

namespace MomMock\Helper;

use Doctrine\DBAL\Connection;
use MomMock\Entity\Journal\Request as JournalRequest;

/**
 * Class RpcClient
 * @package MomMock\Helper
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class RpcClient
{
    /**
     * Holds the rpc communication meta data
     */
    const META_DATA = [
        "jsonrpc" => "2.0",
        "id" => null,
        "method" => null,
        "params" => null
    ];

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var JournalRequest
     */
    private $apiJournal;

    /**
     * RpcClient constructor.
     * @param Connection $db
     */
    public function __construct(
        Connection $db
    ){
        $this->db = $db;
        $this->apiJournal = new JournalRequest($this->db);
    }

    /**
     * Sends an rpc request with given parameters and method
     *
     * @param [] $params
     * @param string $method
     * @return mixed
     */
    public function send(array $params, string $method)
    {
        $data = self::META_DATA;
        $data['id'] = uniqid(null, true);
        $data['method'] = $method;
        $data['params'] = $params;

        $integration = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`integration`')
            ->execute()
            ->fetch();

        $url = $integration['url'];
        $secret = $integration['secret'];

        $hash = 'sha1=' . hash_hmac(
            'sha1',
            json_encode($data, true),
            $secret
        );

        $result = null;

        $exceptionMessage = null;
        try {
            $result = $this->sendRequest(json_encode($data, true), $hash, $url);
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        } finally {
            $status = $exceptionMessage === null ? JournalRequest::STATUS_SUCCESS : JournalRequest::STATUS_ERROR;

            $this->apiJournal->logRequest(
                $data,
                $status,
                JournalRequest::DIRECTION_OUTGOING,
                $integration['id'],
                $exceptionMessage
            );
        }

        return $result;
    }

    /**
     * Sends a rpc request via curl
     *
     * @param $data
     * @param $hash
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    protected function sendRequest($data, $hash, $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'X-Signature: ' . $hash
        ]);

        $result = curl_exec($ch);

        if (curl_error($ch)) {
            throw new \Exception(sprintf('Error during rpc request to %s with error: %s', $url, curl_error($ch)));
        }

        curl_close($ch);

        return $result;
    }
}

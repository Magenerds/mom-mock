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
        "id" => 1,
        "method" => null,
        "params" => null
    ];

    /**
     * @var Connection
     */
    private $db;

    /**
     * RpcClient constructor.
     * @param Connection $db
     */
    public function __construct(
        Connection $db
    ){
        $this->db = $db;
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
        $data['method'] = $method;
        $data['params'] = $params;

        $data = json_encode($data, true);

        $integration = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`integration`')
            ->execute()
            ->fetch();

        $url = $integration['url'];
        $secret = $integration['secret'];

        $hash = 'sha1=' . hash_hmac(
            'sha1',
            $data,
            $secret
        );

        return $this->sendRequest($data, $hash, $url);
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
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
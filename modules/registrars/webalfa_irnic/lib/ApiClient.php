<?php

namespace WHMCS\Module\Registrar\WebalfaIrnic;

class ApiClient
{
    private $api_key;

    function __construct($api_key)
    {
        $this->api_key = $api_key;
    }

    function callAPI($method, $url, $post_body = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.webalfa.net/v2" . $url);

        if ($post_body != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_body));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURL_HTTP_VERSION_1_1, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "content-type: application/json",
            "x-api-key: " . $this->api_key
        ));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        curl_close($ch);

        $results = json_decode($response, true);

        logModuleCall(
            'webalfa_irnic',
            $url,
            $post_body,
            $response,
            $results,
            array(
                $post_body['api_key']
            )
        );

        if ($results === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new AppException('Bad response received from API');
        }

        if (isset($results['success']) && $results['success'] == false) {
            if (count($results['errors']) > 0) {
                $error = current($results['errors']);

                if (isset($error['extra']))
                    throw new AppException($error['message'], $error['code'], $error['extra']);
                else
                    throw new AppException($error['message'], $error['code']);
            } else {
                throw new AppException('Abnormal error');
            }
        }

        return $results;
    }

}


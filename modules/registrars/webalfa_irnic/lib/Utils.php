<?php

namespace WHMCS\Module\Registrar\WebalfaIrnic;

use WHMCS\Database\Capsule;

class Utils
{

    public static function getContactInfo($contact)
    {
        static $contactInfo = [];

        if (!isset($contactInfo[$contact])) {
            $api_key = self::getParam('APIKey');

            $api_client = new ApiClient($api_key);
            $api_result = $api_client->callAPI('GET', '/contacts/' . $contact);

            $contactInfo[$contact] = $api_result['result'];

            return $contactInfo[$contact];
        } else {
            return $contactInfo[$contact];
        }
    }

    public static function getParam($param)
    {
        $params = self::getParams();

        if (is_array($params))
            return $params[$param];
        else
            return false;
    }

    public static function getParams()
    {
        static $params = null;

        if ($params != null) {
            return $params;
        }

        $pdo = Capsule::connection()->getPdo();

        $statement = $pdo->prepare(
            "SELECT `setting`, `value` FROM `tblregistrars` WHERE registrar = 'webalfa_irnic'"
        );
        $statement->execute();
        $result = $statement->fetchAll();

        foreach ($result as $param) {
            $params[$param['setting']] = decrypt($param['value']);
        }

        return $params;

    }

}

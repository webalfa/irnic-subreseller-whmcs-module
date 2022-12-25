<?php

/**
 * WebAlfa IRNIC Reseller Module
 **/

use WHMCS\Module\Registrar\WebalfaIrnic\ApiClient;
use WHMCS\Module\Registrar\WebalfaIrnic\AppException;

function webalfa_irnic_MetaData()
{
    return array(
        'DisplayName' => 'WebAlfa IRNIC Reseller Module',
        'APIVersion' => '2.0',
    );
}

function webalfa_irnic_getConfigArray()
{
    $configarray = array(
        'APIKey' => array(
            'Type' => 'text', 'Size' => '36', 'Description' => 'Enter API Key here',
        ),
        'ResellerBillingHandle' => array(
            'Type' => 'text', 'Size' => '36', 'Description' => 'Enter Reseller Billing Handle here',
        ),
    );

    return $configarray;
}

function webalfa_irnic_RegisterDomain($params)
{
    try {
        $domain = $params['sld'] . '.' . $params['tld'];
        $period = (int)$params['regperiod'];

        $api_client = new ApiClient($params['APIKey']);
        $api_client->callAPI('POST', '/domains/register', array(
            'name' => $domain,
            'period' => $period,
            'contacts' => array(
                'holder' => $params['additionalfields']['holder_handle'],
                'admin' => $params['additionalfields']['admin_handle'],
                'tech' => $params['additionalfields']['tech_handle'],
                'bill' => $params['ResellerBillingHandle']
            ),
            'nameservers' => array(
                $params['ns1'],
                $params['ns2'],
                $params['ns3'],
                $params['ns4']
            )
        ));

        return array(
            'success' => true,
        );

    } catch (AppException $e) {
        return array(
            'error' => AppException::parseException($e)
        );
    }
}

function webalfa_irnic_RenewDomain($params)
{
    try {
        $domain = $params['sld'] . '.' . $params['tld'];
        $period = (int)$params['regperiod'];

        $api_client = new ApiClient($params['APIKey']);
        $api_client->callAPI('POST', '/domains/' . $domain . '/renew', array(
            'period' => $period,
        ));

        return array(
            'success' => true,
        );

    } catch (AppException $e) {
        return array(
            'error' => AppException::parseException($e)
        );
    }
}


function webalfa_irnic_TransferDomain($params)
{
    try {
        $domain = $params['sld'] . '.' . $params['tld'];
        $period = (int)$params['regperiod'];

        $api_client = new ApiClient($params['APIKey']);
        $api_client->callAPI('POST', '/domains/transfer', array(
            'name' => $domain,
            'period' => $period,
        ));

        return array(
            'success' => true,
        );

    } catch (AppException $e) {
        return array(
            'error' => AppException::parseException($e)
        );
    }
}

function webalfa_irnic_GetNameservers($params)
{

    try {
        $domain = $params['sld'] . '.' . $params['tld'];

        $api_client = new ApiClient($params['APIKey']);
        $result = $api_client->callAPI('GET', '/domains/' . $domain);
        $result = $result['result'];

        $values = array(
            'success' => true,
        );

        for ($i = 0; $i < count($result['nameservers']); $i++) {

            if ($result['nameservers'][$i]['hostname'])
                $values['ns' . ($i + 1)] = implode('|', $result['nameservers'][$i]);
        }

        return $values;

    } catch (AppException $e) {
        return array(
            'error' => $e->getMessage()
        );
    }

}

function webalfa_irnic_SaveNameservers($params)
{
    try {
        $domain = $params['sld'] . '.' . $params['tld'];

        $api_client = new ApiClient($params['APIKey']);

        $ns1 = explode('|', $params["ns1"]);
        $ns2 = explode('|', $params["ns2"]);
        $ns3 = explode('|', $params["ns3"]);
        $ns4 = explode('|', $params["ns4"]);

        $nameserver1_host = @$ns1[0];
        $nameserver2_host = @$ns2[0];
        $nameserver3_host = @$ns3[0];
        $nameserver4_host = @$ns4[0];

        $nameserver1_ip = @$ns1[1];
        $nameserver2_ip = @$ns2[1];
        $nameserver3_ip = @$ns3[1];
        $nameserver4_ip = @$ns4[1];

        $nameserver1 = ['hostname' => @$nameserver1_host, 'ip' => @$nameserver1_ip];
        $nameserver2 = ['hostname' => @$nameserver2_host, 'ip' => @$nameserver2_ip];
        $nameserver3 = ['hostname' => @$nameserver3_host, 'ip' => @$nameserver3_ip];
        $nameserver4 = ['hostname' => @$nameserver4_host, 'ip' => @$nameserver4_ip];


        $result = $api_client->callAPI('POST', '/domains/' . $domain, array(
            'nameservers' => array(
                $nameserver1,
                $nameserver2,
                $nameserver3,
                $nameserver4
            )
        ));

        return array(
            'success' => true,
        );

    } catch (AppException $e) {
        return array(
            'error' => $e->getMessage()
        );
    }
}


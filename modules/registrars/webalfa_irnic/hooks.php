<?php

use WHMCS\Module\Registrar\WebalfaIrnic\Utils;
use WHMCS\Module\Registrar\WebalfaIrnic\ApiClient;
use WHMCS\Module\Registrar\WebalfaIrnic\AppException;

webalfa_irnic_registrar_loadLanguage();

function webalfa_irnic_registrar_loadLanguage()
{
    global $_LANG;

    $language = explode('_', $_LANG['locale']);

    switch ($language[0]) {
        default:
            $lang = 'english';
            break;
        case 'fa':
            $lang = 'farsi';
            break;
    }

    $lang_file = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php';

    if (file_exists($lang_file))
        include_once($lang_file);

}


function webalfa_irnic_cart($vars)
{
    global $_LANG;

    $errors = [];

    $webalfa_irnic_flag = false;
    foreach ($_SESSION['cart']['domains'] as $object) {
        if ((substr($object['domain'], -3) == '.ir' || substr($object['domain'], -6) == '.ایران')) {
            $webalfa_irnic_flag = true;
        }
    }

    if ($webalfa_irnic_flag) {
        $hour = (int)date('H');
        $minute = (int)date('i');

        if (($hour == 23 && $minute >= 0) || ($hour == 0 && $minute <= 30)) {
            $errors = array();
            $errors[] = $_LANG["WEBALFA_IRNIC_NIGHT"];

            return $errors;
        }
    }


    $i = 0;
    foreach ($_SESSION['cart']['domains'] as $objectIndex => $object) {

        if ((substr($object['domain'], -3) == '.ir' || substr($object['domain'], -6) == '.ایران')) {

            if ($object['type'] == 'register') {

                // Known roles - Just for safety
                $rolls = ['holder', 'admin', 'tech'];
                $resellerContact = Utils::getParam('ResellerBillingHandle');

                if (isset($object['fields']))
                    foreach ($object['fields'] as $roll => $contact) {
                        $safe_role = $rolls[$roll];

                        // set empty handel
                        if (empty($contact) && $safe_role != 'holder') {
                            $_SESSION['cart']['domains'][$objectIndex]['fields'][$roll] = $resellerContact;
                            $contact = $resellerContact;
                        }


                        if (!empty($contact) && $safe_role == 'holder' && $contact == $resellerContact) {
                            $errors[] = sprintf($_LANG["WEBALFA_IRNIC_ERROR_FOR_SET_RESELLER_AS_HOLDER"], $object['domain'], $contact, $_LANG["WEBALFA_IRNIC_HANDLE_" . strtoupper($safe_role)]);
                        }

                        if (!empty($contact)) {

                            try {
                                $contactInfo = Utils::getContactInfo($contact);

                                if (!$contactInfo['relations'][$safe_role])
                                    $errors[] = sprintf($_LANG["WEBALFA_IRNIC_HANDLE_NOT_ALLOWED_FOR_CONTACT_DESC"], $object['domain'], $contact, $_LANG["WEBALFA_IRNIC_HANDLE_" . strtoupper($safe_role)]);

                            } catch (Exception $e) {
                                if (isset($_LANG['WEBALFA_IRNIC_MSG_CODE_' . $e->getCode()]))
                                    $errors[] = $_LANG['WEBALFA_IRNIC_MSG_CODE_' . $e->getCode()] . ' (' . $contact . ')';
                                else
                                    $errors[] = $e->getMessage();
                            }

                        } else {
                            $errors[] = $_LANG['WEBALFA_IRNIC_HANDLE_' . strtoupper($safe_role)] . ' ' . $_LANG['clientareaerrorisrequired'] . ' (' . $object['domain'] . ')';
                        }

                    }
                else {
                    header("Location: cart.php?a=confdomains");
                    die();
                }
            }

        }

        $i++;
    }


    return $errors;

}

add_hook('ShoppingCartValidateCheckout', 1, 'webalfa_irnic_cart');
add_hook('ShoppingCartValidateDomainsConfig', 1, 'webalfa_irnic_cart');



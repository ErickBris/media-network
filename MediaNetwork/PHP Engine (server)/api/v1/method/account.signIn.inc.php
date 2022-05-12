<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $clientId = helper::clearInt($clientId);

    $fcm_regId = helper::clearText($fcm_regId);
    $username = helper::clearText($username);
    $password = helper::clearText($password);

    $fcm_regId = helper::escapeText($fcm_regId);
    $username = helper::escapeText($username);
    $password = helper::escapeText($password);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $access_data = array();

    $account = new account($dbo);
    $access_data = $account->signin($username, $password);

    unset($account);

    if ($access_data["error"] === false) {

        $account = new account($dbo, $access_data['accountId']);

        switch ($access_data['account_state']) {

            case ACCOUNT_STATE_BLOCKED: {

                break;
            }

            default: {

                $auth = new auth($dbo);
                $access_data = $auth->create($access_data['accountId'], $clientId);

                if ($access_data['error'] === false) {

                    $account->setState(ACCOUNT_STATE_ENABLED);
                    $account->setLastActive();
                    $access_data['account'] = array();

                    array_push($access_data['account'], $account->get());

                    if (strlen($fcm_regId) != 0) {

                        $account->setGCM_regId($fcm_regId);
                    }
                }

                break;
            }
        }
    }

    echo json_encode($access_data);
    exit;
}

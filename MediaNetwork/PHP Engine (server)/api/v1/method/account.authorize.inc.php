<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $accessToken = helper::clearText($accessToken);
    $accessToken = helper::escapeText($accessToken);

    $settings = new settings($dbo);
    $settings->setRequestFrom($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        $result = array("error" => true,
                        "error_code" => ERROR_ACCESS_TOKEN,
                        "error_description" => "Error authorization.");

        $result['settings'] = $settings->get();

        echo json_encode($result);
        exit;
    }

    $account = new account($dbo, $accountId);
    $account->setLastActive();

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "accessToken" => $accessToken,
                    "accountId" => $accountId,
                    "account" => array());

    if (strlen($fcm_regId) != 0) {

        $account->setGCM_regId($fcm_regId);
    }

    array_push($result['account'], $account->get());

    $result['settings'] = $settings->get();

    echo json_encode($result);
    exit;
}

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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;

    $notifyId = isset($_POST['notifyId']) ? $_POST['notifyId'] : 0;

    $gcmRegId = isset($_POST['gcmRegId']) ? $_POST['gcmRegId'] : "";

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $notifyId = helper::clearInt($notifyId);

    $result = array("error" => false,
                    "gcmRegId" => $gcmRegId,
                    "error_code" => ERROR_UNKNOWN);

    $profileId = $chatFromUserId;

    if ($profileId == $accountId) {

        if (strlen($gcmRegId) > 0) {

            $fcm = new fcm($dbo, 0);
            $fcm->setAccountId($chatToUserId);
            $fcm->addDeviceId($gcmRegId);
            $fcm->setData($notifyId, GCM_MESSAGE_ONLY_FOR_PERSONAL_USER, "Seen", 0, $chatId);
            $fcm->send();

        } else {

            $fcm = new fcm($dbo, $chatToUserId);
            $fcm->setData($notifyId, GCM_MESSAGE_ONLY_FOR_PERSONAL_USER, "Seen", 0, $chatId);
            $fcm->send();
        }

    } else {

        if (strlen($gcmRegId) > 0) {

            $fcm = new fcm($dbo, 0);
            $fcm->setAccountId($chatFromUserId);
            $fcm->addDeviceId($gcmRegId);
            $fcm->setData($notifyId, GCM_MESSAGE_ONLY_FOR_PERSONAL_USER, "Seen", 0, $chatId);
            $fcm->send();

        } else {

            $fcm = new fcm($dbo, $chatFromUserId);
            $fcm->setData($notifyId, GCM_MESSAGE_ONLY_FOR_PERSONAL_USER, "Seen", 0, $chatId);
            $fcm->send();
        }
    }

    echo json_encode($result);
    exit;
}

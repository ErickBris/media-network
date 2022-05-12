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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);
    $accountInfo = $account->get();
    unset($account);

    // Get new messages count\

    $messages_count = 0;

    if (APP_MESSAGES_COUNTERS) {

        $msg = new msg($dbo);
        $msg->setRequestFrom($accountId);

        $messages_count = $msg->getNewMessagesCount();

        unset($msg);
    }

    $result['messagesCount'] = $messages_count;

    // Get new friends count

    $friends_count = 0;

    $friends = new friends($dbo);
    $friends->setRequestFrom($accountId);

    $friends_count = $friends->getNewCount($accountInfo['last_friends_view']);

    unset($friends);

    $result['friendsCount'] = $friends_count;

    // Get new notifications count

    $notifications_count = 0;

    $notifications = new notify($dbo);
    $notifications->setRequestFrom($accountId);

    $notifications_count = $notifications->getNewCount($accountInfo['last_notifications_view']);

    unset($notifications);

    $result['notificationsCount'] = $notifications_count;

    echo json_encode($result);
    exit;
}

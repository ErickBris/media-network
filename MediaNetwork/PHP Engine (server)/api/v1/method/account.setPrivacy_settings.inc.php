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

    $allowMessagesFromAnyone = isset($_POST['allowMessagesFromAnyone']) ? $_POST['allowMessagesFromAnyone'] : 0;
    $allowGalleryComments = isset($_POST['allowGalleryComments']) ? $_POST['allowGalleryComments'] : 0;
    $allowShowProfileOnlyToFriends = isset($_POST['allowShowProfileOnlyToFriends']) ? $_POST['allowShowProfileOnlyToFriends'] : 0;
    $allowShowOnline = isset($_POST['allowShowOnline']) ? $_POST['allowShowOnline'] : 0;
    $allowShowPhoneNumber = isset($_POST['allowShowPhoneNumber']) ? $_POST['allowShowPhoneNumber'] : 0;

    $allowMessagesFromAnyone = helper::clearInt($allowMessagesFromAnyone);
    $allowGalleryComments = helper::clearInt($allowGalleryComments);
    $allowShowProfileOnlyToFriends = helper::clearInt($allowShowProfileOnlyToFriends);
    $allowShowOnline = helper::clearInt($allowShowOnline);
    $allowShowPhoneNumber = helper::clearInt($allowShowPhoneNumber);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);

    $account->setPrivacy_settings($allowMessagesFromAnyone, $allowGalleryComments, $allowShowProfileOnlyToFriends, $allowShowOnline, $allowShowPhoneNumber);

    $result = $account->getPrivacy_settings();

    echo json_encode($result);
    exit;
}

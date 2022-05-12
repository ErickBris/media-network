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

    $allowMessagesGCM = isset($_POST['allowMessagesGCM']) ? $_POST['allowMessagesGCM'] : 0;
    $allowLikesGCM = isset($_POST['allowLikesGCM']) ? $_POST['allowLikesGCM'] : 0;
    $allowCommentsGCM = isset($_POST['allowCommentsGCM']) ? $_POST['allowCommentsGCM'] : 0;
    $allowCommentReplyGCM = isset($_POST['allowCommentReplyGCM']) ? $_POST['allowCommentReplyGCM'] : 0;
    $allowFriendsRequestsGCM = isset($_POST['allowFriendsRequestsGCM']) ? $_POST['allowFriendsRequestsGCM'] : 0;

    $allowMessagesGCM = helper::clearInt($allowMessagesGCM);
    $allowLikesGCM = helper::clearInt($allowLikesGCM);
    $allowCommentsGCM = helper::clearInt($allowCommentsGCM);
    $allowCommentReplyGCM = helper::clearInt($allowCommentReplyGCM);
    $allowFriendsRequestsGCM = helper::clearInt($allowFriendsRequestsGCM);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);

    $account->setGCM_settings($allowMessagesGCM, $allowLikesGCM, $allowCommentsGCM, $allowCommentReplyGCM, $allowFriendsRequestsGCM);

    $result = $account->getGCM_settings();

    echo json_encode($result);
    exit;
}

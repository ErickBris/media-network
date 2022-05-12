<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
    $itemFromUserId = isset($_POST['itemFromUserId']) ? $_POST['itemFromUserId'] : 0;
    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;

    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : '';

    $replyToUserId = isset($_POST['replyToUserId']) ? $_POST['replyToUserId'] : 0;

    $accountId = helper::clearInt($accountId);

    $accessToken = helper::clearText($accessToken);
    $accessToken = helper::escapeText($accessToken);

    $itemId = helper::clearInt($itemId);
    $itemFromUserId = helper::clearInt($itemFromUserId);
    $itemType = helper::clearInt($itemType);

    $content = helper::clearText($content);

    $content = preg_replace( "/[\r\n]+/", " ", $content); //replace all new lines to one new line
    $content  = preg_replace('/\s+/', ' ', $content);        //replace all white spaces to one space

    $content = helper::escapeText($content);

    $imgUrl = helper::clearText($imgUrl);
    $imgUrl = helper::escapeText($imgUrl);

    $replyToUserId = helper::clearInt($replyToUserId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $blacklist = new blacklist($dbo);
    $blacklist->setRequestFrom($itemFromUserId);

    if (!$blacklist->isExists($accountId)) {

        $comment = new comments($dbo);
        $comment->setRequestFrom($accountId);

        $result = $comment->add($itemId, $itemFromUserId, $itemType, $content, $imgUrl, $replyToUserId);

        unset($comment);
    }

    echo json_encode($result);
    exit;
}

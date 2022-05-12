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

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;
    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $profileId = helper::clearInt($profileId);
    $itemId = helper::clearInt($itemId);

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom($accountId);

    $itemInfo = $gallery->info($itemId);

    if ($itemInfo['error'] === false && $itemInfo['removeAt'] == 0) {

        $comments = new comments($dbo);
        $comments->setRequestFrom($accountId);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "comments" => $comments->get($itemId, 0),
                        "items" => array());

        array_push($result['items'], $itemInfo);

        unset($comments);
    }

    unset($itemInfo);
    unset($gallery);

    echo json_encode($result);
    exit;
}

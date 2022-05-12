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

    $query = isset($_POST['query']) ? $_POST['query'] : '';
    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $gender = isset($_POST['gender']) ? $_POST['gender'] : -1;
    $online = isset($_POST['online']) ? $_POST['online'] : -1;

    $query = helper::clearText($query);
    $query = helper::escapeText($query);

    $itemId = helper::clearInt($itemId);

    if ($gender != -1) $gender = helper::clearInt($gender);
    if ($online != -1) $online = helper::clearInt($online);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $search = new search($dbo);
    $search->setRequestFrom($accountId);

    $result = $search->query($query, $itemId, $gender, $online);

    echo json_encode($result);
    exit;
}

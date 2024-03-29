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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $reason = isset($_POST['reason']) ? $_POST['reason'] : 0;

    $profileId = helper::clearInt($profileId);

    $reason = helper::clearInt($reason);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);


    $profile = new profile($dbo, $profileId);
    $profile->setRequestFrom($accountId);

    if ($reason >= 0 && $reason < 4) {

        $result = $profile->reportAbuse($reason);
    }

    echo json_encode($result);
    exit;
}

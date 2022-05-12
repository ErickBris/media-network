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

    $showInStream = isset($_POST['showInStream']) ? $_POST['showInStream'] : 1;

    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;

    $desc = isset($_POST['desc']) ? $_POST['desc'] : "";
    $originImgUrl = isset($_POST['originImgUrl']) ? $_POST['originImgUrl'] : "";
    $previewImgUrl = isset($_POST['previewImgUrl']) ? $_POST['previewImgUrl'] : "";
    $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : "";

    $videoUrl = isset($_POST['videoUrl']) ? $_POST['videoUrl'] : "";

    $itemArea = isset($_POST['itemArea']) ? $_POST['itemArea'] : '';
    $itemCountry = isset($_POST['itemCountry']) ? $_POST['itemCountry'] : '';
    $itemCity = isset($_POST['itemCity']) ? $_POST['itemCity'] : '';
    $itemLat = isset($_POST['itemLat']) ? $_POST['itemLat'] : '0.000000';
    $itemLng = isset($_POST['itemLng']) ? $_POST['itemLng'] : '0.000000';

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $showInStream = helper::clearInt($showInStream);
    $itemType = helper::clearInt($itemType);

    $desc = helper::clearText($desc);

    $desc = preg_replace( "/[\r\n]+/", "<br>", $desc); //replace all new lines to one new line
    $desc  = preg_replace('/\s+/', ' ', $desc);        //replace all white spaces to one space

    $desc = helper::escapeText($desc);

    $originImgUrl = helper::clearText($originImgUrl);
    $originImgUrl = helper::escapeText($originImgUrl);

    $previewImgUrl = helper::clearText($previewImgUrl);
    $previewImgUrl = helper::escapeText($previewImgUrl);

    $imgUrl = helper::clearText($imgUrl);
    $imgUrl = helper::escapeText($imgUrl);

    $videoUrl = helper::clearText($videoUrl);
    $videoUrl = helper::escapeText($videoUrl);

    $itemArea = helper::clearText($itemArea);
    $itemArea = helper::escapeText($itemArea);

    $itemCountry = helper::clearText($itemCountry);
    $itemCountry = helper::escapeText($itemCountry);

    $itemCity = helper::clearText($itemCity);
    $itemCity = helper::escapeText($itemCity);

    $itemLat = helper::clearText($itemLat);
    $itemLat = helper::escapeText($itemLat);

    $itemLng = helper::clearText($itemLng);
    $itemLng = helper::escapeText($itemLng);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom($accountId);

    $result = $gallery->add($showInStream, $desc, $videoUrl, $imgUrl, $originImgUrl, $previewImgUrl, $itemArea, $itemCountry, $itemCity, $itemLat, $itemLng);

    echo json_encode($result);
    exit;
}

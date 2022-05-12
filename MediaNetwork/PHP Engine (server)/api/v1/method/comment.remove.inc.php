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
    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;

    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);
    $itemType = helper::clearInt($itemType);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $comments = new comments($dbo);
    $comments->setRequestFrom($accountId);

    $commentInfo = $comments->info($itemId);

    if ($commentInfo['fromUserId'] == $accountId) {

        $comments->remove($itemId);

    } else {

        switch ($itemType) {

            case ITEM_TYPE_GALLERY_ITEM: {

                $gallery = new gallery($dbo);
                $gallery->setRequestFrom($accountId);

                $itemInfo = $gallery->info($commentInfo['itemId']);

                if ($itemInfo['fromUserId'] != 0 && $itemInfo['fromUserId'] == $accountId) {

                    $comments->remove($itemId);
                }

                unset($gallery);

                break;
            }

            default: {

                break;
            }
        }
    }

    unset($comments);


    switch ($itemType) {

        case ITEM_TYPE_GALLERY_ITEM: {

            $gallery = new gallery($dbo);
            $gallery->setRequestFrom($accountId);

            $gallery->recalculate($commentInfo['itemId']);

            unset($gallery);

            break;
        }

        default: {

            break;
        }
    }

    echo json_encode($result);
    exit;
}

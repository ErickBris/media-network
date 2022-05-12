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

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
    }

    $admin = new admin($dbo);


    if (isset($_GET['id'])) {

        $itemId = isset($_GET['id']) ? $_GET['id'] : 0;
        $requestFrom = isset($_GET['requestFrom']) ? $_GET['requestFrom'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';

        $itemId = helper::clearInt($itemId);
        $requestFrom = helper::clearInt($requestFrom);

        if ($accessToken === admin::getAccessToken() && !APP_DEMO) {

            $item = new gallery($dbo);
            $item->setRequestFrom($requestFrom);
            $item->remove($itemId);
            unset($item);
        }

        header("Location: /admin/gallery.php");
        exit;

    } else {

        header("Location: /admin/gallery.php");
        exit;
    }

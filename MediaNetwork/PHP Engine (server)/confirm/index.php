<?php

    /*!
     * ifsoft engine v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk, qascript@mail.ru
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (isset($_GET['hash'])) {

        $hash = isset($_GET['hash']) ? $_GET['hash'] : '';

        $hash = helper::clearText($hash);
        $hash = helper::escapeText($hash);

        $email = new email($dbo);

        $confirmPointInfo = $email->getConfirmPoint($hash);

        if ($confirmPointInfo['error'] !== false) {

            header("Location: /");

        } else {

            $email->setRequestFrom($confirmPointInfo['accountId']);
            $email->confirmPointRemove();

            unset($email);

            $account = new account($dbo, $confirmPointInfo['accountId']);
            $account->setEmailVerified(1);
            unset($account);

            header("Location: /confirm/success");
        }

    } else {

        header("Location: /");

    }

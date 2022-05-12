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

    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $facebookId = isset($_POST['facebookId']) ? $_POST['facebookId'] : '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $language = isset($_POST['language']) ? $_POST['language'] : '';

    $user_sex = isset($_POST['sex']) ? $_POST['sex'] : 0;
    $user_year = isset($_POST['year']) ? $_POST['year'] : 0;
    $user_month = isset($_POST['month']) ? $_POST['month'] : 0;
    $user_day = isset($_POST['day']) ? $_POST['day'] : 0;

    $user_sex = helper::clearInt($user_sex);
    $user_year = helper::clearInt($user_year);
    $user_month = helper::clearInt($user_month);
    $user_day = helper::clearInt($user_day);

    $clientId = helper::clearInt($clientId);

    $facebookId = helper::clearText($facebookId);

    $fcm_regId = helper::clearText($fcm_regId);
    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $language = helper::clearText($language);

    $facebookId = helper::escapeText($facebookId);
    $fcm_regId = helper::escapeText($fcm_regId);
    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);
    $language = helper::escapeText($language);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $result = array("error" => true);

    $account = new account($dbo);
    $result = $account->signup($username, $fullname, $password, $email, $user_sex, $user_year, $user_month, $user_day, $language);
    unset($account);

    if ($result['error'] === false) {

        $account = new account($dbo);
        $account->setState(ACCOUNT_STATE_ENABLED);
        $account->setLastActive();
        $result = $account->signin($username, $password);
        unset($account);

        if ($result['error'] === false) {

            $auth = new auth($dbo);
            $result = $auth->create($result['accountId'], $clientId);

            if ($result['error'] === false) {

                $account = new account($dbo, $result['accountId']);

                if (strlen($facebookId) != 0) {

                    $helper = new helper($dbo);

                    if ($helper->getUserIdByFacebook($facebookId) == 0) {

                        $account->setFacebookId($facebookId);
                    }

                } else {

                    $account->setFacebookId("");
                }

                if (strlen($fcm_regId) != 0) {

                    $account->setGCM_regId($fcm_regId);
                }

                if (APP_SEND_SIGNUP_CONFIRM_EMAIL) {

                    $c_email = new email($dbo);
                    $c_email->setRequestFrom($result['accountId']);

                    $c_email->sendConfirmRequest($email, $clientId);

                    unset($c_email);
                }

                $result['account'] = array();

                array_push($result['account'], $account->get());
            }
        }
    }

    echo json_encode($result);
    exit;
}

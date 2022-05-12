<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class search extends db_connect
{

    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    private function getCount($queryText, $gender = -1, $online = -1)
    {
        $queryText = "%".$queryText."%";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $sql = "SELECT count(*) FROM users WHERE account_state = 0 AND (username LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}')".$genderSql.$onlineSql;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }

    public function query($queryText = '', $itemId = 0, $gender = -1, $online = -1)
    {
        $originQuery = $queryText;

        if ($itemId == 0) {

            $itemId = $this->lastIndex();
            $itemId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $users = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "itemCount" => $this->getCount($originQuery, $gender, $online),
                       "itemId" => $itemId,
                       "query" => $originQuery,
                       "items" => array());

        $queryText = "%".$queryText."%";

        $sql = "SELECT id, regtime FROM users WHERE account_state = 0 AND (username LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}') AND id < {$itemId}".$genderSql.$onlineSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($users['items'], $profile->get());

                    $users['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $users;
    }

    public function preload($itemId = 0, $gender = -1, $online = -1)
    {
        if ($itemId == 0) {

            $itemId = $this->lastIndex();
            $itemId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemCount" => $this->getCount("", $gender, $online),
                        "itemId" => $itemId,
                        "items" => array());

        $sql = "SELECT id, regtime FROM users WHERE account_state = 0 AND id < {$itemId}".$genderSql.$onlineSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($result['items'], $profile->get());

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}


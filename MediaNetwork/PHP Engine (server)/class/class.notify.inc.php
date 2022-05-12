<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class notify extends db_connect
{
    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {

        parent::__construct($dbo);
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM notifications");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function get($itemId = 0)
    {

        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE notifyToId = (:notifyToId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':notifyToId', $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $time = new language($this->db, $this->language);

                    if ($row['notifyFromId'] == 0) {

                        $profileInfo = array("id" => 0,
                                             "state" => 0,
                                             "username" => "",
                                             "fullname" => "",
                                             "lowPhotoUrl" => "/img/profile_default_photo.png");

                    } else {

                        $profile = new profile($this->db, $row['notifyFromId']);
                        $profileInfo = $profile->get();
                        unset($profile);
                    }

                    $itemInfo = array("id" => $row['id'],
                                      "notifyType" => $row['notifyType'],
                                      "notifyToItemType" => $row['notifyToItemType'],
                                      "notifyToItemId" => $row['notifyToItemId'],
                                      "itemId" => $row['itemId'],
                                      "fromUserId" => $profileInfo['id'],
                                      "fromUserState" => $profileInfo['account_state'],
                                      "fromUserUsername" => $profileInfo['username'],
                                      "fromUserFullname" => $profileInfo['fullname'],
                                      "fromUserPhotoUrl" => $profileInfo['photoUrl'],
                                      "createAt" => $row['createAt'],
                                      "timeAgo" => $time->timeAgo($row['createAt']));

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $row['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    public function createNotify($notifyToId, $notifyToItemId, $notifyFromId, $notifyType, $notifyToItemType, $itemId = 0)
    {
        $createAt = time();

        $stmt = $this->db->prepare("INSERT INTO notifications (notifyToId, notifyToItemId, notifyFromId, notifyType, notifyToItemType, itemId, createAt) value (:notifyToId, :notifyToItemId, :notifyFromId, :notifyType, :notifyToItemType, :itemId, :createAt)");
        $stmt->bindParam(":notifyToId", $notifyToId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyToItemId", $notifyToItemId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyFromId", $notifyFromId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyType", $notifyType, PDO::PARAM_INT);
        $stmt->bindParam(":notifyToItemType", $notifyToItemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function remove($itemId)
    {
        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE notifications SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function clear()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE notifications SET removeAt = (:removeAt) WHERE notifyToId = (:notifyToId)");
        $stmt->bindParam(":notifyToId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
        $stmt->execute();

        return $result;
    }

    public function removeNotify($notifyToId, $notifyFromId, $notifyType, $notifyToItemType, $notifyToItemId = 0)
    {
        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE notifications SET removeAt = (:removeAt) WHERE notifyToId = (:notifyToId) AND notifyFromId = (:notifyFromId) AND notifyType = (:notifyType) AND notifyToItemType = (:notifyToItemType) AND notifyToItemId = (:notifyToItemId)");
        $stmt->bindParam(":notifyToId", $notifyToId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyFromId", $notifyFromId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyType", $notifyType, PDO::PARAM_INT);
        $stmt->bindParam(":notifyToItemType", $notifyToItemType, PDO::PARAM_INT);
        $stmt->bindParam(":notifyToItemId", $notifyToItemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM notifications WHERE notifyToId = (:notifyToId)");
        $stmt->bindParam(":notifyToId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getNewCount($lastNotificationsView)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM notifications WHERE notifyToId = (:notifyToId) AND createAt > (:lastNotificationsView) AND removeAt = 0");
        $stmt->bindParam(":notifyToId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":lastNotificationsView", $lastNotificationsView, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
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

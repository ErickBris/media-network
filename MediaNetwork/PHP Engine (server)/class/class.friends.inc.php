<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class friends extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM friends");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM friends");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM friends WHERE friendTo = (:friendTo) AND removeAt = 0");
        $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function request($requestFromUser, $requestToUser)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if ($this->isExists($requestToUser)) {

            return $result;
        }

        if ($this->isRequestExists($requestFromUser, $requestToUser)) {

            $stmt = $this->db->prepare("DELETE FROM friends_requests WHERE fromUser = (:fromUser) AND toUser = (:toUser)");
            $stmt->bindParam(":fromUser", $requestFromUser, PDO::PARAM_INT);
            $stmt->bindParam(":toUser", $requestToUser, PDO::PARAM_INT);

            $stmt->execute();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "friend_request" => false);

            $notify = new notify($this->db);
            $notify->removeNotify($requestToUser, $requestFromUser, NOTIFY_TYPE_FRIEND_REQUEST, ITEM_TYPE_PROFILE, $requestToUser);
            unset($notify);

        } else {

            $createAt = time();

            $stmt = $this->db->prepare("INSERT INTO friends_requests (fromUser, toUser, createAt) value (:fromUser, :toUser, :createAt)");
            $stmt->bindParam(":fromUser", $requestFromUser, PDO::PARAM_INT);
            $stmt->bindParam(":toUser", $requestToUser, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->execute();

            $blacklist = new blacklist($this->db);
            $blacklist->setRequestFrom($requestToUser);

            if (!$blacklist->isExists($requestFromUser)) {

                $account = new account($this->db, $requestToUser);

                if ($account->getAllowFriendsRequestsGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $requestToUser);
                    $gcm->setData(GCM_FRIEND_REQUEST_INBOX, "You have new friend request", 0);
                    $gcm->send();
                }

                unset($account);

                $notify = new notify($this->db);
                $notify->createNotify($requestToUser, $requestToUser, $requestFromUser, NOTIFY_TYPE_FRIEND_REQUEST, ITEM_TYPE_PROFILE, 0);
                unset($notify);
            }

            unset($blacklist);

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "friend_request" => true);
        }

        return $result;
    }

    public function isRequestExists($requestFromUser, $requestToUser)
    {

        $stmt = $this->db->prepare("SELECT id FROM friends_requests WHERE fromUser = (:fromUser) AND toUser = (:toUser) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUser", $requestFromUser, PDO::PARAM_INT);
        $stmt->bindParam(":toUser", $requestToUser, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function isExists($friendId)
    {

        $stmt = $this->db->prepare("SELECT id FROM friends WHERE friend = (:friend) AND friendTo = (:friendTo) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":friend", $friendId, PDO::PARAM_INT);
        $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function reject($fromUser) {

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if ($this->isRequestExists($fromUser, $this->getRequestFrom())) {

            $this->request($fromUser, $this->getRequestFrom());

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function accept($fromUser)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if ($this->isRequestExists($fromUser, $this->getRequestFrom())) {

            $currentTime = time();

            $stmt = $this->db->prepare("INSERT INTO friends (friend, friendTo, createAt) value (:friend, :friendTo, :createAt)");
            $stmt->bindParam(":friend", $fromUser, PDO::PARAM_INT);
            $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);

            if ($stmt->execute()) {

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "itemId" => $this->db->lastInsertId());

                $stmt2 = $this->db->prepare("INSERT INTO friends (friend, friendTo, createAt) value (:friend, :friendTo, :createAt)");
                $stmt2->bindParam(":friend", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt2->bindParam(":friendTo", $fromUser, PDO::PARAM_INT);
                $stmt2->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
                $stmt2->execute();

                $stmt3 = $this->db->prepare("DELETE FROM friends_requests WHERE fromUser = (:fromUser) AND toUser = (:toUser)");
                $stmt3->bindParam(":fromUser", $fromUser, PDO::PARAM_INT);
                $stmt3->bindParam(":toUser", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt3->execute();

                $stmt4 = $this->db->prepare("DELETE FROM friends_requests WHERE fromUser = (:fromUser) AND toUser = (:toUser)");
                $stmt4->bindParam(":fromUser", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt4->bindParam(":toUser", $fromUser, PDO::PARAM_INT);
                $stmt4->execute();

                // ITEM_TYPE_PROFILE = 4
                // NOTIFY_TYPE_FRIEND_REQUEST = 15

                $stmt5 = $this->db->prepare("DELETE FROM notifications WHERE notifyToId = (:notifyToId) AND notifyFromId = (:notifyFromId) AND notifyType = 15 AND notifyToItemType = 4");
                $stmt5->bindParam(":notifyToId", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt5->bindParam(":notifyFromId", $fromUser, PDO::PARAM_INT);
                $stmt5->execute();

                $stmt6 = $this->db->prepare("DELETE FROM notifications WHERE notifyToId = (:notifyToId) AND notifyFromId = (:notifyFromId) AND notifyType = 15 AND notifyToItemType = 4");
                $stmt6->bindParam(":notifyToId", $fromUser, PDO::PARAM_INT);
                $stmt6->bindParam(":notifyFromId", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt6->execute();

                $account = new account($this->db, $this->getRequestFrom());
                $account->updateCounters();
                unset($account);

                $account = new account($this->db, $fromUser);
                $account->updateCounters();

                if ($account->getAllowFriendsRequestsGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $fromUser);
                    $gcm->setData(GCM_FRIEND_REQUEST_ACCEPTED, "Friend Request accepted", 0);
                    $gcm->send();
                }

                unset($account);
            }
        }

        return $result;
    }

    public function clear()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE friends SET removeAt = (:removeAt) WHERE friendTo = (:friendTo) AND removeAt = 0");
        $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function remove($friendId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if ($this->isExists($friendId)) {

            $currentTime = time();

            $stmt = $this->db->prepare("UPDATE friends SET removeAt = (:removeAt) WHERE friendTo = (:friendTo) AND friend = (:friend) AND removeAt = 0");
            $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
            $stmt->bindParam(":friend", $friendId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

            if ($stmt->execute()) {

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);

                $stmt2 = $this->db->prepare("UPDATE friends SET removeAt = (:removeAt) WHERE friend = (:friend) AND friendTo = (:friendTo) AND removeAt = 0");
                $stmt2->bindParam(":friend", $this->getRequestFrom(), PDO::PARAM_INT);
                $stmt2->bindParam(":friendTo", $friendId, PDO::PARAM_INT);
                $stmt2->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
                $stmt2->execute();

                $account = new account($this->db, $this->getRequestFrom());
                $account->updateCounters();
                unset($account);

                $account = new account($this->db, $friendId);
                $account->updateCounters();
            }
        }

        return $result;
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM friends WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['friend']);
                $profileInfo = $profile->get();
                unset($profile);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "friendUserId" => $row['friend'],
                                "friendUserVip" => $profileInfo['vip'],
                                "friendUserVerify" => $profileInfo['verify'],
                                "friendUserUsername" => $profileInfo['username'],
                                "friendUserFullname" => $profileInfo['fullname'],
                                "friendUserPhoto" => $profileInfo['lowPhotoUrl'],
                                "friendUserOnline" => $profileInfo['online'],
                                "friendLocation" => $profileInfo['location'],
                                "friendTo" => $row['friendTo'],
                                "friend" => $row['friend'],
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($userId, $itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id, friend FROM friends WHERE friendTo = (:friendTo) AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':friendTo', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $profile = new profile($this->db, $row['friend']);
                $profile->setRequestFrom($this->getRequestFrom());

                array_push($result['items'], $profile->get());

                $result['itemId'] = $row['id'];

                unset($profile);
            }
        }

        return $result;
    }

    public function getNewCount($lastFriendsView)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM friends WHERE friendTo = (:friendTo) AND createAt > (:lastFriendsView) AND removeAt = 0");
        $stmt->bindParam(":friendTo", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":lastFriendsView", $lastFriendsView, PDO::PARAM_INT);
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

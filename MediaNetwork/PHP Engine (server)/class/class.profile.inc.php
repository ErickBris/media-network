<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class profile extends db_connect
{

    private $id = 0;
    private $requestFrom = 0;

    public function __construct($dbo = NULL, $profileId)
    {

        parent::__construct($dbo);

        $this->setId($profileId);
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }

    public function get()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // test to: check to user and this profile friends?
                $friend = false;

                if ($this->requestFrom != 0) {

                    $friends = new friends($this->db);
                    $friends->setRequestFrom($this->getRequestFrom());

                    if ($friends->isExists($this->getId())) {

                        $friend = true;
                    }

                    unset($friends);
                }

                // test to: check if user send friend request to profile
                $friend_request = false;

                if ($this->requestFrom != 0) {

                    $friends = new friends($this->db, $this->requestFrom);
                    $friends->setRequestFrom($this->getRequestFrom());

                    if ($friends->isRequestExists($this->getRequestFrom(), $this->getId())) {

                        $friend_request = true;
                    }

                    unset($friends);
                }

                // test to blocked
                $blocked = false;

                if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->requestFrom);

                    if ($blacklist->isExists($this->id)) {

                        $blocked = true;
                    }

                    unset($blacklist);
                }

                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "account_type" => $row['account_type'],
                                "account_state" => $row['account_state'],
                                "account_access_level" => $row['account_access_level'],
                                "sex" => $row['sex'],
                                "sex_orientation" => $row['sex_orientation'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "phone" => $row['phone'],
                                "username" => $row['username'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "fb_page" => stripcslashes($row['fb_page']),
                                "instagram_page" => stripcslashes($row['my_page']),
                                "my_page" => stripcslashes($row['my_page']),
                                "verified" => $row['verified'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "photoUrl" => $row['photoUrl'],
                                "coverUrl" => $row['coverUrl'],
                                "itemsCount" => $row['items_count'],
                                "reviewsCount" => $row['reviews_count'],
                                "commentsCount" => $row['comments_count'],
                                "friendsCount" => $row['friends_count'],
                                "likesCount" => $row['likes_count'],
                                "galleryItemsCount" => $row['gallery_items_count'],
                                "imagesCount" => $row['images_count'],
                                "videosCount" => $row['videos_count'],
                                "allowMessagesFromAnyone" => $row['allowMessagesFromAnyone'],
                                "allowItemsFromFriends" => $row['allowItemsFromFriends'],
                                "allowShowProfileOnlyToFriends" => $row['allowShowProfileOnlyToFriends'],
                                "allowItemsComments" => $row['allowItemsComments'],
                                "allowGalleryComments" => $row['allowGalleryComments'],
                                "allowShowOnline" => $row['allowShowOnline'],
                                "allowShowPhoneNumber" => $row['allowShowPhoneNumber'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyJoinDate" => $row['allowShowMyJoinDate'],
                                "inBlackList" => $inBlackList,
                                "blocked" => $blocked,
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online,
                                "friend" => $friend,
                                "friend_request" => $friend_request);
            }
        }

        return $result;
    }

    public function getShort()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "rating" => $row['rating'],
                                "account_state" => $row['account_state'],
                                "sex" => $row['sex'],
                                "sex_orientation" => $row['sex_orientation'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "phone" => $row['phone'],
                                "username" => $row['username'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "fb_page" => stripcslashes($row['fb_page']),
                                "instagram_page" => stripcslashes($row['my_page']),
                                "verified" => $row['verified'],
                                "photoUrl" => $row['photoUrl'],
                                "coverUrl" => $row['coverUrl'],
                                "allowMessagesFromAnyone" => $row['allowMessagesFromAnyone'],
                                "allowItemsFromFriends" => $row['allowItemsFromFriends'],
                                "allowShowProfileOnlyToFriends" => $row['allowShowProfileOnlyToFriends'],
                                "allowShowOnline" => $row['allowShowOnline'],
                                "allowShowPhoneNumber" => $row['allowShowPhoneNumber'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyJoinDate" => $row['allowShowMyJoinDate'],
                                "itemsCount" => $row['items_count'],
                                "reviewsCount" => $row['reviews_count'],
                                "commentsCount" => $row['comments_count'],
                                "likesCount" => $row['likes_count'],
                                "galleryItemsCount" => $row['gallery_items_count'],
                                "imagesCount" => $row['images_count'],
                                "videosCount" => $row['videos_count'],
                                "friendsCount" => $row['friends_count'],
                                "inBlackList" => $inBlackList,
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online);
            }
        }

        return $result;
    }

    public function getVeryShort()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "rating" => $row['rating'],
                                "account_state" => $row['account_state'],
                                "sex" => $row['sex'],
                                "sex_orientation" => $row['sex_orientation'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "phone" => $row['phone'],
                                "username" => $row['username'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "verified" => $row['verified'],
                                "photoUrl" => $row['photoUrl'],
                                "coverUrl" => $row['coverUrl'],
                                "allowMessagesFromAnyone" => $row['allowMessagesFromAnyone'],
                                "allowItemsFromFriends" => $row['allowItemsFromFriends'],
                                "allowShowProfileOnlyToFriends" => $row['allowShowProfileOnlyToFriends'],
                                "allowShowOnline" => $row['allowShowOnline'],
                                "allowShowPhoneNumber" => $row['allowShowPhoneNumber'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyJoinDate" => $row['allowShowMyJoinDate'],
                                "itemsCount" => $row['items_count'],
                                "reviewsCount" => $row['reviews_count'],
                                "commentsCount" => $row['comments_count'],
                                "likesCount" => $row['likes_count'],
                                "galleryItemsCount" => $row['gallery_items_count'],
                                "imagesCount" => $row['images_count'],
                                "videosCount" => $row['videos_count'],
                                "friendsCount" => $row['friends_count'],
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online);
            }
        }

        return $result;
    }

    public function setId($profileId)
    {
        $this->id = $profileId;
    }

    public function getId()
    {
        return $this->id;
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


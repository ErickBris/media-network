<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class gallery extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM gallery WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM gallery");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM gallery WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($showInStream, $desc, $videoUrl = "", $imgUrl = "", $originImgUrl = "", $previewImgUrl = "", $photoArea = "", $photoCountry = "", $photoCity = "", $photoLat = "0.000000", $photoLng = "0.000000")
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($originImgUrl) == 0 && strlen($previewImgUrl) == 0 && strlen($imgUrl) == 0) {

            return $result;
        }

        if (strlen($desc) != 0) {

            $desc = $desc." ";
        }

        $itemType = GALLERY_ITEM_TYPE_IMAGE;

        if (strlen($videoUrl) != 0) $itemType = GALLERY_ITEM_TYPE_VIDEO;

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO gallery (fromUserId, showInStream, itemType, description, originImgUrl, previewImgUrl, imgUrl, videoUrl, area, country, city, lat, lng, createAt, ip_addr, u_agent) value (:fromUserId, :showInStream, :itemType, :description, :originImgUrl, :previewImgUrl, :imgUrl, :videoUrl, :area, :country, :city, :lat, :lng, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":showInStream", $showInStream, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":description", $desc, PDO::PARAM_STR);
        $stmt->bindParam(":originImgUrl", $originImgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":previewImgUrl", $previewImgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":videoUrl", $videoUrl, PDO::PARAM_STR);
        $stmt->bindParam(":area", $photoArea, PDO::PARAM_STR);
        $stmt->bindParam(":country", $photoCountry, PDO::PARAM_STR);
        $stmt->bindParam(":city", $photoCity, PDO::PARAM_STR);
        $stmt->bindParam(":lat", $photoLat, PDO::PARAM_STR);
        $stmt->bindParam(":lng", $photoLng, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "itemId" => $this->db->lastInsertId(),
                            "item" => $this->info($this->db->lastInsertId()));

            $account = new account($this->db, $this->requestFrom);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        if ($itemInfo['fromUserId'] != $this->requestFrom) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE gallery SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $toItemType = ITEM_TYPE_GALLERY_ITEM;

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE notifyToItemId = (:notifyToItemId) AND notifyToItemType = (:notifyToItemType)");
            $stmt2->bindParam(":notifyToItemId", $itemId, PDO::PARAM_INT);
            $stmt2->bindParam(":notifyToItemType", $toItemType, PDO::PARAM_INT);
            $stmt2->execute();

            //remove all comments to item

            $stmt3 = $this->db->prepare("UPDATE comments SET removeAt = (:removeAt) WHERE itemId = (:itemId)");
            $stmt3->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt3->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt3->execute();

            //remove all likes to item

            $stmt4 = $this->db->prepare("UPDATE likes SET removeAt = (:removeAt) WHERE itemId = (:itemId) AND removeAt = 0");
            $stmt4->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt4->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt4->execute();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

            $account = new account($this->db, $itemInfo['fromUserId']);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function remove_all()
    {

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT id FROM gallery WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(':fromUserId', $this->getRequestFrom(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

            while ($row = $stmt->fetch()) {

                $this->remove($row['id']);
            }
        }

        return $result;
    }

    public function restore($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE gallery SET removeAt = 0 WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function recalculate($itemId) {

        $comments_count = 0;
        $likes_count = 0;
        $rating = 0;

        $like = new like($this->db);
        $like->setRequestFrom($this->getRequestFrom());

        $likes_count = $like->getLikesCount($itemId, ITEM_TYPE_GALLERY_ITEM);

        unset($like);

        $comments = new comments($this->db);
        $comments->setRequestFrom($this->getRequestFrom());

        $comments_count = $comments->getCommentsCount($itemId, ITEM_TYPE_GALLERY_ITEM);

        unset($comments);

        $rating = $likes_count + $comments_count;

        $stmt = $this->db->prepare("UPDATE gallery SET likesCount = (:likesCount), commentsCount = (:commentsCount), rating = (:rating) WHERE id = (:itemId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":commentsCount", $comments_count, PDO::PARAM_INT);
        $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $likes_count,
                        "commentsCount" => $comments_count,
                        "myLike" => false);

        return $result;
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM gallery WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $myLike = false;

                if ($this->requestFrom != 0) {

                    $like = new like($this->db);
                    $like->setRequestFrom($this->getRequestFrom());

                    if ($like->is_like_exists($itemId, $this->requestFrom, ITEM_TYPE_GALLERY_ITEM)) {

                        $myLike = true;
                    }

                    unset($like);
                }

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->get();
                unset($profile);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "accessMode" => $row['accessMode'],
                                "itemType" => $row['itemType'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserVerified" => $profileInfo['verified'],
                                "fromUserOnline" => $profileInfo['online'],
                                "fromUserUsername" => $profileInfo['username'],
                                "fromUserFullname" => $profileInfo['fullname'],
                                "fromUserPhoto" => $profileInfo['photoUrl'],
                                "fromUserAllowGalleryComments" => $profileInfo['allowGalleryComments'],
                                "fromUserAllowShowOnline" => $profileInfo['allowShowOnline'],
                                "desc" => htmlspecialchars_decode(stripslashes($row['description'])),
                                "area" => htmlspecialchars_decode(stripslashes($row['area'])),
                                "country" => htmlspecialchars_decode(stripslashes($row['country'])),
                                "city" => htmlspecialchars_decode(stripslashes($row['city'])),
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "imgUrl" => $row['imgUrl'],
                                "previewImgUrl" => $row['previewImgUrl'],
                                "originImgUrl" => $row['originImgUrl'],
                                "previewVideoImgUrl" => $row['previewVideoImgUrl'],
                                "videoUrl" => $row['videoUrl'],
                                "rating" => $row['rating'],
                                "commentsCount" => $row['commentsCount'],
                                "likesCount" => $row['likesCount'],
                                "viewsCount" => $row['viewsCount'],
                                "myLike" => $myLike,
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($profileId, $itemId = 0, $accessMode = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        if ($accessMode == 0) {

            $stmt = $this->db->prepare("SELECT id FROM gallery WHERE accessMode = 0 AND fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
            $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        } else {

            $stmt = $this->db->prepare("SELECT id FROM gallery WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
            $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        }

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['items'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }

    public function stream($itemId = 0, $language = 'en')
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM gallery WHERE showInStream = 1 AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $itemInfo = $this->info($row['id']);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $itemInfo['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    public function feed($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT friend FROM friends WHERE friendTo = (:friendTo) AND removeAt = 0 ORDER BY createAt DESC");
        $stmt->bindParam(':friendTo', $this->getRequestFrom(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $items = array();

            while ($row = $stmt->fetch()) {

                $stmt2 = $this->db->prepare("SELECT id FROM gallery WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC");
                $stmt2->bindParam(':fromUserId', $row['friend'], PDO::PARAM_INT);
                $stmt2->bindParam(':itemId', $itemId, PDO::PARAM_INT);
                $stmt2->execute();

                while ($row2 = $stmt2->fetch())  {

                    $items[] = array("id" => $row2['id'], "itemId" => $row2['id']);
                }
            }

            $stmt3 = $this->db->prepare("SELECT id FROM gallery WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC");
            $stmt3->bindParam(':fromUserId', $this->getRequestFrom(), PDO::PARAM_INT);
            $stmt3->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            $stmt3->execute();

            while ($row3 = $stmt3->fetch())  {

                $items[] = array("id" => $row3['id'], "itemId" => $row3['id']);
            }

            $currentItem = 0;
            $maxItem = 20;

            if (count($items) != 0) {

                arsort($items);

                foreach ($items as $key => $value) {

                    if ($currentItem < $maxItem) {

                        $currentItem++;

                        $itemInfo = $this->info($value['itemId']);

                        array_push($result['items'], $itemInfo);

                        $result['itemId'] = $itemInfo['id'];

                        unset($itemInfo);
                    }
                }
            }
        }

        return $result;
    }

    public function favorites($itemId = 0)
    {
        if ($itemId == 0) {

            $like = new like($this->db);
            $like->setRequestFrom($this->getRequestFrom());

            $itemId = $like->getMaxIdLikes();
            $itemId++;

            unset($like);
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $itemType = ITEM_TYPE_GALLERY_ITEM;

        $stmt = $this->db->prepare("SELECT id, itemId FROM likes WHERE removeAt = 0 AND id < (:itemId) AND fromUserId = (:fromUserId) AND itemType = (:itemType) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $itemInfo = $this->info($row['itemId']);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $row['id'];

                    unset($itemInfo);
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

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}

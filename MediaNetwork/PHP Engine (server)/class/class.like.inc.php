<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class like extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getLikesCount($itemId, $itemType)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM likes WHERE itemId = (:itemId) AND itemType = (:itemType) AND removeAt = 0");
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($itemId, $itemFromUserId, $fromUserId, $itemType)
    {
        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $myLike = false;

        if ($this->is_like_exists($itemId, $fromUserId, $itemType)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE likes SET removeAt = (:removeAt) WHERE itemId = (:itemId) AND fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
            $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($itemFromUserId, $fromUserId, NOTIFY_TYPE_LIKE, $itemType, $itemId);
            unset($notify);

            $myLike = false;

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO likes (toUserId, fromUserId, itemId, itemType, createAt, ip_addr) value (:toUserId, :fromUserId, :itemId, :itemType, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $itemFromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
            $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            $myLike = true;

            $likeId = $this->db->lastInsertId();

            if ($itemFromUserId != $fromUserId) {

                $account = new account($this->db, $itemFromUserId);

                if ($account->getAllowLikesGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $itemFromUserId);
                    $gcm->setData(GCM_NOTIFY_LIKE, "You have new like", $itemId);
                    $gcm->send();
                }

                unset($account);

                $notify = new notify($this->db);
                $notify->createNotify($itemFromUserId, $itemId, $fromUserId, NOTIFY_TYPE_LIKE, $itemType, $likeId);
                unset($notify);
            }
        }

        switch ($itemType) {

            case ITEM_TYPE_GALLERY_ITEM: {

                $gallery = new gallery($this->db);
                $gallery->setRequestFrom($this->getRequestFrom());

                $result = $gallery->recalculate($itemId);

                break;
            }

            default: {

                break;
            }
        }

        if ($itemFromUserId != $this->requestFrom) {

            $account = new account($this->db, $itemFromUserId);
            $account->updateCounters();
            unset($account);
        }

        $result['myLike'] = $myLike;

        return $result;
    }

    public function is_like_exists($itemId, $fromUserId, $itemType)
    {
        $stmt = $this->db->prepare("SELECT id FROM likes WHERE fromUserId = (:fromUserId) AND itemId = (:itemId) AND itemType = (:itemType) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function getLikers($itemId, $itemType, $likeId = 0)
    {

        if ($likeId == 0) {

            $likeId = $this->getMaxIdLikes();
            $likeId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likeId" => $likeId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM likes WHERE itemId = (:itemId) AND itemType = (:itemType) AND id < (:likeId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
        $stmt->bindParam(':likeId', $likeId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['likeId'] = $row['id'];
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

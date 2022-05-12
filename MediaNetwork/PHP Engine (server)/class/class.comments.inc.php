<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class comments extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function allCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT max(id) FROM comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count($itemId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE itemId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCommentsCount($itemId, $itemType)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE itemId = (:itemId) AND itemType = (:itemType) AND removeAt = 0");
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($itemId, $itemFromUserId, $itemType, $content, $imgUrl, $replyToUserId = 0)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $account = new account($this->db, $this->requestFrom);
        $account->setLastActive();
        unset($account);

        if (strlen($content) == 0 && strlen($imgUrl) == 0) {

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO comments (itemId, itemType, fromUserId, itemContent, imgUrl, replyToUserId, createAt, ip_addr, u_agent) value (:itemId, :itemType, :fromUserId, :itemContent, :imgUrl, :replyToUserId, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":replyToUserId", $replyToUserId, PDO::PARAM_INT);
        $stmt->bindParam(":itemContent", $content, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $commentId = $this->db->lastInsertId();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "commentId" => $commentId,
                            "comment" => $this->info($this->db->lastInsertId()));

            if (($itemFromUserId != 0) && ($this->requestFrom != $itemFromUserId) && ($replyToUserId != $itemFromUserId)) {

                $account = new account($this->db, $itemFromUserId);

                if ($account->getAllowCommentsGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $itemFromUserId);
                    $gcm->setData(GCM_NOTIFY_COMMENT, "You have a new comment.", $itemId);
                    $gcm->send();
                }

                $notify = new notify($this->db);
                $notify->createNotify($itemFromUserId, $itemId, $this->requestFrom, NOTIFY_TYPE_COMMENT, $itemType, $commentId);
                unset($notify);

                unset($account);
            }

            if ($replyToUserId != $this->requestFrom && $replyToUserId != 0) {

                $account = new account($this->db, $replyToUserId);

                if ($account->getAllowCommentReplyGCM() == 1) {

                    $gcm = new gcm($this->db, $replyToUserId);
                    $gcm->setData(GCM_NOTIFY_COMMENT_REPLY, "You have a new reply to comment.", $itemId);
                    $gcm->send();
                }

                $notify = new notify($this->db);
                $notify->createNotify($replyToUserId, $itemId, $this->requestFrom, NOTIFY_TYPE_COMMENT_REPLY, $itemType, $commentId);
                unset($notify);

                unset($account);
            }
        }

        switch ($itemType) {

            case ITEM_TYPE_GALLERY_ITEM: {

                $gallery = new gallery($this->db);
                $gallery->setRequestFrom($this->getRequestFrom());

                $gallery->recalculate($itemId);

                break;
            }

            default: {

                break;
            }
        }

        return $result;
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE comments SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId)");
            $stmt2->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt2->execute();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function removeAll($itemId) {

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE comments SET removeAt = (:removeAt) WHERE itemId = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
    }

    public function info($commentId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM comments WHERE id = (:commentId) LIMIT 1");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $fromUserId = $profile->get();
                unset($profile);

                $replyToUserId = $row['replyToUserId'];
                $replyToUserUsername = "";
                $replyToFullname = "";

                if ($replyToUserId != 0) {

                    $profile = new profile($this->db, $row['replyToUserId']);
                    $replyToUser = $profile->get();
                    unset($profile);

                    $replyToUserUsername = $replyToUser['username'];
                    $replyToFullname = $replyToUser['fullname'];
                }

                $lowPhotoUrl = "";

                if (strlen($fromUserId['photoUrl']) != 0) {

                    $lowPhotoUrl = $fromUserId['photoUrl'];
                }

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "comment" => htmlspecialchars_decode(stripslashes($row['itemContent'])),
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => $fromUserId['account_state'],
                                "fromUserVerified" => $fromUserId['verified'],
                                "fromUserOnline" => $fromUserId['online'],
                                "fromUserAllowShowOnline" => $fromUserId['allowShowOnline'],
                                "fromUserUsername" => $fromUserId['username'],
                                "fromUserFullname" => $fromUserId['fullname'],
                                "fromUserPhotoUrl" => $lowPhotoUrl,
                                "replyToUserId" => $replyToUserId,
                                "replyToUserUsername" => $replyToUserUsername,
                                "replyToFullname" => $replyToFullname,
                                "itemId" => $row['itemId'],
                                "createAt" => $row['createAt'],
                                "notifyId" => $row['notifyId'],
                                "timeAgo" => $time->timeAgo($row['createAt']));
            }
        }

        return $result;
    }

    public function get($itemId, $commentId = 0)
    {
        if ($commentId == 0) {

            $commentId = $this->allCommentsCount() + 1;
        }

        $comments = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "commentId" => $commentId,
                          "itemId" => $itemId,
                          "comments" => array());

        $stmt = $this->db->prepare("SELECT id FROM comments WHERE itemId = (:itemId) AND id < (:commentId) AND removeAt = 0 ORDER BY id DESC LIMIT 70");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $commentInfo = $this->info($row['id']);

                array_push($comments['comments'], $commentInfo);

                $comments['commentId'] = $commentInfo['id'];

                unset($commentInfo);
            }
        }

        return $comments;
    }

    public function stream($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->allCommentsCount();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM comments WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $info = $this->info($row['id']);

                    array_push($result['items'], $info);

                    $result['itemId'] = $info['id'];

                    unset($info);
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

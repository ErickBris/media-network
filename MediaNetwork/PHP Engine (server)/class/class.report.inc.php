<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class report extends db_connect
{

	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM abuse_reports");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE abuse_reports SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function remove_all($toItemType)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE abuse_reports SET removeAt = (:removeAt) WHERE abuseToItemType = (:abuseToItemType)");
        $stmt->bindParam(":abuseToItemType", $toItemType, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function add($toItemId, $toItemType, $abuseId)
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS);

        $create_at = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO abuse_reports (abuseFromUserId, abuseToItemType, abuseToItemId, abuseId, createAt, ip_addr) value (:abuseFromUserId, :abuseToItemType, :abuseToItemId, :abuseId, :createAt, :ip_addr)");
        $stmt->bindParam(":abuseFromUserId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":abuseToItemType", $toItemType, PDO::PARAM_INT);
        $stmt->bindParam(":abuseToItemId", $toItemId, PDO::PARAM_INT);
        $stmt->bindParam(":abuseId", $abuseId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $create_at, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        $stmt->execute();

        return $result;
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM abuse_reports WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "abuseFromUserId" => $row['abuseFromUserId'],
                                "abuseToItemType" => $row['abuseToItemType'],
                                "abuseToItemId" => $row['abuseToItemId'],
                                "abuseId" => $row['abuseId'],
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']));
            }
        }

        return $result;
    }

    public function get($toItemType, $limit = 40)
    {
        $itemId = $this->getMaxId();
        $itemId++;

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM abuse_reports WHERE id < (:itemId) AND abuseToItemType = (:abuseToItemType) AND removeAt = 0 ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':abuseToItemType', $toItemType, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

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

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}


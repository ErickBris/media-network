<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class cfcm extends db_connect
{
    private $accountId = 0;
    private $url = "https://android.googleapis.com/gcm/send";
    private $ids = array();
    private $data = array();

    public function __construct($dbo = NULL, $accountId = 0)
    {
        parent::__construct($dbo);

        $this->accountId = $accountId;

        if ($this->accountId != 0) {

            $account = new account($this->db, $this->accountId);

            $deviceId = $account->getGCM_regId();

            if (strlen($deviceId) != 0) {

                $this->addDeviceId($deviceId);
            }
        }
    }

    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function clearIds()
    {
        $this->ids = array();
    }

    public function send()
    {
        $result = array("error" => true,
                        "description" => "regId not found");

        if (empty($this->ids)) {

            return $result;
        }

        $post = array(
            'registration_ids'  => $this->ids,
            'data'              => $this->data,
        );

        $headers = array(
            'Authorization: key=' . FIREBASE_SERVER_KEY,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $this->url);
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {

            $result = array("error" => true,
                            "failure" => 1,
                            "description" => curl_error($ch));
        }

        curl_close($ch);

        $obj = json_decode($result, true);

        $status = 0;

        if ($obj['success'] != 0) {

            $status = 1;
        }

        $this->addToHistory($this->data['type'], $this->data['msgType'], $this->data['msg'], $this->data['addon'], $status, $obj['success']);

        return $result;
    }

    private function addToHistory($fcmId, $msgType, $msg, $addon, $status, $success)
    {
        if ($fcmId == GCM_NOTIFY_URL || $fcmId == GCM_NOTIFY_CUSTOM || $fcmId == GCM_NOTIFY_PERSONAL) {

            $currentTime = time();

            $stmt = $this->db->prepare("INSERT INTO gcm_history (msg, msgType, fcmId, addon, accountId, status, success, createAt) value (:msg, :msgType, :fcmId, :addon, :accountId, :status, :success, :createAt)");
            $stmt->bindParam(":msg", $msg, PDO::PARAM_STR);
            $stmt->bindParam(":msgType", $msgType, PDO::PARAM_INT);
            $stmt->bindParam(":fcmId", $fcmId, PDO::PARAM_INT);
            $stmt->bindParam(":addon", $addon, PDO::PARAM_STR);
            $stmt->bindParam(":accountId", $this->accountId, PDO::PARAM_INT);
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $stmt->bindParam(":success", $success, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function forAll()
    {
        $stmt = $this->db->prepare("SELECT gcm_regid FROM users WHERE gcm_regid <> ''");
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $this->addDeviceId($row['gcm_regid']);
        }
    }

    public function addDeviceId($id)
    {
        $this->ids[] = $id;
    }

    public function setData($fcmId, $msgType, $msg, $addon, $id = 0)
    {
        $this->data = array("type" => $fcmId,
                            "msgType" => $msgType,
                            "msg" => $msg,
                            "addon" => $addon,
                            "id" => $id,
                            "accountId" => $this->accountId);
    }

    public function getData()
    {
        return $this->data;
    }

    public function clearData()
    {
        $this->data = array();
    }

    public function addGCM_regId($gcm_regid)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $currentTime = time();

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO gcm_reg_ids (gcm_regid, createAt, u_agent, ip_addr) value (:gcm_regid, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function searchGCM_regId($gcm_regid)
    {
        $stmt = $this->db->prepare("SELECT id FROM gcm_reg_ids WHERE gcm_regid = (:gcm_regid) LIMIT 1");
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }
}
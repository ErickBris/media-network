<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class account extends db_connect
{

    private $id = 0;

    public function __construct($dbo = NULL, $accountId = 0)
    {

        parent::__construct($dbo);

        $this->setId($accountId);
    }

    public function signup($username, $fullname, $password, $email, $sex, $year, $month, $day, $language = '')
    {

        $result = array("error" => true);

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 0,
                            "error_description" => "Incorrect username");

            return $result;
        }

        if ($helper->isLoginExists($username)) {

            $result = array("error" => true,
                            "error_code" => ERROR_LOGIN_TAKEN,
                            "error_type" => 0,
                            "error_description" => "Login already taken");

            return $result;
        }

        if (empty($fullname)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 3,
                            "error_description" => "Empty user full name");

            return $result;
        }

        if (!helper::isCorrectPassword($password)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 1,
                            "error_description" => "Incorrect password");

            return $result;
        }

        if (!helper::isCorrectEmail($email)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 2,
                            "error_description" => "Wrong email");

            return $result;
        }

        if ($helper->isEmailExists($email)) {

            $result = array("error" => true,
                            "error_code" => ERROR_EMAIL_TAKEN,
                            "error_type" => 2,
                            "error_description" => "User with this email is already registered");

            return $result;
        }

        $salt = helper::generateSalt(3);
        $passw_hash = md5(md5($password).$salt);
        $currentTime = time();

        $ip_addr = helper::ip_addr();

        $accountState = ACCOUNT_STATE_ENABLED;

        $stmt = $this->db->prepare("INSERT INTO users (account_state, username, fullname, passw, email, sex, salt, regtime, ip_addr) value (:account_state, :username, :fullname, :password, :email, :sex, :salt, :createAt, :ip_addr)");
        $stmt->bindParam(":account_state", $accountState, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":password", $passw_hash, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":sex", $sex, PDO::PARAM_INT);
        $stmt->bindParam(":salt", $salt, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $this->setId($this->db->lastInsertId());

            $this->setLanguage("en");

            $result = array("error" => false,
                            'accountId' => $this->id,
                            'username' => $username,
                            'password' => $password,
                            'error_code' => ERROR_SUCCESS,
                            'error_description' => 'SignUp Success!');

            return $result;
        }

        return $result;
    }

    public function signin($username, $password)
    {
        $access_data = array('error' => true,
                             'error_code' => ERROR_UNKNOWN);

        $username = helper::clearText($username);
        $password = helper::clearText($password);

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE username = (:username) OR email = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password).$row['salt']);

            $stmt2 = $this->db->prepare("SELECT id, account_state, fullname, photoUrl, verified FROM users WHERE (username = (:username) OR email = (:username)) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $row2 = $stmt2->fetch();

                $access_data = array("error" => false,
                                     "error_code" => ERROR_SUCCESS,
                                     "accountId" => $row2['id'],
                                     "account_state" => $row2['account_state'],
                                     "fullname" => $row2['fullname'],
                                     "username" => $username,
                                     "photoUrl" => $row2['photoUrl'],
                                     "verified" => $row2['verified']);
            }
        }

        return $access_data;
    }

    public function logout($accountId, $accessToken)
    {
        $auth = new auth($this->db);
        $auth->remove($accountId, $accessToken);
    }

    public function setPassword($password, $newPassword)
    {
        $result = array('error' => true,
                        'error_code' => ERROR_UNKNOWN);

        if (!helper::isCorrectPassword($password)) {

            return $result;
        }

        if (!helper::isCorrectPassword($newPassword)) {

            return $result;
        }

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password).$row['salt']);

            $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":accountId", $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $this->newPassword($newPassword);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
            }
        }

        return $result;
    }

    public function newPassword($password)
    {
        $newSalt = helper::generateSalt(3);
        $newHash = md5(md5($password).$newSalt);

        $stmt = $this->db->prepare("UPDATE users SET passw = (:newHash), salt = (:newSalt) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":newHash", $newHash, PDO::PARAM_STR);
        $stmt->bindParam(":newSalt", $newSalt, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function setPhone($phone)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET phone = (:phone) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getPhone()
    {
        $stmt = $this->db->prepare("SELECT phone FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['phone'];
        }

        return 0;
    }

    public function setSex($sex)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET sex = (:sex) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":sex", $sex, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getSex()
    {
        $stmt = $this->db->prepare("SELECT sex FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['sex'];
        }

        return 0;
    }

    public function setSexOrientation($sex_orientation)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET sex_orientation = (:sex_orientation) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":sex_orientation", $sex_orientation, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getSexOrientation()
    {
        $stmt = $this->db->prepare("SELECT sex_orientation FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['sex_orientation'];
        }

        return 0;
    }

    public function setBirth($year, $month, $day)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET bYear = (:bYear), bMonth = (:bMonth), bDay = (:bDay) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":bYear", $year, PDO::PARAM_INT);
        $stmt->bindParam(":bMonth", $month, PDO::PARAM_INT);
        $stmt->bindParam(":bDay", $day, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setAdmob($admob)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET admob = (:mode) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":mode", $admob, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getAdmob()
    {
        $stmt = $this->db->prepare("SELECT admob FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['admob'];
        }

        return 0;
    }

    public function setGhost($ghost)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET ghost = (:ghost) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":ghost", $ghost, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGhost()
    {
        $stmt = $this->db->prepare("SELECT ghost FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['ghost'];
        }

        return 0;
    }

    public function setFacebookId($fb_id)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET fb_id = (:fb_id) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fb_id", $fb_id, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getFacebookId()
    {
        $stmt = $this->db->prepare("SELECT fb_id FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['fb_id'];
        }

        return 0;
    }

    public function setFacebookPage($fb_page)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET fb_page = (:fb_page) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fb_page", $fb_page, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getFacebookPage()
    {
        $stmt = $this->db->prepare("SELECT fb_page FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['fb_page'];
        }

        return '';
    }

    public function setInstagramPage($instagram_page)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET my_page = (:my_page) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":my_page", $instagram_page, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getInstagramPage()
    {
        $stmt = $this->db->prepare("SELECT my_page FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['my_page'];
        }

        return '';
    }

    public function setEmail($email, $email_verified = 0)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $helper = new helper($this->db);

        if (!helper::isCorrectEmail($email)) {

            return $result;
        }

        if ($helper->isEmailExists($email)) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET email = (:email) AND email_verified = (:email_verified) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":email_verified", $email_verified, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setEmailVerified($email_verified)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET email_verified = (:email_verified) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":email_verified", $email_verified, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getEmail()
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['email'];
        }

        return '';
    }

    public function setUsername($username)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {

            return $result;
        }

        if ($helper->isLoginExists($username)) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET username = (:username) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['username'];
        }

        return '';
    }

    public function setLocation($location)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET country = (:country) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":country", $location, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLocation()
    {
        $stmt = $this->db->prepare("SELECT country FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['country'];
        }

        return '';
    }

    public function setGeoLocation($lat, $lng)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET lat = (:lat), lng = (:lng) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":lat", $lat, PDO::PARAM_STR);
        $stmt->bindParam(":lng", $lng, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGeoLocation()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT lat, lng FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'lat' => $row['lat'],
                            'lng' => $row['lng']);
        }

        return $result;
    }

    public function setStatus($status)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET status = (:status) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getStatus()
    {
        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['status'];
        }

        return '';
    }

    public function restorePointCreate($email, $clientId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $restorePointInfo = $this->restorePointInfo();

        if ($restorePointInfo['error'] === false) {

            return $restorePointInfo;
        }

        $currentTime = time();	// Current time

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $hash = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO restore_data (accountId, hash, email, clientId, createAt, u_agent, ip_addr) value (:accountId, :hash, :email, :clientId, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $this->id,
                            'hash' => $hash,
                            'email' => $email);
        }

        return $result;
    }

    public function restorePointInfo()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM restore_data WHERE accountId = (:accountId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $row['accountId'],
                            'hash' => $row['hash'],
                            'email' => $row['email']);
        }

        return $result;
    }

    public function restorePointRemove()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE restore_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setGCM_regId($gcm_regid)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET gcm_regid = (:gcm_regid) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGCM_regId()
    {
        $stmt = $this->db->prepare("SELECT gcm_regid FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['gcm_regid'];
        }

        return 0;
    }

    public function deactivation($password)
    {

        $result = array('error' => true,
                        'error_code' => ERROR_UNKNOWN);

        if (!helper::isCorrectPassword($password)) {

            return $result;
        }

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password) . $row['salt']);

            $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":accountId", $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $this->setState(ACCOUNT_STATE_DISABLED);
                $this->setPhoto(array("normalPhotoUrl" => "", "photoUrl" => ""));
                $this->setCover(array("normalCoverUrl" => "", "coverUrl" => ""));

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
            }
        }

        return $result;
    }

    public function setLanguage($language)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET lang_code = (:lang_code) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":lang_code", $language, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLanguage()
    {
        $stmt = $this->db->prepare("SELECT lang_code FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['lang_code'];
        }

        return 'en';
    }

    public function setVerified($verified)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET verified = (:verified) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":verified", $verified, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setFullname($fullname)
    {
        if (strlen($fullname) == 0) {

            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setBalance($balance)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET balance = (:balance) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":balance", $balance, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getBalance()
    {
        $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['balance'];
        }

        return 0;
    }

    public function setAllowMessagesFromAnyone($allowMessagesFromAnyone)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowMessagesFromAnyone = (:allowMessagesFromAnyone) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowMessagesFromAnyone", $allowMessagesFromAnyone, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowMessagesFromAnyone()
    {
        $stmt = $this->db->prepare("SELECT allowMessagesFromAnyone FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowMessagesFromAnyone'];
        }

        return 0;
    }

    public function setAllowLikesGCM($allowLikesGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowLikesGCM = (:allowLikesGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowLikesGCM", $allowLikesGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowLikesGCM()
    {
        $stmt = $this->db->prepare("SELECT allowLikesGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowLikesGCM'];
        }

        return 0;
    }

    public function setAllowCommentsGCM($allowCommentsGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowCommentsGCM = (:allowCommentsGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentsGCM", $allowCommentsGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowCommentsGCM()
    {
        $stmt = $this->db->prepare("SELECT allowCommentsGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowCommentsGCM'];
        }

        return 0;
    }

    public function setAllowFriendsRequestsGCM($allowFollowersGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowFriendsRequestsGCM = (:allowFriendsRequestsGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowFriendsRequestsGCM", $allowFollowersGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowFriendsRequestsGCM()
    {
        $stmt = $this->db->prepare("SELECT allowFriendsRequestsGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowFriendsRequestsGCM'];
        }

        return 0;
    }

    public function getAllowMessagesGCM()
    {
        $stmt = $this->db->prepare("SELECT allowMessagesGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowMessagesGCM'];
        }

        return 0;
    }

    public function setAllowCommentReplyGCM($allowCommentReplyGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowCommentReplyGCM = (:allowCommentReplyGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentReplyGCM", $allowCommentReplyGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowCommentReplyGCM()
    {
        $stmt = $this->db->prepare("SELECT allowCommentReplyGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowCommentReplyGCM'];
        }

        return 0;
    }

    public function setAllowReviewsGCM($allowReviewsGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowReviewsGCM = (:allowReviewsGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowReviewsGCM", $allowReviewsGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowReviewsGCM()
    {
        $stmt = $this->db->prepare("SELECT allowReviewsGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowReviewsGCM'];
        }

        return 0;
    }

    public function setGCM_settings($allowMessagesGCM, $allowLikesGCM, $allowCommentsGCM, $allowCommentReplyGCM, $allowFriendsRequestsGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowMessagesGCM = (:allowMessagesGCM), allowLikesGCM = (:allowLikesGCM), allowCommentsGCM = (:allowCommentsGCM), allowCommentReplyGCM = (:allowCommentReplyGCM), allowFriendsRequestsGCM = (:allowFriendsRequestsGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowMessagesGCM", $allowMessagesGCM, PDO::PARAM_INT);
        $stmt->bindParam(":allowLikesGCM", $allowLikesGCM, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentsGCM", $allowCommentsGCM, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentReplyGCM", $allowCommentReplyGCM, PDO::PARAM_INT);
        $stmt->bindParam(":allowFriendsRequestsGCM", $allowFriendsRequestsGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getGCM_settings()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT allowMessagesGCM, allowLikesGCM, allowCommentsGCM, allowCommentReplyGCM, allowFriendsRequestsGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "allowMessagesGCM" => $row['allowMessagesGCM'],
                            "allowLikesGCM" => $row['allowLikesGCM'],
                            "allowCommentsGCM" => $row['allowCommentsGCM'],
                            "allowCommentReplyGCM" => $row['allowCommentReplyGCM'],
                            "allowFriendsRequestsGCM" => $row['allowFriendsRequestsGCM']);
        }

        return $result;
    }

    public function setPrivacy_settings($allowMessagesFromAnyone, $allowGalleryComments, $allowShowProfileOnlyToFriends, $allowShowOnline, $allowShowPhoneNumber)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowMessagesFromAnyone = (:allowMessagesFromAnyone), allowGalleryComments = (:allowGalleryComments), allowShowProfileOnlyToFriends = (:allowShowProfileOnlyToFriends), allowShowOnline = (:allowShowOnline), allowShowPhoneNumber = (:allowShowPhoneNumber) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowMessagesFromAnyone", $allowMessagesFromAnyone, PDO::PARAM_INT);
        $stmt->bindParam(":allowGalleryComments", $allowGalleryComments, PDO::PARAM_INT);
        $stmt->bindParam(":allowShowProfileOnlyToFriends", $allowShowProfileOnlyToFriends, PDO::PARAM_INT);
        $stmt->bindParam(":allowShowOnline", $allowShowOnline, PDO::PARAM_INT);
        $stmt->bindParam(":allowShowPhoneNumber", $allowShowPhoneNumber, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPrivacy_settings()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT allowMessagesFromAnyone, allowGalleryComments, allowShowProfileOnlyToFriends, allowShowOnline, allowShowPhoneNumber FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "allowMessagesFromAnyone" => $row['allowMessagesFromAnyone'],
                            "allowGalleryComments" => $row['allowGalleryComments'],
                            "allowShowProfileOnlyToFriends" => $row['allowShowProfileOnlyToFriends'],
                            "allowShowOnline" => $row['allowShowOnline'],
                            "allowShowPhoneNumber" => $row['allowShowPhoneNumber']);
        }

        return $result;
    }

    public function setAllowGalleryComments($allowGalleryComments)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowGalleryComments = (:allowGalleryComments) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowGalleryComments", $allowGalleryComments, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowGalleryComments()
    {
        $stmt = $this->db->prepare("SELECT allowGalleryComments FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowGalleryComments'];
        }

        return 0;
    }

    public function setAllowShowPhoneNumber($allowShowPhoneNumber)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowShowPhoneNumber = (:allowShowPhoneNumber) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":allowShowPhoneNumber", $allowShowPhoneNumber, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowShowPhoneNumber()
    {
        $stmt = $this->db->prepare("SELECT allowShowPhoneNumber FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowShowPhoneNumber'];
        }

        return 0;
    }

    public function setAllowShowOnline($allowShowOnline)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowShowOnline = (:allowShowOnline) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":allowShowOnline", $allowShowOnline, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowShowOnline()
    {
        $stmt = $this->db->prepare("SELECT allowShowOnline FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowShowOnline'];
        }

        return 0;
    }

    public function setAllowShowProfileOnlyToFriends($allowShowProfileOnlyToFriends)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowShowProfileOnlyToFriends = (:allowShowProfileOnlyToFriends) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":allowShowProfileOnlyToFriends", $allowShowProfileOnlyToFriends, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowShowProfileOnlyToFriends()
    {
        $stmt = $this->db->prepare("SELECT allowShowProfileOnlyToFriends FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowShowProfileOnlyToFriends'];
        }

        return 0;
    }

    public function setAllowItemsComments($allowItemsComments)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowItemsComments = (:allowItemsComments) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowItemsComments", $allowItemsComments, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowItemsComments()
    {
        $stmt = $this->db->prepare("SELECT allowItemsComments FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowItemsComments'];
        }

        return 0;
    }



    public function setState($accountState)
    {

        $stmt = $this->db->prepare("UPDATE users SET account_state = (:accountState) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":accountState", $accountState, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getState()
    {
        $stmt = $this->db->prepare("SELECT account_state FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['account_state'];
        }

        return 0;
    }

    public function setLastActive()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_authorize = (:last_authorize) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":last_authorize", $time, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function setLastNotificationsView()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_notifications_view = (:last_notifications_view) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":last_notifications_view", $time, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getLastNotificationsView()
    {
        $stmt = $this->db->prepare("SELECT last_notifications_view FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['last_notifications_view'];
            }
        }

        $time = time();

        return $time;
    }

    public function setLastFriendsView()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_friends_view = (:last_friends_view) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->getId(), PDO::PARAM_INT);
        $stmt->bindParam(":last_friends_view", $time, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getLastFriendsView()
    {
        $stmt = $this->db->prepare("SELECT last_friends_view FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['last_friends_view'];
            }
        }

        $time = time();

        return $time;
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

                $notifications_count = 0;
                $messages_count = 0;
                $new_friends_count = 0; // New friends count

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
                                "balance" => $row['balance'],
                                "fb_id" => $row['fb_id'],
                                "account_state" => $row['account_state'],
                                "account_type" => $row['account_type'],
                                "account_access_level" => $row['account_access_level'],
                                "regtime" => $row['regtime'],
                                "ip_addr" => $row['ip_addr'],
                                "phone" => $row['phone'],
                                "first_name" => $row['first_name'],
                                "surname" => $row['surname'],
                                "username" => $row['username'],
                                "fullname" => stripcslashes($row['fullname']),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "fb_page" => stripcslashes($row['fb_page']),
                                "instagram_page" => stripcslashes($row['my_page']),
                                "verified" => $row['verified'],
                                "email" => $row['email'],
                                "email_verified" => $row['email_verified'],
                                "sex" => $row['sex'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "lang_code" => $row['lang_code'],
                                "last_notifications_view" => $row['last_notifications_view'],
                                "last_messages_view" => $row['last_messages_view'],
                                "last_friends_view" => $row['last_friends_view'],
                                "photoUrl" => $row['photoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "coverUrl" => $row['coverUrl'],
                                "normalCoverUrl" => $row['normalCoverUrl'],
                                "allowMessagesFromAnyone" => $row['allowMessagesFromAnyone'],
                                "allowGalleryComments" => $row['allowGalleryComments'],
                                "allowItemsComments" => $row['allowItemsComments'],
                                "allowShowProfileOnlyToFriends" => $row['allowShowProfileOnlyToFriends'],
                                "allowShowOnline" => $row['allowShowOnline'],
                                "allowShowPhoneNumber" => $row['allowShowPhoneNumber'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyJoinDate" => $row['allowShowMyJoinDate'],
                                "allowItemsFromFriends" => $row['allowItemsFromFriends'],
                                "allowCommentsGCM" => $row['allowCommentsGCM'],
                                "allowMessagesGCM" => $row['allowMessagesGCM'],
                                "allowLikesGCM" => $row['allowLikesGCM'],
                                "allowFriendsRequestsGCM" => $row['allowFriendsRequestsGCM'],
                                "allowCommentReplyGCM" => $row['allowCommentReplyGCM'],
                                "allowReviewsGCM" => $row['allowReviewsGCM'],
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online,
                                "itemsCount" => $row['items_count'],
                                "reviewsCount" => $row['reviews_count'],
                                "commentsCount" => $row['comments_count'],
                                "notificationsCount" => $notifications_count,
                                "friendsCount" => $new_friends_count,
                                "messagesCount" => $messages_count);

                unset($profile);
                unset($time);
            }
        }

        return $result;
    }

    public function updateCounters()
    {
        $itemsCount = $this->getItemsCount();
        $commentsCount = $this->getCommentsCount();
        $reviewsCount = $this->getReviewsCount();
        $likesCount = $this->getLikesCount();
        $galleryItemsCount = $this->getGalleryItemsCount();

        $friends = new friends($this->db);
        $friends->setRequestFrom($this->getId());

        $friends_count = $friends->count();

        unset($friends);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET friends_count = (:friends_count), gallery_items_count = (:gallery_items_count), items_count = (:items_count), likes_count = (:likes_count), comments_count = (:comments_count), reviews_count = (:reviews_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":friends_count", $friends_count, PDO::PARAM_INT);
        $stmt->bindParam(":gallery_items_count", $galleryItemsCount, PDO::PARAM_INT);
        $stmt->bindParam(":items_count", $itemsCount, PDO::PARAM_INT);
        $stmt->bindParam(":likes_count", $likesCount, PDO::PARAM_INT);
        $stmt->bindParam(":comments_count", $commentsCount, PDO::PARAM_INT);
        $stmt->bindParam(":reviews_count", $reviewsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setGalleryItemsCount($galleryItemsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE gallery SET gallery_items_count = (:gallery_items_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":gallery_items_count", $galleryItemsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGalleryItemsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM gallery WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setReviewsCount($reviewsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET reviews_count = (:reviews_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":reviews_count", $reviewsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getReviewsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM reviews WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setCommentsCount($commentsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET comments_count = (:comments_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":comments_count", $commentsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setItemsCount($itemsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET items_count = (:items_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":items_count", $itemsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getItemsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function setLikesCount($likesCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET likes_count = (:likes_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":likes_count", $likesCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLikesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM likes WHERE toUserId = (:toUserId) AND removeAt = 0");
        $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function edit($fullname)
    {
        $result = array("error" => true);

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    public function setPhoto($array_data)
    {
        $stmt = $this->db->prepare("UPDATE users SET normalPhotoUrl = (:normalPhotoUrl), photoUrl = (:photoUrl) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":normalPhotoUrl", $array_data['normalPhotoUrl'], PDO::PARAM_STR);
        $stmt->bindParam(":photoUrl", $array_data['photoUrl'], PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setCover($array_data)
    {
        $stmt = $this->db->prepare("UPDATE users SET coverUrl = (:coverUrl), normalCoverUrl = (:normalCoverUrl) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":coverUrl", $array_data['coverUrl'], PDO::PARAM_STR);
        $stmt->bindParam(":normalCoverUrl", $array_data['normalCoverUrl'], PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setCoverPosition($position)
    {
        $stmt = $this->db->prepare("UPDATE users SET coverPosition = (:coverPosition) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":coverPosition", $position, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setBackgroundUrl($url)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET originCoverUrl = (:originCoverUrl), normalCoverUrl = (:normalCoverUrl) WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":originCoverUrl", $url, PDO::PARAM_STR);
        $stmt->bindParam(":normalCoverUrl", $url, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getBackgroundUrl()
    {
        $stmt = $this->db->prepare("SELECT originCoverUrl FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['originCoverUrl'];
            }
        }

        return '';
    }

    public function getAccessLevel($user_id)
    {
        $stmt = $this->db->prepare("SELECT access_level FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['access_level'];
            }
        }

        return 0;
    }

    public function setAccessLevel($access_level)
    {
        $stmt = $this->db->prepare("UPDATE users SET access_level = (:access_level) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":access_level", $access_level, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function setId($accountId)
    {
        $this->id = $accountId;
    }

    public function getId()
    {
        return $this->id;
    }
}


<?php

/*!
 * IF engine v1.0
 * http://ifsoft.co.uk
 * ifengine@ifsoft.co.uk
 *
 * Copyright 2012-2016 Dmitry Demyanchuk
 */

class auth extends db_connect
{
    private $auth_valid_sec = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

        $this->auth_valid_sec = 7 * 24 * 3600; // 7 days
    }

    public function authorize($accountId, $accessToken)
    {
        $accountId = helper::clearInt($accountId);

        $accessToken = helper::clearText($accessToken);
        $accessToken = helper::escapeText($accessToken);

        $stmt = $this->db->prepare("SELECT id FROM access_data WHERE accountId = (:accountId) AND accessToken = (:accessToken) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function remove($accountId, $accessToken)
    {
        $accountId = helper::clearInt($accountId);

        $accessToken = helper::clearText($accessToken);
        $accessToken = helper::escapeText($accessToken);

        $currentTime = time(); //current time

        $stmt = $this->db->prepare("UPDATE access_data SET removeAt = (:removeAt) WHERE accountId = (:accountId) AND accessToken = (:accessToken)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return true;
        }

        return false;
    }

    public function removeAll($accountId)
    {
        $accountId = helper::clearInt($accountId);

        $currentTime = time(); //current time

        $stmt = $this->db->prepare("UPDATE access_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return true;
        }

        return false;
    }

    public function create($accountId, $clientId = 0)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $currentTime = time();	// Current time

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $accessToken = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO access_data (accountId, accessToken, clientId, createAt, u_agent, ip_addr) value (:accountId, :accessToken, :clientId, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error'=> false,
                            'error_code' => ERROR_SUCCESS,
                            'accessToken' => $accessToken,
                            'accountId' => $accountId);
        }

        return $result;
    }

    static function clearCookie()
    {
        @setcookie('user_name', null, -1, '/');
        @setcookie("user_password", null, -1, '/');
    }

    static function updateCookie($user_name, $access_token)
    {
        @setcookie('user_name', "{$user_name}", time() + 7 * 24 * 3600, "/");
        @setcookie('user_password', "$access_token", time() + 7 * 24 * 3600, "/");
    }

    protected function generateLogin($social_id)
    {
        if (!$this->isLoginExists("id".$social_id)) {

            return "id".$social_id;

        } else {

            return "id".$social_id.helper::generateId(3);
        }
    }


    protected function getUserId($user_login)
    {
        $stmt = $this->db->prepare("SELECT id FROM tb_accounts WHERE user_login = (:user_login) LIMIT 1");
        $stmt->bindParam(":user_login", $user_login, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            return $row['id'];
        }

        return 0;
    }

    protected function getUserLogin($user_id)
    {
        $stmt = $this->db->prepare("SELECT login FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            return $row['login'];
        }

        return 0;
    }

    protected function getUserEmail($user_id)
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            return $row['email'];
        }

        return 0;
    }

    static function isSession()
    {
        if (isset($_SESSION) && isset($_SESSION['user_id'])) {

            return true;

        } else {

            return false;
        }
    }

    static function setSession($user_id, $user_login, $user_fullname, $user_photo_url, $user_verify, $access_level, $access_token)
    {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_login'] = $user_login;
        $_SESSION['user_photo_url'] = $user_photo_url;
        $_SESSION['user_fullname'] = $user_fullname;
        $_SESSION['user_verify'] = $user_verify;
        $_SESSION['access_level'] = $access_level;
        $_SESSION['create_at'] = time();
        $_SESSION['access_token'] = $access_token;
    }

    static function unsetSession()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_login']);
        unset($_SESSION['user_photo_url']);
        unset($_SESSION['user_fullname']);
        unset($_SESSION['user_verify']);
        unset($_SESSION['access_level']);
        unset($_SESSION['create_at']);
        unset($_SESSION['access_token']);
    }

    public function setActivationSession($access_data)
    {
        $_SESSION['activation_user_id'] = $access_data['user_id'];
        $_SESSION['activation_access_token'] = $access_data['access_token'];
    }

    public function unsetActivationSession()
    {

        unset($_SESSION['activation_user_id']);
        unset($_SESSION['activation_access_token']);
    }

    static function unsetAuthorizationSession()
    {
        unset($_SESSION['signup_with']);
        unset($_SESSION['social_id']);
        unset($_SESSION['social_username']);
        unset($_SESSION['social_email']);
    }

    static function getCurrentUserId()
    {
        if (isset($_SESSION) && isset($_SESSION['user_id'])) {

            return $_SESSION['user_id'];

        } else {

            return 0;
        }
    }

    static function getCurrentUserLogin()
    {
        if (isset($_SESSION) && isset($_SESSION['user_login'])) {

            return $_SESSION['user_login'];

        } else {

            return 'undefined';
        }
    }

    static function getCurrentAccessLevel()
    {
        if (isset($_SESSION) && isset($_SESSION['access_level'])) {

            return $_SESSION['access_level'];

        } else {

            return 0;
        }
    }

    static function setCurrentUserPhotoUrl($user_photo_url)
    {
        $_SESSION['user_photo_url'] = $user_photo_url;
    }

    static function getCurrentUserPhotoUrl()
    {
        if (isset($_SESSION) && isset($_SESSION['user_photo_url']) && strlen($_SESSION['user_photo_url']) > 0) {

            return $_SESSION['user_photo_url'];

        } else {

            return "/img/profile_default_photo.png";
        }
    }

    static function getCurrentUserFullname()
    {
        if (isset($_SESSION) && isset($_SESSION['user_fullname'])) {

            return $_SESSION['user_fullname'];

        } else {

            return "undefined";
        }
    }

    static function getCurrentUserVerify()
    {
        if (isset($_SESSION) && isset($_SESSION['user_verify'])) {

            return $_SESSION['user_verify'];

        } else {

            return 0;
        }
    }

    static function getAccessToken()
    {
        if (isset($_SESSION) && isset($_SESSION['access_token'])) {

            return $_SESSION['access_token'];

        } else {

            return "undefined";
        }
    }

    static function newAuthenticityToken()
    {

        $authenticity_token = md5(uniqid(rand(), true));

        if (isset($_SESSION)) {

            $_SESSION['authenticity_token'] = $authenticity_token;
        }
    }

    static function getAuthenticityToken()
    {
        if (isset($_SESSION) && isset($_SESSION['authenticity_token'])) {

            return $_SESSION['authenticity_token'];

        } else {

            return NULL;
        }
    }

    static function isActivationSession()
    {
        if (isset($_SESSION) && isset($_SESSION['activation_access_token'])) {

            return true;

        } else {

            return false;
        }
    }

    static function generateSalt($n = 3)
    {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz.,*_-=+';
        $counter = strlen($pattern)-1;

        for ($i=0; $i<$n; $i++) {

            $key .= $pattern{rand(0,$counter)};
        }

        return $key;
    }
}

<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class email extends db_connect
{

	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function sendConfirmRequest($email, $clientId = 0)
    {
        $confirmPointInfo = $this->createConfirmPoint($email, $clientId);

        ob_start();

        ?>

        <html>
        <body>
        This is link <a href="<?php echo APP_URL;  ?>/confirm/?hash=<?php echo $confirmPointInfo['hash']; ?>"><?php echo APP_URL;  ?>/confirm/?hash=<?php echo $confirmPointInfo['hash']; ?></a> to reset your password.
        </body>
        </html>

        <?php

        $from = SMTP_EMAIL;

        $to = $email;

        $html_text = ob_get_clean();

        $subject = APP_TITLE." | Confirm Email";

        $mail = new phpmailer();

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = SMTP_HOST;                               // Specify main and backup SMTP servers
        $mail->SMTPAuth = SMTP_AUTH;                               // Enable SMTP authentication
        $mail->Username = SMTP_USERNAME;                      // SMTP username
        $mail->Password = SMTP_PASSWORD;                      // SMTP password
        $mail->SMTPSecure = SMTP_SECURE;                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = SMTP_PORT;                                    // TCP port to connect to

        $mail->From = $from;
        $mail->FromName = APP_TITLE;
        $mail->addAddress($to);               // Name is optional

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $subject;
        $mail->Body    = $html_text;

        $mail->send();

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS);
    }

    public function createConfirmPoint($email, $clientId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $confirmPointInfo = $this->getConfirmPointInfo();

        if ($confirmPointInfo['error'] === false) {

            return $confirmPointInfo;
        }

        $currentTime = time();	// Current time

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $hash = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO confirm_data (accountId, hash, email, clientId, createAt, u_agent, ip_addr) value (:accountId, :hash, :email, :clientId, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":accountId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $this->getRequestFrom(),
                            'hash' => $hash,
                            'email' => $email);
        }

        return $result;
    }

    public function getConfirmPoint($hash)
    {
        $hash = helper::clearText($hash);
        $hash = helper::escapeText($hash);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM confirm_data WHERE hash = (:hash) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array('error'=> false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $row['accountId'],
                            'hash' => $row['hash'],
                            'email' => $row['email']);
        }

        return $result;
    }

    public function getConfirmPointInfo()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM confirm_data WHERE accountId = (:accountId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $this->getRequestFrom(), PDO::PARAM_INT);
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

    public function confirmPointRemove()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE confirm_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
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


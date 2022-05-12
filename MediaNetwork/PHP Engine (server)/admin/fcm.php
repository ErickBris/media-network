<?php

    /*!
     * ifsoft.co.uk engine v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
    }

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
        $addon = isset($_POST['addon']) ? $_POST['addon'] : '';
        $type = isset($_POST['type']) ? $_POST['type'] : 1;
        $fcmId = isset($_POST['fcmId']) ? $_POST['fcmId'] : 0;

        $message = helper::clearText($message);
        $message = helper::escapeText($message);

        $addon = helper::clearText($addon);
        $addon = helper::escapeText($addon);

        $type = helper::clearInt($type);
        $fcmId = helper::clearInt($fcmId);

        if ($authToken === helper::getAuthenticityToken() && !APP_DEMO) {

            if (strlen($message) != 0) {

                $fcm = new fcm($dbo, 0);
                $fcm->setData($fcmId, $type, $message, $addon, 0);
                $fcm->forAll();
                $fcm->send();
            }
        }

        header("Location: /admin/fcm.php");
    }

    $stats = new stats($dbo);

    $page_id = "gcm";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css");
    $page_title = "Firebase Cloud Messaging";

    include_once($_SERVER['DOCUMENT_ROOT']."/common/header.inc.php");

?>

<body>

    <?php

        include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_topbar.inc.php");
    ?>

<main class="content">
    <div class="row">
        <div class="col s12 m12 l12">

            <?php

                include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_banner.inc.php");
            ?>

            <div class="card">
                <div class="card-content">
                    <div class="row">
                        <div class="col s12">

                        <div class="row">
                            <div class="col s6">
                                <h4>Send message (FCM) for all users</h4>
                            </div>
                        </div>

                        <?php
                            if (APP_DEMO) {

                                ?>
                                    <div class="row">
                                        <div class="col s12">
                                            <div class="card blue-grey lighten-2">
                                                <div class="card-content white-text">
                                                    <span class="card-title">Sending push notifications (FCM) is not available in the demo version mode. That we turned off the sending push notifications (FCM) in the demo version mode to protect users from spam and of foul language.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                            }
                        ?>

                        <form method="post" action="/admin/fcm.php">

                            <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                            <div class="row">

                                <div class="input-field col s12">
                                    <select name="fcmId">
                                        <option selected="selected" value="<?php echo GCM_NOTIFY_CUSTOM; ?>">Simple message (GCM_NOTIFY_CUSTOM)</option>
                                        <option value="<?php echo GCM_NOTIFY_URL; ?>">Link to Web Page (GCM_NOTIFY_URL)</option>
                                    </select>
                                    <label>Message type</label>
                                </div>

                                <div class="input-field col s12">
                                    <select name="type">
                                        <option selected="selected" value="<?php echo GCM_MESSAGE_FOR_ALL_USERS; ?>">For all users</option>
                                        <option value="<?php echo GCM_MESSAGE_ONLY_FOR_AUTHORIZED_USERS; ?>">Only for authorized users</option>
                                    </select>
                                    <label>Message for</label>
                                </div>

                                <script type="text/javascript">

                                    $(document).ready(function() {

                                        $('select').material_select();
                                    });

                                </script>

                                <div class="input-field col s12">
                                    <input type="text" class="validate" name="message" id="message" maxlength="100">
                                    <label for="message">Message text</label>
                                </div>

                                <div class="input-field col s12">
                                    <input type="text" class="validate" name="addon" id="addon" maxlength="255">
                                    <label for="addon">Additional text. Currently used for web-links (for GCM_NOTIFY_URL only)</label>
                                </div>

                            </div>

                            <button type="submit" class="btn waves-effect waves-light" name="" >Send</button>
                        </form>

                        <div class="row">
                            <div class="col s12">
                                <h4>Recently sent messages</h4>
                            </div>
                        </div>

                        <div class="col s12">

                            <?php

                                $result = $stats->getGcmHistory();

                                $inbox_loaded = count($result['data']);

                                if ($inbox_loaded != 0) {

                                ?>

                                <table class="bordered data-tables responsive-table">
                                    <tbody>
                                        <tr>
                                            <th class="text-left">Id</th>
                                            <th>Message For</th>
                                            <th>Message Type</th>
                                            <th>Message</th>
                                            <th>Addon text (URL)</th>
                                            <th>Status</th>
                                            <th>Delivered</th>
                                            <th>Create At</th>
                                        </tr>

                                    <?php

                                    foreach ($result['data'] as $key => $value) {

                                        draw($value);
                                    }

                                    ?>

                                    </tbody>
                                </table>

                                <?php

                                    } else {

                                        ?>

                                        <div class="row">
                                            <div class="col s12">
                                                <div class="card blue-grey darken-1">
                                                    <div class="card-content white-text">
                                                        <span class="card-title">History is empty.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                    }
                                    ?>
                        </div>

			</div>
		  </div>
		</div>
	  </div>
	</div>
</div>
</main>

        <?php

            include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_footer.inc.php");
        ?>

        <script type="text/javascript">


        </script>

</body>
</html>

<?php

    function draw($item)
    {
        ?>

        <tr>
            <td class="text-left"><?php echo $item['id']; ?></td>
            <td>
                <?php

                    switch ($item['msgType']) {

                        case GCM_MESSAGE_FOR_ALL_USERS: {

                            echo "For all users";
                            break;
                        }

                        case GCM_MESSAGE_ONLY_FOR_AUTHORIZED_USERS: {

                            echo "Only for authorized users";
                            break;
                        }

                        default: {

                            break;
                        }
                    }
                ?>
            </td>
            <td>
                <?php

                    switch ($item['fcmId']) {

                        case GCM_NOTIFY_CUSTOM: {

                            echo "Simple message (GCM_NOTIFY_CUSTOM)";
                            break;
                        }

                        case GCM_NOTIFY_URL: {

                            echo "Link to Web Page (GCM_NOTIFY_URL)";
                            break;
                        }

                        default: {

                            break;
                        }
                    }
                ?>
            </td>
            <td><?php echo $item['msg']; ?></td>
            <td><?php echo $item['addon']; ?></td>
            <td><?php if ($item['status'] == 1) {echo "success";} else {echo "failure";} ?></td>
            <td><?php echo $item['success']; ?></td>
            <td><?php echo date("Y-m-d H:i:s", $item['createAt']); ?></td>
        </tr>

        <?php
    }
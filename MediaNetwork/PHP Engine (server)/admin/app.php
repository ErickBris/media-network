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

    $stats = new stats($dbo);
    $settings = new settings($dbo);
    $admin = new admin($dbo);

    $allowFacebookAuthorization = 1;
    $allowLogIn = 1;
    $allowSignUp = 1;
    $allowPasswordRecovery = 1;
    $allowAdmobBanner = 1;
    $allowAddVideoToGallery = 1;
    $allowEmoji = 1;
    $allowAddImageToMessage = 1;
    $allowSeenFunction = 1;
    $allowTypingFunction = 1;

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';

        $allowFacebookAuthorization_checkbox = isset($_POST['allowFacebookAuthorization']) ? $_POST['allowFacebookAuthorization'] : '';
        $allowLogIn_checkbox = isset($_POST['allowLogIn']) ? $_POST['allowLogIn'] : '';
        $allowSignUp_checkbox = isset($_POST['allowSignUp']) ? $_POST['allowSignUp'] : '';
        $allowPasswordRecovery_checkbox = isset($_POST['allowPasswordRecovery']) ? $_POST['allowPasswordRecovery'] : '';
        $allowAdmobBanner_checkbox = isset($_POST['allowAdmobBanner']) ? $_POST['allowAdmobBanner'] : '';
        $allowAddVideoToGallery_checkbox = isset($_POST['allowAddVideoToGallery']) ? $_POST['allowAddVideoToGallery'] : '';
        $allowEmoji_checkbox = isset($_POST['allowEmoji']) ? $_POST['allowEmoji'] : '';
        $allowAddImageToMessage_checkbox = isset($_POST['allowAddImageToMessage']) ? $_POST['allowAddImageToMessage'] : '';
        $allowSeenFunction_checkbox = isset($_POST['allowSeenFunction']) ? $_POST['allowSeenFunction'] : '';
        $allowTypingFunction_checkbox = isset($_POST['allowTypingFunction']) ? $_POST['allowTypingFunction'] : '';

        if ($authToken === helper::getAuthenticityToken() && !APP_DEMO) {

            if ($allowFacebookAuthorization_checkbox === "on") {

                $allowFacebookAuthorization = 1;

            } else {

                $allowFacebookAuthorization = 0;
            }

            if ($allowLogIn_checkbox === "on") {

                $allowLogIn = 1;

            } else {

                $allowLogIn = 0;
            }

            if ($allowSignUp_checkbox === "on") {

                $allowSignUp = 1;

            } else {

                $allowSignUp = 0;
            }

            if ($allowPasswordRecovery_checkbox === "on") {

                $allowPasswordRecovery = 1;

            } else {

                $allowPasswordRecovery = 0;
            }

            if ($allowAdmobBanner_checkbox === "on") {

                $allowAdmobBanner = 1;

            } else {

                $allowAdmobBanner = 0;
            }

            if ($allowAddVideoToGallery_checkbox === "on") {

                $allowAddVideoToGallery = 1;

            } else {

                $allowAddVideoToGallery = 0;
            }

            if ($allowEmoji_checkbox === "on") {

                $allowEmoji = 1;

            } else {

                $allowEmoji = 0;
            }

            if ($allowAddImageToMessage_checkbox === "on") {

                $allowAddImageToMessage = 1;

            } else {

                $allowAddImageToMessage = 0;
            }

            if ($allowSeenFunction_checkbox === "on") {

                $allowSeenFunction = 1;

            } else {

                $allowSeenFunction = 0;
            }

            if ($allowTypingFunction_checkbox === "on") {

                $allowTypingFunction = 1;

            } else {

                $allowTypingFunction = 0;
            }

            $settings->setValue("navMessagesMenuItem", 1);
            $settings->setValue("navNotificationsMenuItem", 1);
            $settings->setValue("allowFacebookAuthorization", $allowFacebookAuthorization);
            $settings->setValue("allowLogIn", $allowLogIn);
            $settings->setValue("allowSignUp", $allowSignUp);
            $settings->setValue("allowPasswordRecovery", $allowPasswordRecovery);
            $settings->setValue("allowAdmobBanner", $allowAdmobBanner);
            $settings->setValue("allowAddVideoToGallery", $allowAddVideoToGallery);
            $settings->setValue("allowEmoji", $allowEmoji);
            $settings->setValue("allowAddImageToMessage", $allowAddImageToMessage);
            $settings->setValue("allowSeenFunction", $allowSeenFunction);
            $settings->setValue("allowTypingFunction", $allowTypingFunction);

            $cfcm = new cfcm($dbo);
            $cfcm->setData(GCM_NOTIFY_CONFIG, GCM_MESSAGE_FOR_ALL_USERS, "You have new message", "");
            $cfcm->forAll();
            $cfcm->send();
        }
    }

    $config = $settings->get();

    $arr = array();

    $arr = $config['allowFacebookAuthorization'];
    $allowFacebookAuthorization = $arr['intValue'];

    $arr = $config['allowFacebookAuthorization'];
    $allowLogIn = $arr['intValue'];

    $arr = $config['allowSignUp'];
    $allowSignUp = $arr['intValue'];

    $arr = $config['allowPasswordRecovery'];
    $allowPasswordRecovery = $arr['intValue'];

    $arr = $config['allowAdmobBanner'];
    $allowAdmobBanner = $arr['intValue'];

    $arr = $config['allowAddVideoToGallery'];
    $allowAddVideoToGallery = $arr['intValue'];

    $arr = $config['allowEmoji'];
    $allowEmoji = $arr['intValue'];

    $arr = $config['allowAddImageToMessage'];
    $allowAddImageToMessage = $arr['intValue'];

    $arr = $config['allowSeenFunction'];
    $allowSeenFunction = $arr['intValue'];

    $arr = $config['allowTypingFunction'];
    $allowTypingFunction = $arr['intValue'];

    $page_id = "app";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css", "admin.css");
    $page_title = "App Settings";

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
                                <h4>App Settings</h4>
                            </div>
                        </div>


                        <div class="col s12">

                            <form action="/admin/app.php" method="post">

                                <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                <p>
                                    <input type="checkbox" name="allowFacebookAuthorization" id="allowFacebookAuthorization" <?php if ($allowFacebookAuthorization == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowFacebookAuthorization">Allow registration/authorization via Facebook</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowLogIn" id="allowLogIn" <?php if ($allowLogIn == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowLogIn">Allow authorization for users</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowSignUp" id="allowSignUp" <?php if ($allowSignUp == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowSignUp">Allow registration for new users</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowPasswordRecovery" id="allowPasswordRecovery" <?php if ($allowPasswordRecovery == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowPasswordRecovery">Enable "Password Recovery" feature</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowAdmobBanner" id="allowAdmobBanner" <?php if ($allowAdmobBanner == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowAdmobBanner">Enable show Admob banner</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowAddVideoToGallery" id="allowAddVideoToGallery" <?php if ($allowAddVideoToGallery == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowAddVideoToGallery">Allow add video files to the gallery</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowEmoji" id="allowEmoji" <?php if ($allowEmoji == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowEmoji">Allow internal "emoji keyboard"</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowAddImageToMessage" id="allowAddImageToMessage" <?php if ($allowAddImageToMessage == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowAddImageToMessage">Enable images in chat</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowSeenFunction" id="allowSeenFunction" <?php if ($allowSeenFunction == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowSeenFunction">Enable "seen" function in chat</label>
                                </p>

                                <p class="padding_top_15">
                                    <input type="checkbox" name="allowTypingFunction" id="allowTypingFunction" <?php if ($allowTypingFunction == 1) echo "checked=\"checked\"";  ?> />
                                    <label for="allowTypingFunction">Enable "typing" function in chat</label>
                                </p>

                                <p class="padding_top_30">
                                    <button class="btn waves-effect waves-light teal">Save</button>
                                </p>

                            </form>
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
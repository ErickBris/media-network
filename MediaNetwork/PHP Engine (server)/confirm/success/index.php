<?php

    /*!
     * ifsoft engine v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk, qascript@mail.ru
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    $page_id = "confirm_success";

    $css_files = array("my.css");
    $page_title = APP_TITLE;

    include_once($_SERVER['DOCUMENT_ROOT']."/common/header.inc.php");
?>

<body>

    <?php

        include_once($_SERVER['DOCUMENT_ROOT']."/common/admin_panel_topbar.inc.php");
    ?>
    <div class="section no-pad-bot" id="index-banner">
        <div class="container">

            <div class="row">
                <div class="col s12 m6" style="margin: 0 auto; float: none; margin-top: 100px;">
                    <div class="card teal lighten-2">
                        <div class="card-content white-text">
                            <span class="card-title"><strong>Success!</strong><br> You have successfully verified your email!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/init.js"></script>

</body>
</html>
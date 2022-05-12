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

    $page_id = "reports";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css", "admin.css");
    $page_title = "Reports";

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
                            <div class="col s2">
                                <h4>Reports</h4>
                            </div>
                        </div>

                        <div class="col s12">

                            <div id="stats" class="">

                                <div class="row">

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/reports_profile.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>PROFILE</p>
                                                    <h4><?php echo $stats->getReportsCount(ITEM_TYPE_PROFILE); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Reports To Profiles</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/reports_gallery.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>GALLERY</p>
                                                    <h4><?php echo $stats->getReportsCount(ITEM_TYPE_GALLERY_ITEM); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Reports To Gallery Items</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>
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

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

    $page_id = "stats";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css", "admin.css");
    $page_title = "Statistics";

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
                                <h4>Statistics</h4>
                            </div>
                        </div>

                        <div class="col s12">
                            <table class="striped responsive-table">
                                    <tbody>
                                        <tr>
                                            <th class="text-left">Name</th>
                                            <th>Count</th>
                                        </tr>
                                        <tr>
                                            <td>Accounts</td>
                                            <td><?php echo $stats->getAccountsTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Active accounts</td>
                                            <td><?php echo $stats->getAccountsCount(ACCOUNT_STATE_ENABLED); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Disabled accounts</td>
                                            <td><?php echo $stats->getAccountsCount(ACCOUNT_STATE_DISABLED); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Blocked accounts</td>
                                            <td><?php echo $stats->getAccountsCount(ACCOUNT_STATE_BLOCKED); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total Gallery Items</td>
                                            <td><?php echo $stats->getGalleryItemsTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total Active Gallery Items (not removed)</td>
                                            <td><?php echo $stats->getGalleryItemsCount(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total comments</td>
                                            <td><?php echo $stats->getCommentsTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total active comments (not removed)</td>
                                            <td><?php echo $stats->getCommentsCount(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total Likes</td>
                                            <td><?php echo $stats->getLikesTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Total Active Likes (not removed)</td>
                                            <td><?php echo $stats->getLikesCount(); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-left">Total chats</td>
                                            <td><?php echo $stats->getChatsTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-left">Total active chats (not removed)</td>
                                            <td><?php echo $stats->getChatsCount(); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-left">Total messages</td>
                                            <td><?php echo $stats->getMessagesTotal(); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-left">Total active messages (not removed)</td>
                                            <td><?php echo $stats->getMessagesCount(); ?></td>
                                        </tr>
                                    </tbody>
                            </table>
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
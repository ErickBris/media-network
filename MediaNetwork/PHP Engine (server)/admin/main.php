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

    $page_id = "main";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("my.css", "admin.css");
    $page_title = "General";

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

                            <div id="stats" class="">

                                <div class="row">

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>ACCOUNTS</p>
                                                    <h4><?php echo $stats->getAccountsTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Total Accounts</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>GALLERY</p>
                                                    <h4><?php echo $stats->getGalleryItemsTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Gallery Items Total</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>COMMENTS</p>
                                                    <h4><?php echo $stats->getCommentsTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Comments Total</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>LIKES</p>
                                                    <h4><?php echo $stats->getLikesTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Likes Total</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>MESSAGES</p>
                                                    <h4><?php echo $stats->getMessagesTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Messages Total</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col s12 m6 l3">
                                        <a href="/admin/stats.php">
                                            <div class="card">
                                                <div class="card-content blue darken-2 white-text">
                                                    <p>CHATS</p>
                                                    <h4><?php echo $stats->getChatsTotal(); ?></h4>
                                                    <p><span class="blue-grey-text text-lighten-5">Chats Total</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

				<div class="row">
					<div class="col s12">
						<h4>The recently registered users</h4>
					</div>
				</div>

				<div class="col s12">

                    <?php

                        $result = $stats->getAccounts(0);

                        $inbox_loaded = count($result['users']);

                        if ($inbox_loaded != 0) {

                        ?>

						<table class="bordered data-tables responsive-table">
							<tbody>
                                <tr>
                                    <th>Id</th>
                                    <th>Photo</th>
                                    <th>Username</th>
                                    <th>Fullname</th>
                                    <th>Facebook</th>
                                    <th>Email</th>
                                    <th>Sign up date</th>
                                    <th>Ip address</th>
                                    <th>Action</th>
                                </tr>

                            <?php

                            foreach ($result['users'] as $key => $value) {

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
                                                <span class="card-title">List is empty.</span>
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

    function draw($user)
    {
        $profileImage = $user['photoUrl'];

        if (strlen($profileImage) == 0) {

            $profileImage = "/img/profile_default_photo.png";
        }

        ?>

        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><img style="height: 50px" src="<?php echo $profileImage; ?>"></td>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['fullname']; ?></td>
            <td><?php if (strlen($user['fb_id']) == 0) {echo "Not connected to facebook.";} else {echo "<a target=\"_blank\" href=\"https://www.facebook.com/app_scoped_user_id/{$user['fb_id']}\">Facebook account link</a>";} ?></td>
            <td><?php if (!APP_DEMO) {echo $user['email'];} else {echo "It is not available in the demo version";} ?></td>
            <td><?php echo date("Y-m-d H:i:s", $user['regtime']); ?></td>
            <td><?php if (!APP_DEMO) {echo $user['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
            <td><a href="/admin/profile.php/?id=<?php echo $user['id']; ?>"><i class="material-icons">mode_edit</i></a></td>
        </tr>

        <?php
    }
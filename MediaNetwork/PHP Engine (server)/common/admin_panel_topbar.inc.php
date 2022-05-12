<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */


    if (!admin::isSession()) {

        ?>

        <nav class="light-blue lighten-1" role="navigation">
            <div class="nav-wrapper container">
                <a href="/" class="brand-logo"><?php echo APP_NAME; ?></a>
                <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li><a href="/admin/login.php">Log In</a></li>
                </ul>
                <ul class="side-nav" id="mobile-demo">
                    <li><a href="/admin/login.php">Log In</a></li>
                </ul>
            </div>
        </nav>

        <?php

    } else {

        ?>

        <header class="content">

            <div class="navbar-fixed">

                <ul id="dropdown1" class="dropdown-content">
                    <li><a href="/admin/support.php">Support</a></li>
                    <li><a href="/admin/settings.php">Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="/admin/logout.php/?access_token=<?php echo admin::getAccessToken(); ?>&continue=/" class="waves-effect waves-teal">Logout</a></li>
                </ul>

                <nav class="top-nav light-blue">
                    <div class="nav-wrapper">
                        <a href="#" data-activates="nav-mobile" class="button-collapse top-nav full">
                            <i class="large material-icons">reorder</i>
                        </a>
                        <a href="#" class="page-title">Admin Panel</a>

                        <ul class="right hide-on-med-and-down" style="margin-right: 250px;">
                            <li><a class="dropdown-button" href="#!" data-activates="dropdown1"><i style="padding-left: 0px;" class="material-icons right">more_vert</i><?php echo admin::getFullname(); ?></a></li>
                        </ul>

                    </div>
                </nav>
            </div>

            <ul id="nav-mobile" class="side-nav fixed" style="left: 0px;">

                <li class="collection-header grey lighten-5 center-align" style="line-height: normal"><br>
                    <img class="responsive-img" style="max-width: 60%" src="/img/panel_logo.png"><br>
                    <h4><?php echo APP_NAME; ?></h4>
                    <br>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "main") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/main.php" class="waves-effect waves-ripple"><b>General</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "gallery") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/gallery.php" class="waves-effect waves-ripple"><b>Gallery</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "stream") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/stream.php" class="waves-effect waves-ripple"><b>Stream</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "users") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/users.php" class="waves-effect waves-ripple"><b>Users</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "reports") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/reports.php" class="waves-effect waves-ripple"><b>Reports</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "app") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/app.php" class="waves-effect waves-ripple"><b>App Settings</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "gcm") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/fcm.php" class="waves-effect waves-ripple"><b>Push Notifications</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "support") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/support.php" class="waves-effect waves-ripple"><b>Support</b></a>
                </li>

                <li class="bold <?php if (isset($page_id) && $page_id === "settings") { echo "active grey lighten-3";} ?>">
                    <a href="/admin/settings.php" class="waves-effect waves-ripple"><b>Settings</b></a>
                </li>

                <li class="bold">
                    <a href="/admin/logout.php/?access_token=<?php echo admin::getAccessToken(); ?>&continue=/" class="waves-effect waves-ripple"><b>Logout</b></a>
                </li>

            </ul>

        </header>

        <?php
    }
?>
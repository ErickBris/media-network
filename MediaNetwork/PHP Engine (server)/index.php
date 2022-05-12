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

    $css_files = array("my.css");
    $page_title = APP_TITLE;

    include_once($_SERVER['DOCUMENT_ROOT']."/common/header.inc.php");
?>

<body>

<?php

    include_once($_SERVER['DOCUMENT_ROOT'] . "/common/admin_panel_topbar.inc.php");
?>

<div class="section no-pad-bot" id="index-banner">

    <div class="container" style="margin-top: 140px; margin-bottom: 140px;">
        <br><br>
        <h1 class="header center orange-text"><?php echo APP_NAME; ?></h1>

        <div class="row center">
            <h5 class="header col s12 light">Create your own <?php echo APP_NAME; ?> App now!</h5>
        </div>

        <div class="row center">
            <a href="<?php echo GOOGLE_PLAY_LINK; ?>">
                <button class="btn-large waves-effect waves-light teal">Download <?php echo APP_NAME; ?> from Google Play<i class="material-icons right">file_download</i></button>
            </a>
        </div>

        <br><br>
    </div>

</div>


<footer class="page-footer white" style="padding-top: 0px;">
    <div class="footer-copyright white">
        <div class="container <?php echo SITE_TEXT_COLOR; ?>">
            <span class="grey-text darken-2"><?php echo APP_TITLE; ?> © <?php echo APP_YEAR; ?></span>
            <span class="right"><a class="text-lighten-4 <?php echo SITE_TEXT_COLOR; ?>" target="_blank" href="<?php echo COMPANY_URL; ?>"><?php echo APP_VENDOR; ?></a></span>
        </div>
    </div>
</footer>

    <script type="text/javascript" src="/js/materialize.min.js"></script>

    <script src="/js/init.js"></script>


</body>
</html>
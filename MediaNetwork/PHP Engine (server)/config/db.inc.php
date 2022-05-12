<?php

    /*!
     * ifsoft.co.uk v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk, qascript@mail.ru
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

$C = array();
$B = array();

include_once($_SERVER['DOCUMENT_ROOT']."/config/constants.inc.php");

$B['APP_DEMO'] = false;                                     //true = enable demo version mode (only for Admin panel)

$B['SITE_THEME'] = "my-theme";                     			//Color Styles look here: http://materializecss.com/color.html
$B['SITE_TEXT_COLOR'] = "red-text text-darken-1";           //For menu items, icons and etc.. Color Styles look here: http://materializecss.com/color.html

$B['APP_SEND_SIGNUP_CONFIRM_EMAIL'] = false;                //Send email to confirm e-mail address of the user? false = no, true = yes
$B['APP_MESSAGES_COUNTERS'] = true;                         //true = show new messages counters
$B['APP_MYSQLI_EXTENSION'] = true;                          //if on the server is not installed mysqli extension, set to false
$B['APP_CONNECTION_TYPE'] = "http://";                      //you use http or https?
$B['APP_LANG'] = "en";                                      //for browser header | html lang | file common/header.inc.php

// Additional information. It does not affect the work application and website

$C['COMPANY_URL'] = "http://codecanyon.net/user/qascript/portfolio?ref=qascript";
$B['APP_SUPPORT_EMAIL'] = "qascript@mail.ru";
$B['APP_AUTHOR_PAGE'] = "qascript";
$B['APP_PATH'] = "app";
$B['APP_VERSION'] = "1";
$B['APP_AUTHOR'] = "Demyanchuk Dmitry";
$B['APP_VENDOR'] = "ifsoft.co.uk";

// Paths to folders for storing images and other files. Please, do not change!

$B['TEMP_PATH'] = "../tmp/";                                //don`t edit this option
$B['COVER_PATH'] = "../cover/";                             //don`t edit this option
$B['PHOTO_PATH'] = "../photo/";                             //don`t edit this option
$B['VIDEO_PATH'] = "../video/";                             //don`t edit this option
$B['GALLERY_PATH'] = "../gallery/";                         //don`t edit this option
$B['VIDEO_IMAGE_PATH'] = "../video_images/";                //don`t edit this option
$B['CHAT_IMAGE_PATH'] = "../chat_images/";                  //don`t edit this option
$B['ITEMS_PHOTO_PATH'] = "../items/";                       //don`t edit this option

// Link to GOOGLE Play App in main page

$B['GOOGLE_PLAY_LINK'] = "https://play.google.com/store/apps/details?id=ru.ifsoft.marketplace";

// Data for the title of the website and copyright

$B['APP_NAME'] = "Media Network";                           //
$B['APP_TITLE'] = "Media Network";                          //
$B['APP_YEAR'] = "2016";                                    // Year in footer

// Your domain (host) and url! See comments! Carefully!

$B['APP_HOST'] = "mnetwork.ifsoft.ru";                 //edit to your domain, example (WARNING - without http:// and www): yourdomain.com
$B['APP_URL'] = "http://mnetwork.ifsoft.ru";           //edit to your domain url, example (WARNING - with http://): http://yourdomain.com

// Client ID. For more information, see the documentation, FAQ section
// Warning! CLIENT_ID must be identical with CLIENT_ID from Constants.java (Android App Config file)

$B['CLIENT_ID'] = 1;                                 //Client ID | For identify you application | Example: 12567 (see documentation. section: faq)

// Firebase settings | For sending FCM (Firebase Cloud Messages) | http://ifsoft.co.uk/help/how_to_migrate_from_gcm_to_fcm/ and see documentation

$B['FIREBASE_SERVER_KEY'] = "AIzgaSyBzS41W2fcECxfaK4efZQ4p3okhtNnWVJm-DQ";
$B['FIREBASE_SENDER_ID'] = "34536547568567";

// SMTP Settings | For password recovery | Data for SMTP can ask your hosting provider
// These data are available on request from your web hosting provider!
// I do not know your server settings, and can not know even in theory! All hosting providers have their own settings.

$B['SMTP_HOST'] = 'yousite.com';                            //SMTP host | Specify main and backup SMTP servers
$B['SMTP_AUTH'] = true;                                     //SMTP auth (Enable SMTP authentication)
$B['SMTP_SECURE'] = 'tls';                                  //SMTP secure (Enable TLS encryption, `ssl` also accepted)
$B['SMTP_PORT'] = 587;                                      //SMTP port (TCP port to connect to)
$B['SMTP_EMAIL'] = 'support@yousite.com';                   //SMTP email
$B['SMTP_USERNAME'] = 'support@yousite.com';                //SMTP username
$B['SMTP_PASSWORD'] = 'you_email_password';                 //SMTP password

//Please edit database data

$C['DB_HOST'] = "localhost";                                //localhost or your db host
$C['DB_USER'] = "your db user";                             //your db user
$C['DB_PASS'] = "your db password";                         //your db password
$C['DB_NAME'] = "your db name";                             //your db name

// Languages.

$LANGS = array();
$LANGS['English'] = "en";
$LANGS['Русский'] = "ru";


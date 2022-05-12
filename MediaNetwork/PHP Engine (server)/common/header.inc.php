<!DOCTYPE html>
<html lang="<?php echo APP_LANG; ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="google-site-verification" content="" />
    <meta name='yandex-verification' content='' />
    <meta name="msvalidate.01" content="" />
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <link href="/img/favicon.png" rel="shortcut icon" type="image/x-icon">

    <script type="text/javascript" src="/js/jquery-2.1.1.js"></script>
    <script type="text/javascript" src="/js/materialize.min.js"></script>
    <script type="text/javascript" src="/js/jquery.cookie.js"></script>

    <link href="<?php echo APP_CONNECTION_TYPE; ?>fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="/css/materialize.css"  media="screen,projection"/>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <?php
        foreach($css_files as $css): ?>
        <link rel="stylesheet" href="/css/<?php echo $css."?x=1"; ?>" type="text/css" media="screen">
    <?php
        endforeach;
    ?>
</head>
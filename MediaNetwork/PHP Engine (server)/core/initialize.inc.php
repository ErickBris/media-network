<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

	try {

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS users (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  gcm_regid TEXT,
								  account_owner INT(10) UNSIGNED DEFAULT 0,
								  account_state INT(10) UNSIGNED DEFAULT 0,
								  account_type INT(10) UNSIGNED DEFAULT 0,
								  account_access_level INT(10) UNSIGNED DEFAULT 0,
								  device_model VARCHAR(125) NOT NULL DEFAULT '',
								  device_os VARCHAR(125) NOT NULL DEFAULT '',
								  device_lang VARCHAR(25) NOT NULL DEFAULT '',
								  first_name VARCHAR(125) NOT NULL DEFAULT '',
								  surname VARCHAR(125) NOT NULL DEFAULT '',
								  fullname VARCHAR(250) NOT NULL DEFAULT '',
								  salt CHAR(3) NOT NULL DEFAULT '',
								  passw VARCHAR(32) NOT NULL DEFAULT '',
								  username VARCHAR(50) NOT NULL DEFAULT '',
								  email VARCHAR(64) NOT NULL DEFAULT '',
								  email_verified SMALLINT(6) UNSIGNED DEFAULT 0,
								  phone VARCHAR(30) NOT NULL DEFAULT '',
								  phone_verified SMALLINT(6) UNSIGNED DEFAULT 0,
								  lang_code CHAR(10) DEFAULT 'en',
								  bYear SMALLINT(6) UNSIGNED DEFAULT 2000,
								  bMonth SMALLINT(6) UNSIGNED DEFAULT 0,
								  bDay SMALLINT(6) UNSIGNED DEFAULT 1,
								  status VARCHAR(500) NOT NULL DEFAULT '',
								  country VARCHAR(30) NOT NULL DEFAULT '',
								  country_id INT(10) UNSIGNED DEFAULT 0,
								  city VARCHAR(50) NOT NULL DEFAULT '',
								  city_id INT(10) UNSIGNED DEFAULT 0,
								  balance INT(11) UNSIGNED DEFAULT 10,
								  sex SMALLINT(6) UNSIGNED DEFAULT 0,
								  sex_orientation SMALLINT(6) UNSIGNED DEFAULT 0,
								  lat float(10,6) DEFAULT 0,
								  lng float(10,6) DEFAULT 0,
								  vk_page VARCHAR(150) NOT NULL DEFAULT '',
								  fb_page VARCHAR(150) NOT NULL DEFAULT '',
								  tw_page VARCHAR(150) NOT NULL DEFAULT '',
								  my_page VARCHAR(150) NOT NULL DEFAULT '',
								  verified SMALLINT(6) UNSIGNED DEFAULT 0,
								  vk_id varchar(40) DEFAULT 0,
								  fb_id varchar(40)	DEFAULT 0,
								  gl_id varchar(40) DEFAULT 0,
								  tw_id varchar(40) DEFAULT 0,
								  lasttime INT(10) UNSIGNED DEFAULT 0,
								  pin_item_id INT(11) UNSIGNED DEFAULT 0,
								  items_count INT(11) UNSIGNED DEFAULT 0,
								  comments_count INT(11) UNSIGNED DEFAULT 0,
								  friends_count INT(11) UNSIGNED DEFAULT 0,
								  likes_count INT(11) UNSIGNED DEFAULT 0,
								  reviews_count INT(11) UNSIGNED DEFAULT 0,
								  images_count INT(11) UNSIGNED DEFAULT 0,
								  videos_count INT(11) UNSIGNED DEFAULT 0,
								  gallery_items_count INT(11) UNSIGNED DEFAULT 0,
								  rating INT(11) UNSIGNED DEFAULT 0,
								  last_notifications_view INT(10) UNSIGNED DEFAULT 0,
								  last_messages_view INT(10) UNSIGNED DEFAULT 0,
								  last_friends_view INT(10) UNSIGNED DEFAULT 0,
								  last_authorize INT(10) UNSIGNED DEFAULT 0,
								  regtime INT(10) UNSIGNED DEFAULT 0,
								  ip_addr CHAR(32) NOT NULL DEFAULT '',
								  u_agent varchar(300) DEFAULT '',
								  allowMessagesFromAnyone SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowItemsFromFriends SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowFriendsRequestsGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowItemsComments SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowGalleryComments SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowLikesGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowCommentsGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowMessagesGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowCommentReplyGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowReviewsGCM SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowShowProfileOnlyToFriends SMALLINT(6) UNSIGNED DEFAULT 0,
								  allowShowOnline SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowShowPhoneNumber SMALLINT(6) UNSIGNED DEFAULT 1,
								  allowShowMyBirthday SMALLINT(6) UNSIGNED DEFAULT 0,
								  allowShowMyJoinDate SMALLINT(6) UNSIGNED DEFAULT 1,
								  photoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  normalPhotoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  photo_gallery_id INT(11) UNSIGNED DEFAULT 0,
								  coverUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  normalCoverUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								  cover_gallery_id INT(11) UNSIGNED DEFAULT 0,
  								PRIMARY KEY  (id), UNIQUE KEY (username)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS settings (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  name VARCHAR(150) NOT NULL DEFAULT '',
								  intValue INT(10) UNSIGNED DEFAULT 0,
								  textValue CHAR(32) NOT NULL DEFAULT '',
  								PRIMARY KEY  (id), UNIQUE KEY (name)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS admins (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								access_level INT(10) UNSIGNED DEFAULT 0,
								username VARCHAR(50) NOT NULL DEFAULT '',
                                salt CHAR(3) NOT NULL DEFAULT '',
                                password VARCHAR(32) NOT NULL DEFAULT '',
                                fullname VARCHAR(150) NOT NULL DEFAULT '',
                                email VARCHAR(64) NOT NULL DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS chats (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								fromUserId INT(11) UNSIGNED DEFAULT 0,
								toUserId INT(11) UNSIGNED DEFAULT 0,
								fromUserId_lastView INT(11) UNSIGNED DEFAULT 0,
								toUserId_lastView INT(11) UNSIGNED DEFAULT 0,
								image VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								video VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								message varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								messageCreateAt INT(11) UNSIGNED DEFAULT 0,
								updateAt INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt INT(11) UNSIGNED DEFAULT 0,
								removeToUserId int(11) UNSIGNED DEFAULT 0,
								removeFromUserId int(11) UNSIGNED DEFAULT 0,
								removeToUserIdAt int(11) UNSIGNED DEFAULT 0,
								removeFromUserIdAt int(11) UNSIGNED DEFAULT 0,
								PRIMARY KEY (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS messages (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								chatId int(11) UNSIGNED DEFAULT 0,
								msgType int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								toUserId int(11) UNSIGNED DEFAULT 0,
								message varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewVideoImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								videoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								audioUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								stickerId int(11) UNSIGNED DEFAULT 0,
								stickerImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								area VARCHAR(150) NOT NULL DEFAULT '',
								country VARCHAR(150) NOT NULL DEFAULT '',
								city VARCHAR(150) NOT NULL DEFAULT '',
								lat float(10,6),
								lng float(10,6),
								seenAt INT(11) UNSIGNED DEFAULT 0,
								createAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								removeFromUserId int(11) UNSIGNED DEFAULT 0,
								removeToUserId int(11) UNSIGNED DEFAULT 0,
								seenFromUserId int(11) UNSIGNED DEFAULT 0,
								seenToUserId int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS gallery (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								accessMode int(11) UNSIGNED DEFAULT 0,
								showInStream int(11) UNSIGNED DEFAULT 1,
								itemType int(11) UNSIGNED DEFAULT 0,
								likesCount int(11) UNSIGNED DEFAULT 0,
								commentsCount int(11) UNSIGNED DEFAULT 0,
								viewsCount int(11) UNSIGNED DEFAULT 0,
								rating int(11) UNSIGNED DEFAULT 0,
								description varchar(400) DEFAULT '',
								moderatedId int(11) UNSIGNED DEFAULT 0,
								moderatedAt int(11) UNSIGNED DEFAULT 0,
								originImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewVideoImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								videoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								area VARCHAR(150) NOT NULL DEFAULT '',
								country VARCHAR(150) NOT NULL DEFAULT '',
								city VARCHAR(150) NOT NULL DEFAULT '',
								lat float(10,6),
								lng float(10,6),
								u_agent varchar(300) DEFAULT '',
								createAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS items (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								rating int(11) UNSIGNED DEFAULT 0,
								itemType int(11) UNSIGNED DEFAULT 0,
								accessMode int(11) UNSIGNED DEFAULT 0,
								toUserId int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								likesCount int(11) UNSIGNED DEFAULT 0,
								imagesCount int(11) UNSIGNED DEFAULT 0,
								viewsCount int(11) UNSIGNED DEFAULT 0,
								reviewsCount int(11) UNSIGNED DEFAULT 0,
								reportsCount int(11) UNSIGNED DEFAULT 0,
								commentsCount int(11) UNSIGNED DEFAULT 0,
								rePostId int(11) UNSIGNED DEFAULT 0,
								rePostsCount int(11) UNSIGNED DEFAULT 0,
								allowComments int(11) UNSIGNED DEFAULT 1,
								itemContent TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewGifUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								gifUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								previewVideoImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								videoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								urlPreviewTitle VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								urlPreviewImage VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								urlPreviewDescription VARCHAR(400) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								urlPreviewLink VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								area VARCHAR(150) NOT NULL DEFAULT '',
								country VARCHAR(150) NOT NULL DEFAULT '',
								city VARCHAR(150) NOT NULL DEFAULT '',
								lat float(10,6),
								lng float(10,6),
								moderatedByAdminId int(11) UNSIGNED DEFAULT 0,
								moderatedAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								createAt int(11) UNSIGNED DEFAULT 0,
								modifyAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS comments (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								itemType int(11) UNSIGNED DEFAULT 0,
								itemId int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								replyToUserId int(11) UNSIGNED DEFAULT 0,
								likesCount int(11) UNSIGNED DEFAULT 0,
                                itemContent varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                previewImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
								imgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS likes (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								itemType int(11) UNSIGNED DEFAULT 0,
								itemId int(11) UNSIGNED DEFAULT 0,
                                toUserId int(11) UNSIGNED DEFAULT 0,
								fromUserId int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								createAt int(11) UNSIGNED DEFAULT 0,
								removeAt int(11) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS reviews (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								fromUserId int(11) UNSIGNED DEFAULT 0,
								toUserId int(11) UNSIGNED DEFAULT 0,
								rank int(11) UNSIGNED DEFAULT 0,
                                review varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                answer varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
                                createAt int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
                                notifyId int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS support (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								clientId int(11) UNSIGNED DEFAULT 0,
                                accountId int(11) UNSIGNED DEFAULT 0,
                                email varchar(64) DEFAULT '',
                                subject varchar(180) DEFAULT '',
                                text varchar(400) DEFAULT '',
                                reply varchar(400) DEFAULT '',
                                replyAt int(11) UNSIGNED DEFAULT 0,
                                replyFrom int(11) UNSIGNED DEFAULT 0,
                                removeAt int(11) UNSIGNED DEFAULT 0,
                                createAt int(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS notifications (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								notifyToId int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyFromId int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyType int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyToItemType int(11) UNSIGNED NOT NULL DEFAULT 0,
								notifyToItemId int(11) UNSIGNED NOT NULL DEFAULT 0,
								itemId int(11) UNSIGNED NOT NULL DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS friends (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								friend INT(11) UNSIGNED DEFAULT 0,
								friendTo INT(11) UNSIGNED DEFAULT 0,
								friendType INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt INT(11) UNSIGNED DEFAULT 0,
								PRIMARY KEY (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS friends_requests (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								fromUser INT(11) UNSIGNED DEFAULT 0,
								toUser INT(11) UNSIGNED DEFAULT 0,
								requestType INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt INT(11) UNSIGNED DEFAULT 0,
								PRIMARY KEY (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS access_data (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								accountId int(11) UNSIGNED NOT NULL,
								accessToken varchar(32) DEFAULT '',
								clientId int(11) UNSIGNED DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS restore_data (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								accountId int(11) UNSIGNED NOT NULL,
								hash varchar(32) DEFAULT '',
								email VARCHAR(64) NOT NULL DEFAULT '',
								clientId int(11) UNSIGNED DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS confirm_data (
								id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								accountId int(11) UNSIGNED NOT NULL,
								hash varchar(32) DEFAULT '',
								email VARCHAR(64) NOT NULL DEFAULT '',
								clientId int(11) UNSIGNED DEFAULT 0,
								createAt int(10) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS abuse_reports (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								abuseFromUserId INT(11) UNSIGNED DEFAULT 0,
								abuseToItemType INT(11) UNSIGNED DEFAULT 0,
								abuseToItemId INT(11) UNSIGNED DEFAULT 0,
								abuseId INT(11) UNSIGNED DEFAULT 0,
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt int(10) UNSIGNED DEFAULT 0,
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

        $sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS blacklist (
								id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
								blockedByUserId INT(11) UNSIGNED DEFAULT 0,
								blockedUserId INT(11) UNSIGNED DEFAULT 0,
								reason varchar(400) DEFAULT '',
								createAt INT(11) UNSIGNED DEFAULT 0,
								removeAt INT(11) UNSIGNED DEFAULT 0,
								u_agent varchar(300) DEFAULT '',
								ip_addr CHAR(32) NOT NULL DEFAULT '',
								PRIMARY KEY (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS gcm_reg_ids (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  gcm_regid TEXT,
								  createAt int(11) UNSIGNED DEFAULT 0,
								  u_agent varchar(300) DEFAULT '',
								  ip_addr CHAR(32) NOT NULL DEFAULT '',
  								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

		$sth = $dbo->prepare("CREATE TABLE IF NOT EXISTS gcm_history (
								  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								  msg VARCHAR(150) NOT NULL DEFAULT '',
								  addon VARCHAR(350) NOT NULL DEFAULT '',
								  fcmId INT(10) UNSIGNED DEFAULT 0,
								  msgType INT(10) UNSIGNED DEFAULT 0,
								  accountId int(11) UNSIGNED DEFAULT 0,
								  status INT(10) UNSIGNED DEFAULT 0,
								  success INT(10) UNSIGNED DEFAULT 0,
								  createAt int(10) UNSIGNED DEFAULT 0,
  								PRIMARY KEY  (id)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci");
		$sth->execute();

	} catch (Exception $e) {

		die ($e->getMessage());
	}

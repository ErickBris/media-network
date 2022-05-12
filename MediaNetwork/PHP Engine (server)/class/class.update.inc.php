<?php

    /*!
     * ifsoft.co.uk engine v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

    class update extends db_connect
    {
        public function __construct($dbo = NULL)
        {
            parent::__construct($dbo);
        }

        function setGalleryEmojiSupport()
        {
            $stmt = $this->db->prepare("ALTER TABLE gallery charset = utf8mb4, MODIFY COLUMN description VARCHAR(500) CHARACTER SET utf8mb4");
            $stmt->execute();
        }

        function setCommentEmojiSupport()
        {
            $stmt = $this->db->prepare("ALTER TABLE comments charset = utf8mb4, MODIFY COLUMN itemContent VARCHAR(800) CHARACTER SET utf8mb4");
            $stmt->execute();
        }

        function setChatEmojiSupport()
        {
            $stmt = $this->db->prepare("ALTER TABLE messages charset = utf8mb4, MODIFY COLUMN message VARCHAR(800) CHARACTER SET utf8mb4");
            $stmt->execute();
        }

        function setDialogsEmojiSupport()
        {
            $stmt = $this->db->prepare("ALTER TABLE chats charset = utf8mb4, MODIFY COLUMN message VARCHAR(800) CHARACTER SET utf8mb4");
            $stmt->execute();
        }

        function setAccountStatusEmojiSupport()
        {
            $stmt = $this->db->prepare("ALTER TABLE users charset = utf8mb4, MODIFY COLUMN status VARCHAR(500) CHARACTER SET utf8mb4");
            $stmt->execute();
        }
    }

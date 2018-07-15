CREATE DATABASE db_name DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS yii_user;
CREATE TABLE IF NOT EXISTS yii_user (
    uid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'User ID',
    email VARCHAR(50) NOT NULL COMMENT 'E-mail',
    password CHAR(32) NOT NULL COMMENT 'Password',
    salt CHAR(32) NOT NULL COMMENT 'Sucure Code',
    role ENUM('ADMIN','MODERATOR','USER') NOT NULL DEFAULT 'USER' COMMENT 'Role',
    time_create TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Create Time',
    time_update TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last Time',
    enabled tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - active и 0 - noactive',
    ip VARCHAR(15) NOT NULL DEFAULT '0' COMMENT 'IP address',
    PRIMARY KEY (`uid`),
    UNIQUE `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS yii_profile;
CREATE TABLE yii_profile (
    user_id INT(10) UNSIGNED NOT NULL COMMENT 'User ID',
    firstname VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'First Name',
    lastname VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Last Name',
    uimage VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'User Photo',
    about TEXT NOT NULL DEFAULT '' COMMENT 'User About',
    UNIQUE KEY `user_id` (`user_id`),
    CONSTRAINT `user_profile_id` FOREIGN KEY (`user_id`) REFERENCES `yii_user` (`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO yii_profile (user_id) VALUES ('1');

/**************************************************************************/

DROP TABLE IF EXISTS yii_expiredomains;
CREATE TABLE yii_expiredomains (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    domain VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Domain Name',
    cy INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Yandex CY',
    pr INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Google PR',
    dmoz ENUM('NA','YES','NO') NOT NULL DEFAULT 'NA' COMMENT 'Is DMOZ',
    dmoz_count INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'DMOZ Count',
    wa ENUM('NA','YES','NO') NOT NULL DEFAULT 'NA' COMMENT 'Is Web Archive',
    wa_count INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Web Archive Count',
    glue_cy VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Glue CY',
    glue_pr VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Glue PR',
    yaca VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Ya Catalog',
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time Check',  
    PRIMARY KEY (`id`),
    UNIQUE KEY `domain` (`domain`),
    KEY `main_index` (`cy`,`pr`,`dmoz`,`wa`,`glue_cy`,`glue_pr`,`yaca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS yii_interdomains;
CREATE TABLE yii_interdomains (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    domain VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Domain Name',
    cy INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Yandex CY',
    pr INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Google PR',
    dmoz ENUM('NA','YES','NO') NOT NULL DEFAULT 'NA' COMMENT 'Is DMOZ',
    dmoz_count INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'DMOZ Count',
    wa ENUM('NA','YES','NO') NOT NULL DEFAULT 'NA' COMMENT 'Is Web Archive',
    wa_count INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Web Archive Count',
    glue_cy VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Glue CY',
    glue_pr VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Glue PR',
    yaca VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Ya Catalog',
    interception TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 - оставить и 1 - перехватить',
    success TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 - не успешно и 1 - успешно',
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time Check',  
    PRIMARY KEY (`id`),
    KEY `main_index` (`domain`,`cy`,`pr`,`dmoz`,`wa`,`glue_cy`,`glue_pr`,`yaca`,`interception`,`success`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS yii_proxylist;
CREATE TABLE yii_proxylist (    
    ip VARCHAR(18) NOT NULL DEFAULT '' COMMENT 'Ip',
    port SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Port',
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
    KEY `main_index` (`ip`,`port`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE DATABASE udb_bount DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

/* Аккаунты фейков и их параметры */
DROP TABLE IF EXISTS yoo_accounts;
CREATE TABLE IF NOT EXISTS yoo_accounts (
  id INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  phone BIGINT NOT NULL,
  user_id BIGINT NOT NULL DEFAULT '0' COMMENT 'Vk user ID',
  pass VARCHAR(30) NOT NULL COMMENT 'Password',
  access_token VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Vk access token',
  secret VARCHAR(255) NOT NULL DEFAULT '',
  count_friends INT(10) NOT NULL DEFAULT '0',
  count_followers INT(10) NOT NULL DEFAULT '0',
  sex ENUM('NONE','MALE','FEMALE') NOT NULL DEFAULT 'NONE',
  soc ENUM('VK','OK','FB') NOT NULL DEFAULT 'VK',
  time_create TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Create Time',
  status ENUM('ACTIVE','UNDERWAY','SALE','SOLD','DISABLED','BLOCKED','TEST','GROUP') NOT NULL DEFAULT 'ACTIVE',
  item_id BIGINT NOT NULL DEFAULT '0' COMMENT 'Item ID in shop',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Динамически обновляемая таблица со списком пользователей, найденных по критериям */
DROP TABLE IF EXISTS yoo_users_searched;
CREATE TABLE yoo_users_searched (
  list_id INT(10) UNSIGNED NOT NULL COMMENT 'List ID',
  user_id INT(10) UNSIGNED NOT NULL COMMENT 'Vk user ID',
  first_name VARCHAR(255) NOT NULL DEFAULT '',
  sex ENUM('M','F','N') NOT NULL DEFAULT 'N',
  processed ENUM('Y','N') NOT NULL DEFAULT 'N',
  updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time Online Updated',
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*Динамический список прокси (здесь не используется)*/
DROP TABLE IF EXISTS yoo_proxylist;
CREATE TABLE yoo_proxylist (
  ip VARCHAR(18) NOT NULL DEFAULT '' COMMENT 'Ip',
  port SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Port',
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
  KEY `main_index` (`ip`,`port`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*Рабочий список прокси, которые привязаны к аккаунтам*/
DROP TABLE IF EXISTS yoo_proxy_vip;
CREATE TABLE yoo_proxy_vip (
  user_id INT(10) UNSIGNED NOT NULL COMMENT 'User ID',
  ip_port VARCHAR(30) NOT NULL DEFAULT '',
  login_pass VARCHAR(30) NOT NULL DEFAULT '',
  timecreate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  timefinish TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*Список купленных прокси с датой окончания их действия*/
DROP TABLE IF EXISTS yoo_proxy_assigned;
CREATE TABLE yoo_proxy_assigned (
  ip_port VARCHAR(30) NOT NULL DEFAULT '',
  login_pass VARCHAR(30) NOT NULL DEFAULT '',
  timecreate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  timefinish TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*База различных соц.статусов (используется для постов аккаунтов на свои стены)*/
DROP TABLE IF EXISTS yoo_db_status;
CREATE TABLE yoo_db_status (
  id INT(10) NOT NULL AUTO_INCREMENT,
  data TEXT NOT NULL DEFAULT '',
  used ENUM('N','Y') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*-------------------------------------------------------------*/
/* Динамически обновляемая таблица со объявлений (сдам квартиру - москва) */
DROP TABLE IF EXISTS rilty_parse_sdam_mos;
CREATE TABLE rilty_parse_sdam_mos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  item BIGINT(18) NOT NULL,
  date VARCHAR(20) NOT NULL DEFAULT '',
  title VARCHAR(100) NOT NULL DEFAULT '',
  addr VARCHAR(255) NOT NULL DEFAULT '',
  descipt TEXT NOT NULL DEFAULT '',
  photos TEXT NOT NULL DEFAULT '',
  price VARCHAR(20) NOT NULL DEFAULT '',
  contact_name VARCHAR(50) NOT NULL DEFAULT '',
  contact_phone VARCHAR(20) NOT NULL DEFAULT '',
  published ENUM('Y','N') NOT NULL DEFAULT 'N',
  timeadd TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*Динамический список прокси для Авито*/
DROP TABLE IF EXISTS rilty_proxylist;
CREATE TABLE rilty_proxylist (
  ip VARCHAR(18) NOT NULL DEFAULT '' COMMENT 'Ip',
  port SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Port',
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
  KEY `main_index` (`ip`,`port`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

/*-------------------------------------------------------------*/
/* Таблица с сайтами, сделанных на битриксе (для рассылки предложений) */
DROP TABLE IF EXISTS bitrix_sites_info;
CREATE TABLE bitrix_sites_info (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  site VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  phone VARCHAR(255) NOT NULL DEFAULT '',
  category VARCHAR(255) NOT NULL DEFAULT '',
  types VARCHAR(255) NOT NULL DEFAULT '',
  status ENUM('NOFOUND','NOSITE','SUCCESS','NONE') NOT NULL DEFAULT 'NONE',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
-- Adminer 4.1.1-dev MySQL dump

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `wechatcgi_attachments`;
CREATE TABLE `wechatcgi_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `title` varchar(100) DEFAULT NULL COMMENT '文件标题',
  `user_id` int(10) unsigned NOT NULL COMMENT '所属用户',
  `filename` varchar(255) DEFAULT NULL COMMENT '原始文件名',
  `filesize` mediumint(8) unsigned NOT NULL COMMENT '文件大小',
  `path` varchar(255) DEFAULT NULL COMMENT '附件路径',
  `mime` varchar(80) DEFAULT NULL COMMENT 'mime',
  `filetype` varchar(40) DEFAULT NULL COMMENT '文件后缀',
  `description` text COMMENT '文件说明',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='附件表';
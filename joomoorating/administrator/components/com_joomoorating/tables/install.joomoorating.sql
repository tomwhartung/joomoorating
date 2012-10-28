#
# @author      Tom Hartung <webmaster@tomhartung.com>
# @database    MySql
# @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
# @license     TBD
#
# 
#  SQL to create jos_joomoorating table
#
DROP TABLE IF EXISTS `jos_joomoorating`;
CREATE TABLE IF NOT EXISTS `jos_joomoorating`
(
	`id` int(11) unsigned NOT NULL DEFAULT NULL AUTO_INCREMENT,
	`contentid`      int(11) NULL DEFAULT NULL,
	`galleryimageid` int(11) NULL DEFAULT NULL,
	`vote_count`     int(11) NULL DEFAULT NULL,
	`vote_total`     int(11) NULL DEFAULT NULL,
	`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY (`contentid`),
	KEY (`galleryimageid`)
) CHARACTER SET `utf8` COLLATE `utf8_general_ci`;


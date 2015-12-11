/*
 * @author		  Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        11.12.2015
 */
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for member_access_right
-- ----------------------------
DROP TABLE IF EXISTS `member_access_right`;
CREATE TABLE `member_access_right` (
  `member` int(10) unsigned NOT NULL COMMENT 'Member who takes the action.',
  `action` int(5) unsigned NOT NULL COMMENT 'Action that member logged.',
  `right` varchar(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'g' COMMENT 'Access right.',
  `date_assigned` datetime NOT NULL COMMENT 'Date when the right is assigned.',
  PRIMARY KEY (`member`,`action`),
  UNIQUE KEY `idxUMemberAccessRight` (`member`,`action`) USING BTREE,
  KEY `idxNMemberAccessRightDateAssigned` (`date_assigned`) USING BTREE,
  KEY `idxFActionGrantedToMember` (`action`) USING BTREE,
  CONSTRAINT `idxFMemberGrantedAction` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idxFActionGrantedToMember` FOREIGN KEY (`action`) REFERENCES `action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for member_group_access_right
-- ----------------------------
DROP TABLE IF EXISTS `member_group_access_right`;
CREATE TABLE `member_group_access_right` (
  `member_group` int(10) unsigned NOT NULL COMMENT 'Member group.',
  `action` int(5) unsigned NOT NULL COMMENT 'Action.',
  `right` varchar(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'g' COMMENT 'g:granted,r:revoked',
  `date_assigned` datetime NOT NULL COMMENT 'Date when the right is assigned.',
  PRIMARY KEY (`member_group`,`action`),
  UNIQUE KEY `idxUMemberGroupAccessRight` (`member_group`,`action`) USING BTREE,
  KEY `idxFActionGrantedToMemberGroup` (`action`) USING BTREE,
  KEY `idxNMemberGroupAccessRightDateAssigned` (`date_assigned`) USING BTREE,
  CONSTRAINT `idxFActionGrantedToMemberGroup` FOREIGN KEY (`action`) REFERENCES `action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idxFMemberGroupGrantedAction` FOREIGN KEY (`member_group`) REFERENCES `member_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

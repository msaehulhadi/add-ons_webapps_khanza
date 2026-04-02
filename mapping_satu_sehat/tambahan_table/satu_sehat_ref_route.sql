SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for satu_sehat_ref_route
-- ----------------------------
DROP TABLE IF EXISTS `satu_sehat_ref_route`;
CREATE TABLE `satu_sehat_ref_route`  (
  `code` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `display` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`code`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of satu_sehat_ref_route
-- ----------------------------
INSERT INTO `satu_sehat_ref_route` VALUES ('Inhal', 'Inhalasi (Uap)');
INSERT INTO `satu_sehat_ref_route` VALUES ('N', 'Nasal (Hidung)');
INSERT INTO `satu_sehat_ref_route` VALUES ('O', 'Oral (Minum)');
INSERT INTO `satu_sehat_ref_route` VALUES ('P', 'Parenteral (Suntik/Infus)');
INSERT INTO `satu_sehat_ref_route` VALUES ('R', 'Rectal (Anus)');
INSERT INTO `satu_sehat_ref_route` VALUES ('Topical', 'Topikal (Oles/Kulit/Mata/Telinga)');
INSERT INTO `satu_sehat_ref_route` VALUES ('V', 'Vaginal');

SET FOREIGN_KEY_CHECKS = 1;

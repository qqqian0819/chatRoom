
-- 创建comet表
CREATE TABLE `tb_comet` (
  `pos` char(30) CHARACTER SET utf8 NOT NULL,
  `receiver` char(30) CHARACTER SET utf8 NOT NULL,
  `content` varchar(255) CHARACTER SET utf8 NOT NULL,
  `flag` tinyint(8) unsigned zerofill NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1
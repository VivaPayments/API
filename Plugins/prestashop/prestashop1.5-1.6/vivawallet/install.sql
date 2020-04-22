DROP TABLE IF EXISTS `vivawallet_data`;
CREATE TABLE IF NOT EXISTS `vivawallet_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `secure_key` varchar(150) DEFAULT NULL,
  `OrderCode` varchar(255) DEFAULT NULL,
  `ErrorCode` varchar(50) DEFAULT NULL,
  `ErrorText` varchar(255) DEFAULT NULL,
  `Timestamp` datetime DEFAULT NULL,
  `ref` varchar(150) DEFAULT NULL,
  `total_cost` int(11) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `order_state` char(1) DEFAULT NULL,
  `sessionid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

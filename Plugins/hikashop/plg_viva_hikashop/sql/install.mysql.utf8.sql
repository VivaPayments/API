CREATE TABLE IF NOT EXISTS `#__vivadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(150) NOT NULL,
  `orderid` varchar(150) NOT NULL,
  `ordercode` varchar(255) NULL,
  `total_cost` varchar(50) NOT NULL,
  `locale` varchar(50) NOT NULL,
  `period` varchar(50) NOT NULL,
  `itemid` varchar(150) NOT NULL,
  `currency` char(3) NOT NULL,
  `order_state` char(1) NOT NULL,
  `timestamp` datetime default null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
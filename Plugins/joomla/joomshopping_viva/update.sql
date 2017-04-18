CREATE TABLE IF NOT EXISTS `#__vivadata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ref` varchar(150) NOT NULL,
  `orderid` varchar(150) NOT NULL,
  `total_cost` varchar(50) NOT NULL,
  `locale` varchar(50) NOT NULL,
  `period` varchar(50) NOT NULL,
  `ordercode` varchar(255) NOT NULL,
  `errorcode` varchar(150) NOT NULL,
  `errortext` text NOT NULL,
  `okurl` text NOT NULL,
  `failurl` text NOT NULL,
  `gatewayurl` text NOT NULL,
  `currency` char(3) NOT NULL,
  `order_state` char(1) NOT NULL,
  `timestamp` datetime default null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__jshopping_payment_method` (`payment_code`, `payment_class`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `price`, `price_type`, `tax_id`, `image`, `show_descr_in_email`, `name_en-GB`, `description_en-GB`, `name_de-DE`, `description_de-DE`) VALUES
('VIV', 'pm_viva', 0, 6, 'user_id=00000\nproject_id=00000\nproject_password=00000\ntransaction_end_status=6\ntransaction_pending_status=1\ntransaction_failed_status=3\n', 2, 0.00, 0, 1, '', 0, 'Viva Payments', '', 'Viva Payments', '');
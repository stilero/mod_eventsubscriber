DROP TABLE IF EXISTS `#__eventsubscriber_subsctiptions`;
CREATE TABLE IF NOT EXISTS `#__eventsubscriber_subsctiptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `lastvisit` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
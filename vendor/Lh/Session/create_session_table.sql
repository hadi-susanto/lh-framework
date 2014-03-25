CREATE TABLE `session_data` (
  `id` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_accessed` datetime NOT NULL,
  `session` text,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
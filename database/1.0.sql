CREATE TABLE `cb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8 NOT NULL,
  `password` varchar(50) COLLATE utf8 NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `cb_users`
  ADD PRIMARY KEY (`id`);

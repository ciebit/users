CREATE TABLE `cb_users` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cb_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cb_users`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

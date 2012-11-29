
--
-- Database: `njia`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_table`
--

CREATE TABLE IF NOT EXISTS `access_table` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `access_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci;

--
-- Initializing data for table `access_table`
--

INSERT INTO `access_table` (
    `user_id`, `project_id`, `access_creation_date`
) VALUES (
    1, 1, NOW()
);

-- --------------------------------------------------------

--
-- Table structure for table `log_table`
--

CREATE TABLE IF NOT EXISTS `log_table` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `work_hours` float NOT NULL DEFAULT '0',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf16_unicode_ci,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_table`
--

CREATE TABLE IF NOT EXISTS `project_table` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `project_discussion` text COLLATE utf8_unicode_ci,
  `project_status` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `project_owner` int(11) NOT NULL,
  `project_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Initializing data for table `project_table`
--

INSERT INTO `project_table` (
    `project_name`, `project_discussion`, `project_status`, `project_owner`, `project_created_date`
) VALUES (
    'Njia administration', 'This is an ongoing project for tracking administration, maintenance, and support tasks for Njia.', 'open', 1, NOW()
);

-- --------------------------------------------------------

--
-- Table structure for table `session_table`
--

CREATE TABLE IF NOT EXISTS `session_table` (
  `session_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_expiration_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_table`
--

CREATE TABLE IF NOT EXISTS `task_table` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `parent_task_id` int(11) DEFAULT NULL,
  `task_summary` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `task_discussion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `user_id` int(11) DEFAULT NULL COMMENT 'task assignee',
  `timebox_id` int(11) DEFAULT NULL,
  `task_status` enum('open','closed') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `task_created_date` timestamp NULL DEFAULT NULL,
  `task_modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci AUTO_INCREMENT=1 ;

--
-- Initializing data for table `task_table`
--

INSERT INTO `task_table` (
    `project_id`, `task_summary`, `task_discussion`, `user_id`, `timebox_id`, `task_status`, `task_created_date`
) VALUES (
    1, 'Change admin password', 'Njia is installed with a default administrator name and password.  To protect access to the Njia system and database, these should be changed as soon as Njia is installed.', 1, 1, 'open', NOW()
);

-- --------------------------------------------------------

--
-- Table structure for table `timebox_table`
--

CREATE TABLE IF NOT EXISTS `timebox_table` (
  `timebox_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `timebox_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `timebox_discussion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `timebox_end_date` date NOT NULL,
  PRIMARY KEY (`timebox_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf16 COLLATE=utf16_unicode_ci AUTO_INCREMENT=1 ;

--
-- Initializing data for table `timebox_table`
--

INSERT INTO `timebox_table` (
    `project_id`, `timebox_name`, `timebox_discussion`, `timebox_end_date`
) VALUES (
    1, 'Njia installation', 'This timebox is set up automatically when Njia is installed, with an end date one week after installation.  Use this timebox to track any remaining tasks related to the installation and setup of Njia.', DATE(DATE_ADD(NOW(), INTERVAL 7 DAY))
);

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE IF NOT EXISTS `user_table` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'MD5(CONCAT(`password_salt`, $password))',
  `password_salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_permissions` tinytext COLLATE utf8_unicode_ci,
  `user_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `account_closed_date` timestamp NULL DEFAULT NULL COMMENT 'Set NULL for active accounts',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login_name` (`login_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Initializing data for table `user_table`
--

INSERT INTO `user_table` (
    `login_name`, `password`, `password_salt`, `user_permissions`, `user_creation_date`, `account_closed_date`
) VALUES (
    'admin', MD5(CONCAT(`password_salt`, 'password')), '8533595bc24d29a06e1893d61b90b71b', 'admin', '2012-07-01 19:38:15', NULL
);

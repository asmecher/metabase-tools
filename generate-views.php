<?php

/**
 * Generate and output the SQL required to (re)create the views for a single journal from a multi-journal instance.
 */

use Illuminate\Database\Capsule\Manager as Capsule;

require_once('vendor/autoload.php');

// Load the configuration file
$config = require_once('config/config.php');

// Establish the database connection for multijournal installation
$capsule = new Capsule;
$capsule->addConnection($config['databases']['multi'], 'multi');
$db = $capsule->getConnection('multi')->getDoctrineConnection();
$sm = $db->createSchemaManager();

$multiDatabaseName = $config['databases']['multi']['database'];

echo "CREATE DATABASE IF NOT EXISTS `{$config['databases']['single']['database']}` DEFAULT CHARACTER SET utf8;
CREATE USER `{$config['databases']['single']['username']}`@`{$config['databases']['single']['host']}` IDENTIFIED BY " . $db->quote($config['databases']['single']['password']) . ";
GRANT SELECT ON `{$config['databases']['single']['database']}`.* TO `{$config['databases']['single']['username']}`@`{$config['databases']['single']['host']}`;
USE `{$config['databases']['single']['database']}`;
CREATE OR REPLACE VIEW journals AS SELECT j.* FROM {$multiDatabaseName}.journals AS j WHERE j.path=" . $db->quote($config['journalPath']) . ";
CREATE OR REPLACE VIEW journal_settings AS SELECT js.* FROM {$multiDatabaseName}.journal_settings AS js JOIN journals j ON (js.journal_id = j.journal_id);
CREATE OR REPLACE VIEW submissions AS SELECT s.* FROM {$multiDatabaseName}.submissions AS s JOIN journals j ON (s.context_id = j.journal_id);
CREATE OR REPLACE VIEW submission_settings AS SELECT ss.* FROM {$multiDatabaseName}.submission_settings AS ss JOIN submissions AS s ON (ss.submission_id = s.submission_id);
CREATE OR REPLACE VIEW publications AS SELECT p.* FROM {$multiDatabaseName}.publications AS p JOIN submissions AS s ON (p.submission_id = s.submission_id);
CREATE OR REPLACE VIEW publication_settings AS SELECT ps.* FROM {$multiDatabaseName}.publication_settings AS ps JOIN publications AS p ON (ps.publication_id = p.publication_id);
CREATE OR REPLACE VIEW review_rounds AS SELECT rr.* FROM {$multiDatabaseName}.review_rounds AS rr JOIN submissions AS s ON (rr.submission_id = s.submission_id);
CREATE OR REPLACE VIEW authors AS SELECT a.* FROM {$multiDatabaseName}.authors AS a JOIN publications AS p ON (a.publication_id = p.publication_id);
CREATE OR REPLACE VIEW author_settings AS SELECT a_s.* FROM {$multiDatabaseName}.author_settings AS a_s JOIN authors AS a ON (a_s.author_id = a.author_id);
CREATE OR REPLACE VIEW edit_decisions AS SELECT ed.* FROM {$multiDatabaseName}.edit_decisions AS ed JOIN submissions AS s ON (ed.submission_id = s.submission_id);
CREATE OR REPLACE VIEW issues AS SELECT i.* FROM {$multiDatabaseName}.issues AS i JOIN journals j ON (i.journal_id = j.journal_id);
CREATE OR REPLACE VIEW sections AS SELECT s.* FROM {$multiDatabaseName}.sections AS s JOIN journals j ON (s.journal_id = j.journal_id);
CREATE OR REPLACE VIEW section_settings AS SELECT ss.* FROM {$multiDatabaseName}.section_settings AS ss JOIN sections s ON (ss.section_id = s.section_id);
CREATE OR REPLACE VIEW stage_assignments AS SELECT sa.* FROM {$multiDatabaseName}.stage_assignments AS sa JOIN submissions AS s ON (sa.submission_id = s.submission_id);
CREATE OR REPLACE VIEW user_groups AS SELECT ug.* FROM {$multiDatabaseName}.user_groups AS ug JOIN journals j ON (ug.context_id = j.journal_id);
CREATE OR REPLACE VIEW user_group_settings AS SELECT ugs.* FROM {$multiDatabaseName}.user_group_settings AS ugs JOIN user_groups ug ON (ug.user_group_id = ugs.user_group_id);
CREATE OR REPLACE VIEW user_user_groups AS SELECT uug.* FROM {$multiDatabaseName}.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);
CREATE OR REPLACE VIEW user_user_groups AS SELECT uug.* FROM {$multiDatabaseName}.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);
";

// For the users table, we want to exclude the password column.
$columns = array_filter(array_map(fn($c) => $c->getName() != 'password' ? $c->getName() : null, $sm->listTableColumns('users')));
echo "CREATE OR REPLACE VIEW users AS SELECT u." . implode(', u.', $columns) . " FROM {$multiDatabaseName}.users AS u WHERE u.user_id IN (SELECT user_id FROM user_user_groups);\n";

echo "CREATE OR REPLACE VIEW user_settings AS SELECT us.* FROM {$multiDatabaseName}.user_settings AS us JOIN users u ON (us.user_id = u.user_id) WHERE setting_name NOT IN ('orcidAccessExpiresOn', 'orcidAccessScope', 'orcidAccessToken', 'orcidRefreshToken', 'orcidSandbox', 'apiKey', 'apiKeyEnabled');
";

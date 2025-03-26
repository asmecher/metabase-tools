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

$tableOrView = ($config['materializedViews'] ?? false) ? 'TABLE' : 'VIEW';

echo "CREATE DATABASE IF NOT EXISTS `{$config['databases']['single']['database']}` DEFAULT CHARACTER SET utf8;
CREATE USER IF NOT EXISTS `{$config['databases']['single']['username']}`@`{$config['databases']['single']['host']}` IDENTIFIED BY " . $db->quote($config['databases']['single']['password']) . ";
GRANT SELECT ON `{$config['databases']['single']['database']}`.* TO `{$config['databases']['single']['username']}`@`{$config['databases']['single']['host']}`;
USE `{$config['databases']['single']['database']}`;

DROP {$tableOrView} IF EXISTS journals;
CREATE {$tableOrView} journals AS SELECT j.* FROM {$multiDatabaseName}.journals AS j WHERE j.path=" . $db->quote($config['journalPath']) . ";

DROP {$tableOrView} IF EXISTS journal_settings;
CREATE {$tableOrView} journal_settings AS SELECT js.* FROM {$multiDatabaseName}.journal_settings AS js JOIN journals j ON (js.journal_id = j.journal_id);

DROP {$tableOrView} IF EXISTS submissions;
CREATE {$tableOrView} submissions AS SELECT s.* FROM {$multiDatabaseName}.submissions AS s JOIN journals j ON (s.context_id = j.journal_id);

DROP {$tableOrView} IF EXISTS genres;
CREATE {$tableOrView} genres AS SELECT g.* FROM {$multiDatabaseName}.genres AS g JOIN journals j ON (g.context_id = j.journal_id);

DROP {$tableOrView} IF EXISTS submission_settings;
CREATE {$tableOrView} submission_settings AS SELECT ss.* FROM {$multiDatabaseName}.submission_settings AS ss JOIN submissions AS s ON (ss.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS publications;
CREATE {$tableOrView} publications AS SELECT p.* FROM {$multiDatabaseName}.publications AS p JOIN submissions AS s ON (p.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS publication_settings;
CREATE {$tableOrView} publication_settings AS SELECT ps.* FROM {$multiDatabaseName}.publication_settings AS ps JOIN publications AS p ON (ps.publication_id = p.publication_id);

DROP {$tableOrView} IF EXISTS review_rounds;
CREATE {$tableOrView} review_rounds AS SELECT rr.* FROM {$multiDatabaseName}.review_rounds AS rr JOIN submissions AS s ON (rr.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS review_round_files;
CREATE {$tableOrView} review_round_files AS SELECT rrf.* FROM {$multiDatabaseName}.review_round_files AS rrf JOIN review_rounds AS rr ON (rrf.review_round_id = rr.review_round_id);

DROP {$tableOrView} IF EXISTS authors;
CREATE {$tableOrView} authors AS SELECT a.* FROM {$multiDatabaseName}.authors AS a JOIN publications AS p ON (a.publication_id = p.publication_id);

DROP {$tableOrView} IF EXISTS author_settings;
CREATE {$tableOrView} author_settings AS SELECT a_s.* FROM {$multiDatabaseName}.author_settings AS a_s JOIN authors AS a ON (a_s.author_id = a.author_id);

DROP {$tableOrView} IF EXISTS edit_decisions;
CREATE {$tableOrView} edit_decisions AS SELECT ed.* FROM {$multiDatabaseName}.edit_decisions AS ed JOIN submissions AS s ON (ed.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS submission_files;
CREATE {$tableOrView} submission_files AS SELECT sf.* FROM {$multiDatabaseName}.submission_files AS sf JOIN submissions AS s ON (sf.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS files;
CREATE {$tableOrView} files AS SELECT f.* FROM {$multiDatabaseName}.files AS f JOIN submission_files AS sf ON (f.file_id = sf.file_id);

DROP {$tableOrView} IF EXISTS issues;
CREATE {$tableOrView} issues AS SELECT i.* FROM {$multiDatabaseName}.issues AS i JOIN journals j ON (i.journal_id = j.journal_id);

DROP {$tableOrView} IF EXISTS sections;
CREATE {$tableOrView} sections AS SELECT s.* FROM {$multiDatabaseName}.sections AS s JOIN journals j ON (s.journal_id = j.journal_id);

DROP {$tableOrView} IF EXISTS section_settings;
CREATE {$tableOrView} section_settings AS SELECT ss.* FROM {$multiDatabaseName}.section_settings AS ss JOIN sections s ON (ss.section_id = s.section_id);

DROP {$tableOrView} IF EXISTS stage_assignments;
CREATE {$tableOrView} stage_assignments AS SELECT sa.* FROM {$multiDatabaseName}.stage_assignments AS sa JOIN submissions AS s ON (sa.submission_id = s.submission_id);

DROP {$tableOrView} IF EXISTS user_groups;
CREATE {$tableOrView} user_groups AS SELECT ug.* FROM {$multiDatabaseName}.user_groups AS ug JOIN journals j ON (ug.context_id = j.journal_id);

DROP {$tableOrView} IF EXISTS user_group_settings;
CREATE {$tableOrView} user_group_settings AS SELECT ugs.* FROM {$multiDatabaseName}.user_group_settings AS ugs JOIN user_groups ug ON (ug.user_group_id = ugs.user_group_id);

DROP {$tableOrView} IF EXISTS user_user_groups;
CREATE {$tableOrView} user_user_groups AS SELECT uug.* FROM {$multiDatabaseName}.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);

DROP {$tableOrView} IF EXISTS user_user_groups;
CREATE {$tableOrView} user_user_groups AS SELECT uug.* FROM {$multiDatabaseName}.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);
";

// For the users table, we want to exclude the password column.
$columns = array_filter(array_map(fn($c) => $c->getName() != 'password' ? $c->getName() : null, $sm->listTableColumns('users')));
echo "DROP {$tableOrView} IF EXISTS users; CREATE {$tableOrView} users AS SELECT u." . implode(', u.', $columns) . " FROM {$multiDatabaseName}.users AS u WHERE u.user_id IN (SELECT user_id FROM user_user_groups);\n";

echo "DROP {$tableOrView} IF EXISTS user_settings; CREATE {$tableOrView} user_settings AS SELECT us.* FROM {$multiDatabaseName}.user_settings AS us JOIN users u ON (us.user_id = u.user_id) WHERE setting_name NOT IN ('orcidAccessExpiresOn', 'orcidAccessScope', 'orcidAccessToken', 'orcidRefreshToken', 'orcidSandbox', 'apiKey', 'apiKeyEnabled');
";

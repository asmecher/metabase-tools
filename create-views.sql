-- This script creates a series of views that present a subset of journal data.
-- This can be used to virtually partition a multi-journal instance of OJS into
-- a single-journal view of it, from a database perspective.

-- To use:
--  - Adapt "rlae" to the name of the target (empty) database in the first 3 lines
--  - Adapt "scielo" throughout the script to the name of the main (multijournal) database

CREATE DATABASE rlae;

USE rlae;

CREATE VIEW journals AS SELECT j.* FROM scielo.journals AS j WHERE j.path IN ('rlae');

CREATE VIEW journal_settings AS SELECT js.* FROM scielo.journal_settings AS js JOIN journals j ON (js.journal_id = j.journal_id);

CREATE VIEW submissions AS SELECT s.* FROM scielo.submissions AS s JOIN journals j ON (s.context_id = j.journal_id);

CREATE VIEW submission_settings AS SELECT ss.* FROM scielo.submission_settings AS ss JOIN submissions AS s ON (ss.submission_id = s.submission_id);

CREATE VIEW publications AS SELECT p.* FROM scielo.publications AS p JOIN submissions AS s ON (p.submission_id = s.submission_id);

CREATE VIEW publication_settings AS SELECT ps.* FROM scielo.publication_settings AS ps JOIN publications AS p ON (ps.publication_id = p.publication_id);

CREATE VIEW authors AS SELECT a.* FROM scielo.authors AS a JOIN publications AS p ON (a.publication_id = p.publication_id);

CREATE VIEW author_settings AS SELECT a_s.* FROM scielo.author_settings AS a_s JOIN authors AS a ON (a_s.author_id = a.author_id);

CREATE VIEW edit_decisions AS SELECT ed.* FROM scielo.edit_decisions AS ed JOIN submissions AS s ON (ed.submission_id = s.submission_id);

CREATE VIEW issues AS SELECT i.* FROM scielo.issues AS i JOIN journals j ON (i.journal_id = j.journal_id);

CREATE VIEW sections AS SELECT s.* FROM scielo.sections AS s JOIN journals j ON (s.journal_id = j.journal_id);

CREATE VIEW section_settings AS SELECT ss.* FROM scielo.section_settings AS ss JOIN sections s ON (ss.section_id = s.section_id);

CREATE VIEW stage_assignments AS SELECT sa.* FROM scielo.stage_assignments AS sa JOIN submissions AS s ON (sa.submission_id = s.submission_id);

CREATE VIEW user_groups AS SELECT ug.* FROM scielo.user_groups AS ug JOIN journals j ON (ug.context_id = j.journal_id);

CREATE VIEW user_group_settings AS SELECT ugs.* FROM scielo.user_group_settings AS ugs JOIN user_groups ug ON (ug.user_group_id = ugs.user_group_id);

CREATE VIEW user_user_groups AS SELECT uug.* FROM scielo.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);

CREATE VIEW user_user_groups AS SELECT uug.* FROM scielo.user_user_groups AS uug JOIN user_groups ug ON (uug.user_group_id = ug.user_group_id);

CREATE VIEW users AS SELECT u.* FROM scielo.users AS u WHERE u.user_id IN (SELECT user_id FROM user_user_groups);

CREATE VIEW user_settings AS SELECT us.* FROM scielo.user_settings AS us JOIN users u ON (us.user_id = u.user_id);

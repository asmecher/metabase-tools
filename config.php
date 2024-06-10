<?php

// Database connection defaults. You probably don't have to change these.
$dbDefaults = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
];

return [
    // Your Metabase information.
    'metabase' => [
	'baseUrl' => 'http://localhost:3000',
	'apiKey' => getenv('METABASE_API_KEY'), // This can be created in Metabase
	'mbApiKey' => getenv('METABASE_MB_API_KEY'), // This must be provided to Metabase as MB_API_KEY environment variable
    ],
    // The journal path that you wish to expose via views for Metabase to analyze.
    'journalPath' => getenv('JOURNAL_PATH'),
    'databases' => [
	// This is the multi-journal installation
	'multi' => array_merge($dbDefaults, [
	    'database' => 'scielo',
	    'username' => 'scielo',
	    'password' => 'scielo',
	]),
	// This is the virtual single-journal database that contains the views
	'single' => array_merge($dbDefaults, [
	    'database' => getenv('JOURNAL_PATH'),
	    'username' => getenv('JOURNAL_PATH'),
	    'password' => getenv('JOURNAL_PATH'),
	]),
	// This is the Metabase database
	'metabase' => array_merge($dbDefaults, [
	    'database' => 'metabase',
	    'username' => 'metabase',
	    'password' => 'metabase',
	]),
    ],
    // A list of foreign keys that should be configured in Metabase.
    // Syntax: 'foreign_table.foreign_column' => 'primary_table.primary_column'
    'foreignKeys' => [
	'author_settings.author_id' => 'authors.author_id',
	'authors.user_group_id' => 'user_groups.user_group_id',
	'authors.publication_id' => 'publications.publication_id',
	'edit_decisions.editor_id' => 'users.user_id',
	'edit_decisions.review_round_id' => 'review_rounds.review_round_id',
	'issues.journal_id' => 'journals.journal_id',
	'journal_settings.journal_id' => 'journals.journal_id',
	'journals.current_issue_id' => 'issues.issue_id',
	'publication_settings.publication_id' => 'publications.publication_id',
	'publications.primary_contact_id' => 'users.user_id',
	'publications.section_id' => 'sections.section_id',
	'publications.submission_id' => 'submissions.submission_id',
	'section_settings.section_id' => 'sections.section_id',
	'sections.journal_id' => 'journals.journal_id',
	'stage_assignments.submission_id' => 'submissions.submission_id',
	'stage_assignments.user_group_id' => 'user_groups.user_group_id',
	'stage_assignments.user_id' => 'users.user_id',
	'submission_settings.submission_id' => 'submissions.submission_id',
	'submissions.context_id' => 'journals.journal_id',
	'submissions.current_publication_id' => 'publications.publication_id',
	'user_group_settings.user_group_id' => 'user_groups.user_group_id',
	'user_groups.context_id' => 'journals.journal_id',
	'user_settings.user_id' => 'users.user_id',
	'user_user_groups.user_id' => 'users.user_id',
	'user_user_groups.user_group_id' => 'users.user_group_id',
    ],
    // A list of enumerations that should be configured with human-readable labels in Metabase.
    // Syntax: 'table_name.column_name' => [
    //     constant_value => 'Human readable label'
    // ]
    'enumerations' => [
	'user_groups.role_id' => [
	    16 => 'Manager',
	    17 => 'Section Editor',
	    4096 => 'Reviewer',
	    4097 => 'Assistant',
	    65536 => 'Author',
	    1048576 => 'Reader',
	    2097152 => 'Subscription Manager',
	],
	'user_groups.is_default' => [0 => 'false', 1 => 'true'],
	'user_groups.show_title' => [0 => 'false', 1 => 'true'],
	'user_groups.permit_self_registration' => [0 => 'false', 1 => 'true'],
	'user_groups.permit_metadata_edit' => [0 => 'false', 1 => 'true'],
	'submissions.status' => [
	    1 => 'Queued',
	    3 => 'Published',
	    4 => 'Declined',
	    5 => 'Scheduled',
	],
	'edit_decisions.decision' => [
	    1 => 'Internal Review',
	    2 => 'Accept',
	    3 => 'External Review',
	    4 => 'Pending Revisions',
	    5 => 'Resubmit',
	    6 => 'Decline',
	    7 => 'Send to Production',
	    8 => 'Initial Decline',
	    9 => 'Recommend Accept',
	    10 => 'Recommend Pending Revisions',
	    11 => 'Recommend Resubmit',
	    12 => 'Recommend Decline',
	    14 => 'New External Round',
	    15 => 'Revert Decline',
	    16 => 'Revert Initial Decline',
	    17 => 'Skip External Review',
	    19 => 'Accept Internal',
	    29 => 'Back From Production',
	    30 => 'Back From Copyediting',
	    31 => 'Cancel Review Round',
	],
	'edit_decisions.stage_id' => $submissionStageIds = [
	    0 => 'Published',
	    1 => 'Submission',
	    2 => 'Internal Review',
	    3 => 'External Review',
	    4 => 'Editing',
	    5 => 'Production',
	],
	'submissions.stage_id' => $submissionStageIds,
    ],
];


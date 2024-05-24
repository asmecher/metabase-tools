# metabase-tools
Configuration tools for Metabase when working with SciELO journals

To use:
1. Create an empty database. Use `create-views.sql` (adapted as necessary) to create a set of views in it.
2. Use the Metabase UI to add the new database.
3. Edit `config.php` to specify the databases you wish to use and run `configure-metabase.php` to update Metabase's configuration. (This can also be used to sync changes back to Metabase after e.g. an addition to `config.inc.php` or an inadvertent change in the Metabase configuration.)

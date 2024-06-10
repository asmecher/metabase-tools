# metabase-tools
Configuration tools for Metabase when working with SciELO journals

To use:
1. Configure `config.php` with details from your environment.
2. Use `generate-views.sql` (adapted as necessary) to create a set of views in it:
   1. Generate the SQL:
      ```
      JOURNAL_PATH=abc php generate-views.sql
      ```
   2. Review the output to make sure it's correct
   3. Run the SQL as e.g. root (because of required CREATE DATABASE / GRANT privileges):
      ```
      JOURNAL_PATH=abc php generate-views.sql | sudo mysql
      ```
3. Create the new database in Metabase:
   ```
   METABASE_API_KEY="abcdefg" JOURNAL_PATH=abc php create-metabase-database.php
   ```
4. Sync the database:
   ```
   METABASE_MB_API_KEY="xyz123" JOURNAL_PATH=abc php sync-database.php
   ```
   *Note that `METABASE_MB_API_KEY` is NOT the same thing as the API key you create in the web interface for Metabase. It needs to be provided to Metabase when it is started using the [`MB_API_KEY`](https://www.metabase.com/docs/latest/configuring-metabase/environment-variables#mb_api_key) environment variable. It can be anything you like.*
5. Set the visibility for all tables in the new database:
   ```
   JOURNAL_PATH=abc php set-table-visibility.php
   ```
6. Sync the database again as in step 4.
7. Run `configure-metabase.php` to update Metabase's configuration. (This can also be used to sync changes back to Metabase after e.g. an addition to `config.inc.php` or an inadvertent change in the Metabase configuration.)
   ```
   JOURNAL_PATH=abc php configure-metabase.php
   ```

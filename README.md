# metabase-tools
Configuration tools for Metabase when working with large journal collections without a common editorial team.

In a hosting environment that uses OJS installations with multiple journals, hosts might want to support Metabase as a way for editors to build and run reports or explore the workflow data. However, hosts need to ensure that data from other journals is not exposed to editors. This toolset uses views to segment the main OJS database into derived databases that Metabase can explore freely without exposing unwanted data.

To use:
1. Configure `config/config.php` with details from your environment.
2. Use `generate-views.php` to create a database with a set of views in it:
   1. Generate the SQL:
      ```sh
      JOURNAL_PATH=abc php generate-views.php
      ```
   2. Review the output to make sure it's correct
   3. Run the SQL as e.g. root (because of required CREATE DATABASE / GRANT privileges):
      ```sh
      JOURNAL_PATH=abc php generate-views.php | sudo mysql
      ```
3. Create the new database in Metabase:
   *You can get a Metabase API key by creating it in the Metabase web interface.*
   ```sh
   METABASE_API_KEY="abcdefg" JOURNAL_PATH=abc php create-metabase-database.php
   ```
4. Sync the database:
   *Note that `METABASE_MB_API_KEY` is NOT the same thing as the API key you create in the web interface for Metabase. It needs to be provided to Metabase when it is started using the [`MB_API_KEY`](https://www.metabase.com/docs/latest/configuring-metabase/environment-variables#mb_api_key) environment variable. It can be anything you like.*
   ```sh
   METABASE_MB_API_KEY="xyz123" JOURNAL_PATH=abc php sync-database.php
   ```
5. Set the visibility for all tables in the new database:
   ```sh
   JOURNAL_PATH=abc php set-table-visibility.php
   ```
6. Sync the database again as in step 4.
7. Run `configure-metabase.php` to update Metabase's configuration. (This can also be used to sync changes back to Metabase after e.g. an addition to `config.inc.php` or an inadvertent change in the Metabase configuration.)
   ```sh
   JOURNAL_PATH=abc php configure-metabase.php
   ```
8. Run `create-default-cards.php` to create a set of reports/queries for the new database.
   ```sh
   METABASE_API_KEY="abcdefg" JOURNAL_PATH=abc php create-default-cards.php
   ```

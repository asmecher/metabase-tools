# metabase-tools
Configuration tools for Metabase when working with SciELO journals

To use:
1. Configure `config.php` with details from your environment.
2. Use `generate-views.sql` (adapted as necessary) to create a set of views in it:
  1. Generate the SQL:
    ```
    php generate-views.sql
    ```
  2. Review the output to make sure it's correct
  3. Run the SQL as e.g. root (because of required CREATE DATABASE / GRANT privileges):
    ```
    php generate-views.sql | sudo mysql
    ```
3. Use the Metabase UI to add the new database.
4. Run `configure-metabase.php` to update Metabase's configuration. (This can also be used to sync changes back to Metabase after e.g. an addition to `config.inc.php` or an inadvertent change in the Metabase configuration.)
  ```
  php configure-metabase.php
  ```

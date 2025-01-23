# Metabase tools for OJS
Configuration tools for Metabase when working with large journal collections without a common editorial team.

In a hosting environment that uses OJS installations with multiple journals, hosts might want to support Metabase as a way for editors to build and run reports or explore the workflow data. However, hosts need to ensure that data from other journals is not exposed to editors. This toolset uses views to segment the main OJS database into derived databases that Metabase can explore freely without exposing unwanted data.

## Setting up a Metabase environment for a single journal
### Required API keys

You will require *two* API keys for the following steps:

- A Metabase API key created through the Metabase web interface (see `METABASE_API_KEY` below)
- A Metabase API key provided to Metabase through the command line when starting it up (see `METABASE_MB_API_KEY` below)

**These two API keys are not the same!**

### Running the steps

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
### Copying cards

A "card" is the collective Metabase term for questions, models, and visualizations. You'll probably want to create some default cards in the newly created database. You can copy these from somewhere else in the same Metabase installation.

1. Determine the ID of the card you want to copy. The quickest way to do this is inspecting the link to the card. This will contain both the ID and an indication of the title. For example, a card called "Submissions with status by month" might have a URL like `.../question/82-submissions-with-status-by-month`. The ID in this case is `82`.
2. Use the `copy-card` script to copy it to your new database:
   ```sh
   METABASE_API_KEY="abcdefg" JOURNAL_PATH=abc php copy-card.php 123
   ```

# Backend Coding Challenge

Welcome to the Backend challenge for Artnight 🎉

This task wants to test your PHP abilities, in order to do so, we give you space to refactor the code as much as you
want.
Ideally, the task should not take you more than 2-3 hours, but feel free to take it as long as you want.

## Context 📚

We have a small PHP script that read some customers from different sources (csv file and from network), and we want to
parse and insert them into some database.

The code is pretty legacy, it has multiple errors, and is very hard to update/extend it, we would like to completely
refactor the script, but the main logic have to be the same (read from multiple networks and write the content
somewhere).

main logic have to be the same (read from multiple networks and write the content
somewhere).

## Instructions 🔎

The project is completely framework-agnostic.
The code is in the `run.php` file, and the output is stored in a local SQLite database.

You can run the project with the following command:

```bash
php run.php
```

Some suggestions:

1. Consider readability and best practices
2. Consider test coverage
3. _[OPTIONAL]_ We want to replace the database driver (from SQLite to filesystem, MySQL, PostgreSQL...)
4. _[OPTIONAL]_ Add a different data source (database, filesystem...)

### Network problems 📡

In case you have problems fetching the content from the external network (`randomuser.me`) because the page is down,
or you have internet connection issues..., you can replace on `run.php` the L20 by the following one.

```php
$web_provider = json_decode(file_get_contents($getcurrentworkingDirectory . '/data/network.json'))->results;
```

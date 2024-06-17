## Setup <u>**_ABN SCALA FEEDS_**</u>

---

#### Setting config
1. Setup your local environment. (I am currently using [Laradock](https://laradock.io/) to set this up)
    * PHP 7.4
    * MySQL 5.7 or later
    * NGINX / Apache
2. `config.php` - Go to <u>application/config/config.php</u> and edit the value of `$config['base_url']` to `scalafeeds.test`
3. Edit your `/etc/hosts` file. Add `127.0.0.1 scalafeeds.test`
4. `database.php` - Go to <u>application/config/database.php</u> and change the values with the code below

    ```
    $db['default']['hostname'] = 'mysql';
    $db['default']['username'] = 'root';
    $db['default']['password'] = 'root';
    $db['default']['database'] = 'scala-feeds';
    ```

    **Make sure you add `scala-feeds` database to your mysql server**
5. You should be able to open `scalafeeds.test` in your browser, showing a welcome page

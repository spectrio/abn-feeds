## To set-up for MAC M chips locally

---
1. Install docker to your local machine and launch it. https://docs.docker.com/get-docker/
2. Add entry to your hosts file by `sudo vim /etc/hosts`
   * Add `127.0.0.1 scalafeeds.aggregator.test` then press `:wq!`
   * Run `git submodule add https://github.com/Laradock/laradock.git`
      * Folder structure should look like this
       ```
       * project
           * laradock
       ```
3. Go to  laradock folder `cd laradock` then configure `nginx/` and `.env`
   * In `nginx/sites/` folder, add a new file called `scala_feeds.conf` and add the following
       ```
       #server {
       #    listen 80;
       #    server_name laravel.com.co;
       #    return 301 https://laravel.com.co$request_uri;
       #}
       server {
           listen 80;
           listen [::]:80;
           # For https
           # listen 443 ssl;
           # listen [::]:443 ssl ipv6only=on;
           # ssl_certificate /etc/nginx/ssl/default.crt;
           # ssl_certificate_key /etc/nginx/ssl/default.key;
           server_name scalafeeds.aggregator.test;
           root /var/www/public;
           index index.php index.html index.htm;
           location / {
                try_files $uri $uri/ /index.php$is_args$args;
           }
           location ~ \.php$ {
               try_files $uri /index.php =404;
               fastcgi_pass php-upstream;
               fastcgi_index index.php;
               fastcgi_buffers 16 16k;
               fastcgi_buffer_size 32k;
               fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
               #fixes timeouts
               fastcgi_read_timeout 600;
               include fastcgi_params;
           }
           location ~ /\.ht {
               deny all;
           }
           location /.well-known/acme-challenge/ {
               root /var/www/letsencrypt/;
               log_not_found off;
           }
           error_log /var/log/nginx/laravel_error.log;
           access_log /var/log/nginx/laravel_access.log;
       }
       ```
   * cd back into laradock folder
   * Run `cp .env.example .env` to create .env file and change `PHP_VERSION=8.3` to `PHP_VERSION=7.4` \
     Change `MYSQL_VERSION=latest` to `MYSQL_VERSION=5.7` \
     if you're having trouble starting Mysql image. Backup this then `~/.laradock/mysql/` folder then delete.
     Set zsh shell values to true for shell syntax highlighting
       ```
       SHELL_OH_MY_ZSH=true
       SHELL_OH_MY_ZSH_AUTOSUGESTIONS=true
       SHELL_OH_MY_ZSH_ALIASES=true
       ```
   * Run `docker-compose up -d nginx mysql --build`
   * If you have issues building due to m1 chip, cd into laradock and edit the docker-compose.yml file in the area below, add: `platform: linux/x86_64`
     ```### MySQL ################################################
     mysql:```
   <br><br>

4. Import the `control` and `expand` database to your laradock local db
5. Once you have the db's imported to your laradock database, change the .env for database to have a hosrt of `mysql` instead of localhost. This is needed for laradock. For e.g:
   DB_USER = "root"
   DB_HOST = "mysql"
   DB_PASS  = 'root'
   DB_NAME = 'control'

6. To run the nginx and mysql service via docket, do the command `docker-compose up -d nginx mysql --build`
7. Now open [abn.gmportal.com](http://scalafeeds.aggregator.test) in your browser
---

## Docker commands
* `optools` to go into docker instance
* `docker-compose up -d nginx mysql` to start
* `docker-compose down` to start
* `docker-compose up -d nginx mysql --build` to re-build and start docker services

See [Laradock documentation](https://laradock.io/getting-started) for more reference.


NewRelic Installation
- You want to follow the installation Guide from when you log into newrelic.
- Add Data -> Guided Install -> Application Management (APM) -> PHP -> Follow remaining steps
- Copy the newrelic license and user api keys as they will be used in the app


Change PHP version On the Server
Please use to below command

`sudo update-alternatives --config php`

After run above command select the PHP version that you need to use.

Press to keep the current choice[*], or type selection number: For example 2

After switching below command used to restart the PHP and Nginx server.

`sudo service nginx restart`
sudo service php7.1-fpm or php7.2-fpm  restart
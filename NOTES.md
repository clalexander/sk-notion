# Notion API Documentation

The Notion API and Notion API 2 are Laravel applications running on an AWS Lightsail Bitnami LAMP server.

# Server

Domain: rhemasystem.tjc.org

Notion API: https://rhemasystem.tjc.org/

Notion API 2: https://rhemasystem.tjc.org:3002/

## Access

Access the server via SSH. Note you must have the server access key.

`ssh bitnami@rhemasystem.tjc.org -i [access-key.pem]`

# Migration Notes

## Files

1. Copy `www` directory from old server to `~/www` on new server
    - Notion API folder location: `~/www/notion`
    - Notion API 2 folder location: `~/www/notion2`
    - (Included other folders in `www`, stored but ignored)
1. Change `notion` folders ownership to Apache `daemon` user
    - `sudo chown -R daemon notion*`
    - `sudo chgrp -R daemon notion*`

## Databases

The database on the new server is a MariaDB server, which is backward compatible with the MySQL server on the previous server.

1. Perform a database dump for the `db_notion` and `db_notion2` databases on the old server
    
    `mysqldump -u [username] -p[password] [database_name] > [dump_file.sql]`
    
1. Copy the dump files to the new server
1. Restore the databases on the new server
    
    `mariadb -u [username] -p[password] [database_name] < [dump_file.sql]`
    
1. Create a new user and grant it privileges to its respective database
    
    https://phoenixnap.com/kb/how-to-create-mariadb-user-grant-privileges
    
1. Update `notion/.env` and `notion2/.env` `DB_USERNAME` and `DB_PASSWORD` values for each user respectively

References:

- https://docs.bitnami.com/aws/faq/get-started/find-credentials/#using-amazon-lightsail
- https://mariadb.com/kb/en/connecting-to-mariadb/

## Ports

In the AWS Lightsail interface, ensure TCP ports are open for 80, 443, and 3002 (custom).

## SSL Certificate Setup

Setup the SSL certificate for the server following this guide:

https://docs.aws.amazon.com/lightsail/latest/userguide/amazon-lightsail-using-lets-encrypt-certificates-with-lamp.html

## Apache Configuration

The Apache configuration files are at `~/stack/apache/conf`.

1. In `~/stack/apache/conf/bitnami/bitnami.conf`
    1. Disable the default HTTP configuration
        
        ```bash
        # <VirtualHost _default_:80>
        #   DocumentRoot "/opt/bitnami/apache/htdocs"
        #   <Directory "/opt/bitnami/apache/htdocs">
        #     Options Indexes FollowSymLinks
        #     AllowOverride All
        #     Require all granted
        #   </Directory>
        #
        #   # Error Documents
        #   ErrorDocument 503 /503.html
        # </VirtualHost>
        ```
        
    1. Add an SSL redirect configuration
        
        ```bash
        <VirtualHost *:80>
          RewriteEngine On
          RewriteCond %{HTTPS} !=on
          RewriteRule ^/(.*) https://%{SERVER_NAME}/$1 [R,L]
        </VirtualHost>
        ```
        
1. In `~/stack/apache/conf/bitnami/bitnami-ssl.conf`
    1. Disable the default HTTPS configuration
        
        ```bash
        # <VirtualHost _default_:443>
        #  DocumentRoot "/opt/bitnami/apache/htdocs"
        #  SSLEngine on
        #  SSLCertificateFile "/opt/bitnami/apache/conf/bitnami/certs/server.crt"
        #  SSLCertificateKeyFile "/opt/bitnami/apache/conf/bitnami/certs/server.key"
        #
        #   <Directory "/opt/bitnami/apache/htdocs">
        #     Options Indexes FollowSymLinks
        #     AllowOverride All
        #     Require all granted
        #   </Directory>
        #
        #   # Error Documents
        #   ErrorDocument 503 /503.html
        # </VirtualHost>
        ```
        
    1. Add HTTPS configuration for the `notion` application
        
        ```bash
        <VirtualHost *:443>
          ServerName tjc.org
          ServerAlias rhemasystem.tjc.org
          DocumentRoot "/home/bitnami/www/notion/public"
        
          RewriteEngine On
          RewriteCond %{HTTPS} !=on
          RewriteRule ^/(.*) https://%{SERVER_NAME}/$1 [R,L]
        
          SSLEngine on
          SSLCertificateFile "/opt/bitnami/apache/conf/bitnami/certs/server.crt"
          SSLCertificateKeyFile "/opt/bitnami/apache/conf/bitnami/certs/server.key"
        
          <Directory "/home/bitnami/www/notion/public">
                    Options FollowSymLinks
                    AllowOverride All
                    Require all granted
                    ReWriteEngine On
          </Directory>
        
          # Error Documents
          ErrorDocument 503 /503.html
        </VirtualHost>
        ```
        
    1. Add HTTPS configuration for the `notion2` application on port `3002`
        
        ```bash
        Listen 3002
        <VirtualHost *:3002>
          ServerName tjc.org
          ServerAlias rhemasystem.tjc.org
          DocumentRoot "/home/bitnami/www/notion2/public"
        
          RewriteEngine On
          RewriteCond %{HTTPS} !=on
          RewriteRule ^/(.*) https://%{SERVER_NAME}/$1 [R,L]
        
          SSLEngine on
          SSLCertificateFile "/opt/bitnami/apache/conf/bitnami/certs/server.crt"
          SSLCertificateKeyFile "/opt/bitnami/apache/conf/bitnami/certs/server.key"
        
          <Directory "/home/bitnami/www/notion2/public">
                    Options FollowSymLinks
                    AllowOverride All
                    Require all granted
                    ReWriteEngine On
          </Directory
        
          # Error Documents
          ErrorDocument 503 /503.html
        </VirtualHost>
        ```
        
1. Restart the server
    
    `sudo /opt/bitnami/ctlscript.sh restart`
    

## Scripts

Copied the `/usr/bin/script__cache_homepage_data.sh` from the old server to the new server.

## Cron

Copied the cron jobs and added a certificate renewal job.

1. Accessed the cron jobs
    
    `sudo crontab -e`
    
1. Added the `generate_json` `curl` job with the updated URL and port. (Runs every night at midnight.)
    
    ```bash
    0 0 * * * curl https://rhemasystem.tjc.org:3002/api/generate_json
    ```
    
1. Added the homepage caching script job. (Runs every half hour.)
    
    ```bash
    0,30 * * * * /usr/bin/script__cache_homepage_data.sh
    ```
    
1. Added the SSL certificate renewal job. (Runs at midnight on the 1st and 15th of every month.)
    
    ```bash
    0 0 1,15 * * sudo certbot renew --post-hook "sudo /opt/bitnami/ctlscript.sh restart apache"
    ```
    

# Troubleshooting

Logging locations:

- `~/stack/apache/logs`
- `~/www/notion/storage/logs`
- `~/www/notion2/storage/logs`

## Check `Referer` Header Matches CORS Policy

Check that your `Referer` header matches the CORS policy, enforce at:

- `./public/.htaccess`
- `./config/cors.php`

## Check Folder Permissions

Check that the application folder owner and group match the Apache user and the permissions are either `644` or `755`

## Check Apache Configuration

Verify the Apache configuration is correct in `~/stack/apache/conf/bitnami/bitnami.conf` and `~/stack/apache/conf/bitnami/bitnami-ssl.conf`.

- Verify the port numbers
- Be sure to use full paths for `DocumentRoot` and `Directory` values
- Be sure to restart the server
    
    `sudo /opt/bitnami/ctlscript.sh restart`
    

## Database Access

Ensure you have correctly configured the database credentials in the `.env` files for each app.

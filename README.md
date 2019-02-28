# GitLab Autopull
Setup for GitLab Webhook to provide automatic pull.
When a push event occurs trigger PHP script to refresh repo on the server.

> This guide is for Ubuntu 18.04 and assuming NGINX already installed.

## PHP Setup

Install PHP FastCGI worker:

```shell
sudo apt-get install php-fpm
```

Create PHP page:

```shell
sudo nano /var/www/html/pull.php
```

```php
<?php
$json = json_decode(file_get_contents('php://input'));
$event = $json->{'object_kind'};
$project = $json->{'project'}->{'name'};
if(strcmp($event, 'push') == 0) {
  shell_exec("cd /opt/projects/$project && git reset --hard HEAD && git pull");
}
?>
```

## NGINX Site

Add site for NGINX:

```ini
server {
      listen 27000;
      server_name <server_fqdn>;
      root /var/www/html;

      location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
      }
}
```

## Clone Repo

Clone repo to target directory. For this example: ```/opt/projects```<br/>
The php-fpm service will need permissions to the projet directory. Default user is the same as NGINX: ```www-data```<br/>
You can change the php-fpm user in: ```/etc/php/7.2/fpm/pool.d/www.conf```

```shell
sudo mkdir /opt/projects
cd /opt/projects
sudo git clone <repo>
sudo chown -R www-data:www-data /opt/projects
```

## GitLab Webhook
In the Admin Area go to Settings -> Outbound Requests.<br/>
Check the box for "Allow requests to the local network from hooks and services."

In your repository go to Settings -> Integrations.<br/>
Put the URL in for the RunDeck server and PHP listener port: ```https:\\<server_fqdn>:27000\pull.php```<br/>
Enter ```master``` for the branch under Push Events.<br/>
Click "Add Webhook"<br/>

Commit and push a change to the repo and you should see the filesystem update in ```/opt/projects/<repo>```

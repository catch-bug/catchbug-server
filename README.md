# rollbug-server
opensource server written in PHP7 alternative to [rollbar](https://rollbar.com)

## Configuring web server
For proper functionality you must enable mod rewrite and must setup rewrite on http server in server configuration file or in .htaccess. For production usage use configuration in server setup (virtual host).   
Rewrite rule `RewriteRule ^api/v1\.0/(.*)$ api/v1.0/index.php?path=$1 [END,QSA]` is **mandatory for API** (POSTing messages to server, etc...). All other rule you can omit if you switch off rewrite when installing / configuring rollBug server web interface. 

### Apache
#### Virtual host configuration file
`Options +SymLinksIfOwnerMatch` is mandatory for mod rewrite

~~~~apacheconfig
<Directory /dir/where/is/rollBugServer>
 
 ......
 
 Options +SymLinksIfOwnerMatch
 RewriteEngine on
 RewriteRule ^api/v1\.0/(.*)$ api/v1.0/index.php?path=$1 [END,QSA]
</Dirrectory> 
~~~~

#### .htaccess
File is included in installation package. You must enable .htaccess reading in server config adding directive **`AllowOverride all`**, for example in virtual host config file:
~~~~apacheconfig
<Directory /dir/where/is/rollBugServer>
  AllowOverride all
  .....
</Directory>  
~~~~

## Installation
















[![Analytics](http://counter.z-web.eu/piwik.php?idsite=17&amp;rec=1)](http://counter.z-web.eu/piwik.php?idsite=17&amp;rec=1)

Lightbox Plugin for Wordpress 0.7a
===

* [http://github.com/m3nt0r/lightbox-plugin-for-wordpress](http://github.com/m3nt0r/lightbox-plugin-for-wordpress)
* [http://www.m3nt0r.de/blog/lightbox-wordpress-plugin/](http://www.m3nt0r.de/blog/lightbox-wordpress-plugin/)

WHAT'S NEW:
------------
+ Now using Lightbox 2.0.4
+ It's smaller: Shrinked >180KB to just 46KB
+ Build and tested on Wordpress 2.6.1
+ Only using a single wp_options field, not 8 fields.. 
+ Fixed Settings Panel - everything is working now.
+ Updated Style and HTML to look good in new Wordpress Admin Panel


UPGRADE INFO:
------------
To clean up your old install i recommend to install this new version and use the uninstall button once. 
Then use the "accident" backlink to reinitialize the plugin installation. This will delete all old fields.


INSTALLATION:
-------------

	cd wordpress/wp-content/plugins/
	git clone git://github.com/m3nt0r/lightbox-plugin-for-wordpress.git 
	
Now go to your admin panel and activate it.


NON-GIT INSTALL:
--------------
Once you downloaded all files create a folder called "lightbox-plugin-for-wordpress" in your plugins folder.
Then place all files in that directory so it looks something like this:

	wp-content/plugins/
		+ wp-content/plugins/lightbox-plugin-for-wordpress/
			+ wp-content/plugins/lightbox-plugin-for-wordpress/css
			+ wp-content/plugins/lightbox-plugin-for-wordpress/images
			+ wp-content/plugins/lightbox-plugin-for-wordpress/js
			+ wp-content/plugins/lightbox-plugin-for-wordpress/plugin.php
			
Now go to your admin panel and activate it.

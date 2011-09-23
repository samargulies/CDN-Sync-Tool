=== CDN Sync Tool ===
Contributors: Fubra,Backie, ray.viljoen,Olliea95
Tags: CDN,content delivery network, sync, CDN sync, tool, Content, Upload, Files, Media, Optimization,cloudfront,cloud front,amazon s3,s3,cloudfiles,theme,MaxCDN,Origin Pull,Origin,Pull,files,speed,faster,accelerator,Page Load, zoom
Tested up to: 3.1
Stable tag: 1.12
Requires At Least: 3.0
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=99WUGVV4HY5ZE&lc=GB&item_name=CATN%20Plugins-CDN&item_number=catn-cdn&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted 

Syncs static files to a content delivery network (CDN) such as Amazon S3 / CloudFront,MaxCDN and CloudFiles. To be used with WP-Supercache.
 
== Description ==

Front end optimisation plugin to be used with WP-Supercache.

Uploads/syncs your static files to a Content Delivery Network (CDN) with push CDNs such as Amazon S3 / CloudFront and CloudFiles as well as Origin Pull CDNs such as MaxCDN / NetDNA. You can choose files from your media library, theme directory, WordPress's wp-include directory and plugin directories as well as new media library uploads.

Plugin runs images thought smushit.com to losslessy compress images, as well as GD compression of images.

There is also concatenation of all Javascript and CSS files in the header and footer to one file each to reduce HTTP requests. Also moves the javascript file to the footer so the browser doesn't hold up the page load doing it. Leverages Google's Closure Compiler to remove whitespace, do simple and advanced optimisations to reduce file size.

This plugin requires WP Super Cache to be installed. As it will handle the rewriting of the inclusion of static files to ensure all static files will load from your CDN.

Developed by <a href="http://www.catn.com">PHP Hosting Experts CatN</a>

== Frequently Asked Questions ==

= Why should I care about fast loading web page? =

Because a speed affects your SEO and your sales. People aren't patient creatures, they want stuff as fast as possible. Google has stated that a page loading time makes up part of their page rank. Amazon found that for every 100ms in page loading caused them 1% in sales. Google found when they increased size of results from 10 results to 30 results and increased their page load by 0.5 seconds their traffic dropped by 20%. 

= Why does uploading files take so long with this installed? =

The reason for the increased time when uploading files is caused by using smushit which can take a 1+ seconds per image, GD compression and uploading to your CDN also increase the time spent handling the file. Since uploading new media happens only once per image the increase in time cause in the admin backend is saved on the front end page load.  

= Do you pre compress css and Javascript files before uploading to S3? =

Yes, the plugin gzips javascript and css files and adds a gzip content-type header to the files before uploading to S3 as S3 doesn't add these values to plain text files by default.

= Why do you upload static plugin files? =

Because some plugins also have images and static files that need to be displayed on your site, we also want the plugin to work even if you decide not to use the concatenation functionality of the plugin.

= Why do you concatenation Javascript and CSS files when there are others plugins that do it? =

The problem with these other plugins is that they don't upload the files to a CDN once they've been created.

= Do you upload concatenated Javascript and CSS files every time? =

No the files are uploaded to the Content Delivery Network (CDN) only once and they are then cached. If the CSS/Javascript files content changes then there will be a new file created and uploaded to the CDN. Using a different filename to avoid CDN edge caching conflicts.

= Why do I need to have WP Super Cache installed? =

You need to have this installed as we use their url changer functionality and they will help improve your site's speed.

= Why is there a custom directory sync? Doesn't the plugin sync everything I need by default? =

Well various plugins store images and static files in different places than the place we look by default, due to the large amount of places static files could be stored it would be near impossible for the plugin to automatically detect and sync the files.

= What sort of speed improvements can I expect? =

The page load improvements of a Content Delivery Network (CDN) can vary however it has been seen that by implementing use of a CDN can improve the speed of the site's loading by more than 75%.

= Is there anything special I need to do to have my new uploads sync to my Amazon S3? =

No with the plugin enabled and the Content Delivery Network (CDN) assigned as Amazon S3 /Cloudfront the uploads will happen automatically as well as other optimisations such compression.

= Is there anything special I need to do to have my new uploads sync to my CloudFiles Container? =

No with the plugin enabled and the Content Delivery Network (CDN) assigned as CloudFiles the uploads will happen automatically as well as other optimisations such compression.

= How long can the sync'ing process take? =

The syncing processing time can vary depending on how maybe media files you have and if you are using SmushIt, for example if you have 100 or so files you can expect it to last a few minutes or so or for 1000+ files you can expect it to last 60+ mins.

= I already have some of the files in a folder synced will the plugin know to skip these? =

Yes, there is a database table which stores the results of a file transfer which means if a file has already been synced and you haven't asked it to force uploads then it will skip the uploading to your CDN.

= Why is the JavaScript link at the bottom of the page not HEAD? =

Because while it's in the head some browsers will stop the rendering of the page until it's received. Since JavaScript is generally not used in the layout of the page it's presence isn't mandatory for the page to look good.

= Can I force the plugin to reupload files? =

Yes you just select `Force upload` just before you hit the sync button. This will mean that all files it finds it will upload to your CDN no matter if the file has already been uploaded before. 

= Why doesn't the anti-hotlinking work on CloudFront =

This is because CloudFront isn't currently able to do referrer checks and allow depending the result. When Amazon add this ability it will be added to the plugin.

= Anti-hotlinking isn't working on CloudFiles =

This is because CloudFiles ACL Referrer doesn't work as expected. So CloudFiles at this time is unable to do anti-hotlinking at this time.

= What is the difference between Origin Pull and Amazon S3/CloudFront and CloudFiles =

The difference is that with Origin Pull the files are pulled from your web host when the file is first requested. Origin Pull providers like MaxCDN generally respect your .htaccess rules/HTTP headers, which mean it will send the same headers as your server sends. Meaning you can change headers more easily and quickly than you would at Amazon S3/CloudFront.

== Installation ==

1. Upload plugin contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go CDN Sync Tool and configure

== CHANGELOG ==

= 1.13 =

* [20/07/2011 - 15:38] Grammar and spelling fixes to the contact page
* [20/07/2011 - 15:39] Removed a useless occurrence of the word 'directory'

= 1.12 =

* [17/05/2011 - 12:34] Added mime_content_type function for when there isn't one
* [18/05/2011 - 08:34] Fixed time and date for caching purposes to HTTP specs. - Thanks hydn
* [18/05/2011 - 08:53] increased small cache control to match expires
* [18/05/2011 - 09:21] Fixed windows incapitablity.
* [26/05/2011 - 09:51] Removed pointless SQL table and insert queries.

= 1.11 =

* [09/05/2011 - 14:47] Fixed blank white screen on pages when WP Super Cache isn't installed
* [10/05/2011 - 10:16] Fixed invalid cdn details resulting in call to non existant object.
* [10/05/2011 - 10:16] Fixed rewriting of javascript and css files when cdn error occurs

= 1.10 =

* [15/03/2011 - 17:00] Added ability to uncentralized multisite media files
* [16/03/2011 - 11:26] Fixed WP Super Cache depency nag not disappearing.
* [08/04/2011 - 11:16] Added Rackspace CloudFiles UK account support

= 1.9 =

* [14/03/2011 - 14:18] Removed calls to supposedly private instance variable $wpdb->prefix.
* [14/03/2011 - 13:58] Added Cache-Control header to AWS uploads. Props Michel Peterson
* [02/03/2011 - 09:27] Added admin bar menu.
* [25/02/2011 - 08:03] Fixed debug messages being written to error log when WP_DEBUG != true

= 1.8 =
* [14/2/2011 - 15:00] Fixed Smushit and GD compression turnning boolean/null values instead of the fileArray.
* [14/2/2011 - 16:20] Added test case to check non images are being sync'd properly.
* [14/2/2011 - 16:39] Fixed CloudFront mime content type.

= 1.7 =

* [10/2/2011 - 9:38] Fixed provider adding extra ABSPATH to file locations
* [10/2/2011 - 9:45] Updated unit test

= 1.6 =

* Fixed Minify settings not appearing saved.
* Fixed file uploading issue

= 1.5 = 

* Added toogable compression level on GD compression
* Added ability to have image compression done on 

= 1.4 =

* Moved JavaScript from <head> to </head> for the head option.
* Fixed </link> tag remaining.
* Fixed </body> javascript not appearing.

= 1.2 = 
* Fixed SQL table not being updated.

= 1.1 =

* Fixed text for CloudFiles container hint.
* Fixed skipping on non skipped files
* Fixed not knowing that container name is invalid.
* Fixed always saying details aren't valid on AWS details check.
* Fixed CloudFiles SSL cert issue.

= 1.0 =
* Added ability to move JavaScript file location
* Added gif to smushit.
* Added MaxCDN/Origin Pull support.
* Fixed CSS image rewrite
* Fixed child theme file issues
* Fixed issue with the options javascript location form.
* Fixed creating empty JS files in Concatenion issue.

= 0.10 =

* Fixed bug that added .gz to files where they wasn't a compressed version
* Fixed yet another CSS rewriting issue.
* Added ability to wipe JavaScript and CSS file cache.
* Added ability to use FTP to sync files.
* Added ability to toogle GZIP Compression on AWS S3.

= 0.9 =

* Removed custom output buffering and started using wpsupercache_buffer filter.
* Fixed upgrade error
* Fixed reseting theme files sync'd
* Fixed CSS rewritting error.
* Fixed api detail validation JavaScript error.

= 0.8 = 

* Fixed create bucket
* Fixed filesize error on incorrect file path for media library items.
* Fixed cdn hostname not showing up in form after saving even tho it was saved.
* Added debug log ability.

= 0.7 =
* Fixed CSS for errors
* Fixed Anti-Hotlinking call being made when not all the valid information has been given.
* Fixed constantly saying everything has been synced before.
* Fixed Scroll wheel fail that caused S3 syncing not to work. (DOH!)
* Removed debug info from when Rackspace Cloud's anti-hotlinking attempts is done.
* Removed WP Super Cache check before saving to ossdl_off_cdn_url.
* Removed W3 Total Cache as a dependency.
* Added option to sync JavaScript and CSS builds.
* Added abiltity to remove warning messages.

= 0.6 =
* Added cst_upgrade function which was called in the activation function
* Changed CDN hostname in options to solely work with "ossdl_off_cdn_url" option
* Fixed detail validation javascript/ajax

= 0.5 =
* Added IF NOT EXISTS to table creation

= 0.4 =

* Added Rackspace Cloudfiles support.
* Fixed get_mu_plugins() not defined error.
* Added anti-hotlinking functionality.

= 0.3 = 

* Allow usage of deprecated mime_content_type when Fileinfo isn't present.
* Added network activated and must use plugins into list of activated.
* Fixed typos/incorrect docs

= 0.2 =

* Fixed typos

== Upgrade Notice ==

= 0.6 =

* Activation fix

= 0.2 =

* Small non important fixes

== Screenshots ==

1. Options Page
2. Files syncing
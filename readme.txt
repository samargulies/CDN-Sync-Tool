=== CDN Sync Tool ===
Contributors: Fubra
Tags: CDN,content delivery network, sync, CDN sync, tool, Content, Upload, Files, Media, Optimization,cloudfront,cloud front,amazon s3,s3,cloudfiles,theme files,speed,faster,accelerator,Page Load,
Tested up to: 3.1-RC2
Stable tag: 0.3
Requires At Least: 3.0

A tool to sync static files to a content delivery network (CDN) such as Amazon S3/CloudFront. Designed to be used with WP-Supercache or W3TotalCache.

== Description ==

Front end optimization plugin to be used with WP-Supercache or W3TotalCache.

Uploads/syncs your static files to a Content Deilvery Network (CDN) such as Amazon S3/CloudFront from your media library, theme directory, WordPress's wp-include directory and plugin directories aswell as new media library uploads.

Plugin runs images thought smushit.com to losslessy compress images, aswell as GD compression of images.

There is also concatenation of all Javascript and CSS files in the header and footer to one file each to reduce HTTP requests. Also moves the javascript file to the footer so the browser doesn't hold up the page load doing it. Leverages Google's Closure Compiler to remove whitespace, do simple and advanced optimizations to reduce file size.

This plugin requires WP Super Cache or W3 Total Cache to be installed. These plugins will handle the rewriting of the inclusion of static files to ensure all static files will load from your CDN.

=BETA RELEASE=

Developed by <a href="http://www.catn.com">PHP Hosting Experts CatN</a>

== Frequently Asked Questions ==

= Why should I care about fast loading web page? =

Because a speed affects your SEO and your sales. People aren't paitence creatures, they want stuff as fast as possible. Google has stated that a page loading time makes up part of their page rank. Amazon found that for every 100ms in page loading caused them 1% in sales. Google found when they increased size of results from 10 results to 30 results and increased their page load by 0.5 seconds their traffic dropped by 20%. 

= Why does uploading files take so long with this installed? =

The reason for the increased time when uploading files is caused by using smushit which can take a 1+ seconds per image, GD compression and uploading to your CDN also increase the time spent handling the file. Since uploading new media happens only once per image the increase in time cause in the admin backend is saved on the front end pagson fe load.  

= Do you pre compress css and Javascript files before uploading to S3? =

Yes, the plugin gzips javascript and css files and adds a gzip content-type header to the files before uploading to S3 as S3 doesn't add these values to plain text files by default.

= Why do you upload static plugin files? =

Because some plugins also have images and static files that need to be displayed on your site, we also want the plugin to work even if you decide not to use the concatenation functionality of the plugin.

= Why do you concatenation Javascript and CSS files when there are others plugins that do it? =

The problem with these other plugins is that they don't upload the files to a CDN once they've been created.

= Do you upload concatenated Javascript and CSS files everytime? =

No the files are uploaded to the Content Deilvery Network (CDN) only once and they are then cached. If the CSS/Javascript files content changes then there will be a new file created and uploaded to the CDN. Using a different filename to avoid CDN edge caching conflicts.

= Why do I need to have WP Super Cache or W3 Total Cache installed? =

You need to have one of these installed as we use their url changer functionality and they will help imrpove your site's speed.

= Why is there a custom directory sync? Doesn't the plugin sync everything I need by default? =

Well various plugins store images and static files in different places than the place we look by default, due to the large amount of places static files could be stored it would be near impossible for the plugin to automatically detect and sync the files.

= What sort of speed improvements can I expect? =

The page load improvements of a Content Deilvery Network (CDN) can vary however it has been seen that by implementing use of a CDN can improve the speed of the site's loading by more than 75%.

= Is there anything special I need to do to have my new uploads sync to my Amazon S3? =

No with the plugin enabled and the Content Deilvery Network (CDN) assigned as Amazon S3/Cloudfront the uploads will happen automatically aswell as other optimizations such compression.

= How long can the sync'ing process take? =

The syncing processing time can vary depending on how mabye media files you have and if you are using SmushIt, for example if you have 100 or so files you can expect it to last a few minutes or so or for 1000+ files you can expect it to last 60+ mins.

= I already have some of the files in a folder synced will the plugin know to skip these? =

Yes, there is a database table which stores the results of a file transfer which means if a file has already been synced and you haven't asked it to force uploads then it will skip the uploading to your CDN.

= Why is the JavaScript link at the bottom of the page not HEAD? =

Because while it's in the head some browsers will stop the rendering of the page until it's recevied. Since JavaScript is generally not used in the layout of the page it's prescene isn't mandatory for the page to look good.

= Can I force the plugin to reupload files? =

Yes you just select `Force upload` just before you hit the sync button. This will mean that all files it finds it will upload to your CDN no matter if the file has already been uploaded before. 

== Installation ==

1. Upload plugin contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go CDN Sync Tool and configure

== CHANGELOG ==

= 0.4 =

* Added Rackspace Cloudfiles support
* Fixed get_mu_plugins() not defined error.

= 0.3 = 

* Allow usage of deprecated mime_content_type when Fileinfo isn't present.
* Added network activated and must use plugins into list of activated.
* Fixed typos/incorrect docs

= 0.2 =

* Fixed typos

== Upgrade Notice ==

= 0.2 =

* Small non important fixes

== Screenshots ==

1. Options Page
2. Files syncing
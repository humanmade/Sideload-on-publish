=== Sideload Images on publish ===
Contributors: mattheu, humanmade
Tags: productivity, workflow, p2
Requires at least: 3.1
Tested up to: 3.6
Stable tag: 0.1

Automatically sideload external images when publishing posts/comments to ensure things don't break in the future when those images are no longer available.

== Description ==

Searches the content for `<img>` elements and images inserted in markdown format.

Only loads images from whitelisted domains. This can be easily filtered using the `hm_sideload_images` filter.

Current Whitelist
* 'https://dl.dropboxusercontent.com', // DropBox
* 'http://cl.ly/image/463Y120M3O1R',   // CloudApp
* 'http://www.evernote.com',           // Evernote / Skitch
* 'https://www.evernote.com',          // Evernote / Skitch
* 'https://skydrive.live.com',         // Skydrive
* 'http://sdrv.ms',                    // Skydrive

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.  
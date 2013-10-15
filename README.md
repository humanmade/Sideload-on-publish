Sideload Images on Publish
==========================

WordPress plugin for automatically sideloading external images when publishing posts/comments to ensure things don't break in the future when those images are no longer available.

Searches the content for `<img>` elements and images inserted in markdown format.

Only loads images from whitelisted domains. This can be easily filtered using the `hm_sideload_images` filter.

## Current Whitelist
    'https://dl.dropboxusercontent.com', // DropBox
    'http://cl.ly/image/463Y120M3O1R',   // CloudApp
    'http://www.evernote.com',           // Evernote / Skitch
    'https://www.evernote.com',          // Evernote / Skitch
    'https://skydrive.live.com',         // Skydrive
    'http://sdrv.ms',                    // Skydrive

## WP-CLI Commands

To sideload images on existing posts & comments, you can use the provided WP CLI commands.

[Find out more about WP-CLI](http://wp-cli.org/)

** Sideload all images on all posts **
sideload-images all-in-posts [--post__in=<comma separated post IDs>] [--post_type=<comma separated post types>] [--post_statuses=<comma separated post status>]


## Contribution guidelines ##

see https://github.com/humanmade/Sideload-on-publish/blob/master/contributing.md



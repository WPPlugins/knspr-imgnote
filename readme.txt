=== knspr-imgnote ===
Contributors: knuspermagier
Donate link: http://knuspermagier.de/spenden
Tags: flickr, ajax, images, notes, media
Requires at least: 2.5.0
Tested up to: 3.0
Stable tag: 1.0-rc8

This plugin let's you place notes on images like you know it from flickr.

== Description ==

This plugin let's you place notes on images like you know it from flickr. Via a additional box in the admin's post edit view you're able to place notes on images associated to the post.

Please take note that it's currently no final release, so there might be bugs.

If you have any problem please consult the [support forums](http://support.knuspercode.de/index.php/categories/2/knspr-imgnote)

Uses the [imgAreaSelect jQuery plugin](http://odyniec.net/projects/imgareaselect/) by Michal Wojciechowski and is based on the [imgnotes jQuery plugin](http://www.sanisoft.com/blog/2008/05/26/img-notes-jquery-plugin/) by Dr. Tarique Sani.

== Installation ==

1. Upload it to `/wp-content/plugins/`
2. Activate it
3. Look at the post edit page, down under there should be a box called "Edit image notes"

== Workflow ==

1. Add a new post or edit an existing one
2. Upload the images via the Wordpress media upload tools and insert it in the post
3. If the post is new, safe it as draft and reload the page, the images should be visible in the thumbnail list
4. If the post is an existing one just hit the "Reload" link in the thumbnail list and the new images should be visible
5. Click the thumbnail of the image you want to add notes to and click the "add note"-Button.
6. Add all your notes, they will be automatically saved as you click "Save note".

== Frequently asked questions ==

= How to change the look of the notes? =

You can either edit the css-file in `(plugin)/themes/default` or create an own theme by copying the default styles to a new subfolder of `(plugin)/themes`. If you do the least you need to adjust the `$noteTheme` variable in the `knspr-imgnote.php` file.

= How to disable the 'Notes: 2' display? =
Open `knspr-imgnote.php` and set the `$noteShowNoteCount` variable to `false`.

= Help! Everything is messed up with the notes =

It's crucial to insert the images in their full size into the posts and not as thumbnail.

= How to change the RSS feed message? =

If you want to replace the default message that shows up in the rss feed if the post contains image notes you need to edit the following line in `knspr-imgnote.php` to suit your needs:

`define('KNSPR_FEED_MESSAGE', '<p>Images in this post contain notes, please open the website directly</p>');`

Just edit the part in the second pair of ticks.

== Changelog ==

= 1.0-rc8 = 
* Fixed paths for reload-icon and spinner...

= 1.0-rc7 = 
* Fixed a bug introduced with rc5.

= 1.0-rc6 = 
* Made the message, that shows up when you have insufficient rights more... polite
* Using get_posts instead of get_children to retrieve attachments => no more unrelated images. Hopefully
* Better path retrieving.

= 1.0-rc5 =
* It now works with scaled images.
* Fixed bug with WP < 2.9

= 1.0-rc4a =
* Fixed a bug with umlauts

= 1.0-rc4 = 
* Fixed a but that killed the theme editor
* Made the admin box movable
* Changed linked notes to a normal anchor element, so we can open it in new tabs
* Placed the RSS message directly beneath the image
* You now can disable the number-of-notes display

= 1.0-rc3 =
* Fixed a bug when using " or ' in note texts
* Fixed a very stupid bug.

= 1.0-rc1 =
* First release

== Upgrade Notice ==

= 1.0-rc8 =
Tiny changes

= 1.0-rc7 =
Fixed a nasty bug introduced in RC5

= 1.0-rc6 =
Better path retrieving, not showing unrelated images, more polite. Hopefully.



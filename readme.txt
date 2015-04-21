=== Tumblr Widget ===
Contributors: gabrielroth
Tags: Tumblr, widget
Requires at least: 2.8
Tested up to: 4.2
Stable tag: trunk

Allows you to import a Tumblr into any widgetized area of a WordPress blog.

== Description ==

Tumblr Widget allows you to display the contents of a Tumblr in any widget-enabled area of your WordPress blog. You can import all Tumblr posts, or only those posts with a specified tag, or specify certain categories (photo, link, quotation, etc.) to display.

If you find this plugin useful, I'd love to check out your site. Send me an email and let me know where you're using it! gabe.roth@gmail.com



**Controls**

+ *Title:* The title you want to appear above the Tumblr on your WordPress page. Leave blank if you like.

+ *Your Tumblr:* The URL of the Tumblr you want to import. It doesn't have to contain 'tumblr.com'. Leave off the 'http://' at the beginning.

+ *Tag to show:* Enter a single tag to display only posts with that tag. Leave blank to show all Tumblr posts.

+ *Tag to hide:* Enter a single tag to hide all posts with that tag.

+ *Maximum number of posts to display:* This number is a *maximum,* as the text suggests.

+ *Link title to Tumblr:* Turns the widget title into a link to your Tumblr's URL. If you don't enter a title in the title field, you won't get a link.

+ *Link to each post on Tumblr:* When checked, this displays the date of the Tumblr post, linking the date to the original post on the Tumblr site.

+ *Images link to Tumblr post:* By default, images in photo posts link to a large image file. When this box is checked, they link to the Tumblr post instead.

+ *Add inline CSS padding:* Adds a CSS style rule adding 8 pixels of padding above and below each Tumblr post. Disable to prevent it messing up your own CSS.

+ *Set video width:* Resizes videos to help them fit in your theme. Enter a value in pixels. 50px is the minimum. Height will be adjusted automatically in proportion to the width you choose.

+ *Show:* Include or exclude different post types in the feed.

+ *Photo size:* Tumblr provides each photo in six different sizes. Whichever size you choose to display, the image links to the 1,280-pixel version.

== Installation ==

1. Upload `tumblr-widget.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Widgets' page in the 'Appearance' menu in WordPress.

== Frequently Asked Questions ==

= How can I change the way the Tumblr posts display? =

Use CSS. You can add targeted style rules to your `style.css` file, in your theme folder. Each post is a <code><li></code> with the class "tumblr_post." Each post also has the class of its post type: "quote", "link", "conversation", "regular", "photo", "audio", or "video".

= Can I filter by more than one tag =

Tumblr’s API only supports searching on a single tag, unfortunately.

= Can I display photos at a different size other than the six in the dropdown menu? =

Choose the nearest size that's bigger than what you want, then adjust the photo’s width and height properties using CSS.

= Can I import and display someone else's Tumblr? =

Yes, you can. But you shouldn't, unless you have their permission.

= I have another question that's not covered here. =

Email me: gabe.roth at gmail.


== Changelog ==

= 2.1 =
* Added hide-by-tag feature.
* Added support for high-resolution 1,280-px photos.
* Tidied up control panel.

= 2.0.1 =
* Fixed bug that was throwing "undefined index" warnings on first run.

= 2.0 =
* Tumblr Widget now supports multiple instances, allowing you to import more than one Tumblr to your WordPress site.
* Added option to link images to the Tumblr post rather than the image file.
* Smarter cache handling.

= 1.5 =
* Added filter-by-tag feature.
* Fixed bug that was throwing warning on requests that returned no posts. 
* Removed an unnecessary `<br />` tag when laying out image posts.

= 1.4.8 =
* Fixed bug that was causing SimpleXML errors.
* Fixed bug that was throwing warning for using mktime() with no argument.

= 1.4.7 =
* Tumblr's API started inserting some extra whitespace before the XML, making it invalid; this revision trims that whitespace before processing.

= 1.4.6 =
* Fixed bug with video resizing code.

= 1.4.5 =
* Fixed bug that caused a dot to appear in link posts where it didn't belong.

= 1.4.4 =
* Minor bug fixes.

= 1.4.3 =
* Better error checking.
* Various minor fixes.

= 1.4.2 =
* Now flushing cache when changing Tumblr URL.
* Added 'Hide error messages' option

= 1.4.1 =
* Fixed reappearing bug that prevented some themes from finishing loading on Tumblr failure.

= 1.4 =
* Added caching, which should help when Tumblr's servers are being flaky.
* We now use WP_Http instead of cURL, as recommended.

= 1.3 =
* Added two features: 'Resize videos' and 'Link title to Tumblr'. 

= 1.2 =
* Fixed bug that was preventing settings from being preserved with WP 2.9.

= 1.1 =
* Added 'link to Tumblr' feature

= 1.0 =
* First release.
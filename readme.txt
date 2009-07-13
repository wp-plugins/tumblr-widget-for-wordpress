=== Tumblr Widget ===
Contributors: gabrielroth
Tags: Tumblr, widget
Requires at least: 2.8
Tested up to: 2.8.1
Stable tag: trunk

Allows you to import a Tumblr into any widgetized area of a WordPress blog.

== Description ==

Tumblr Widget allows you to display the contents of a Tumblr in any widget-enabled area of your WordPress blog. You can import all Tumblr posts or specify certain categories (photo, link, quotation, etc.) to display.

**Controls**

+ *Title:* The title you want to appear above the Tumblr on your WordPress page. Leave blank if you like.

+ *Your Tumblr:* The URL of the Tumblr you want to import. It doesn't have to contain 'tumblr.com'. Leave off the 'http://' at the beginning.

+ *Maximum number of posts to display:* This number is a *maximum,* as the text suggests.

+ *Link to each post on Tumblr* When checked, this displays the date of the Tumblr post, linking the date to the original post on the Tumblr site.

+ *Add inline CSS padding* Adds a CSS style rule adding 8 pixels of padding above and below each Tumblr post. Disable to prevent it messing up your own CSS.

+ *Show:* Include or exclude different post types in the feed.

+ *Photo size:* Tumblr provides each photo in five different sizes. Whichever size you choose to display, the image links to the 500 pixel version.

== Installation ==

1. Upload `tumblr-widget.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Widgets' page in the 'Appearance' menu in WordPress.

== Frequently Asked Questions ==

= How can I change the way the Tumblr posts display? =

Use CSS. You can add targeted style rules to your `style.css` file, in your theme folder. Each post is a <code><li></code> with the class "tumblr_post." Each post also has the class of its post type: "quote", "link", "conversation", "regular", "photo", "audio", or "video".

= Can I import and display someone else's Tumblr? =

Yes, you can. But you shouldn't, unless you have their permission.

= Can I display my Tumblr if it's set to private? =

Not right now. I'm hoping to get to that soon.

= I have another question that's not covered here. =

Email me via the website listed at the top of this form.


== Changelog ==

= 1.0 =
* First release.

== To do ==

* Authenticate and import private Tumblrs

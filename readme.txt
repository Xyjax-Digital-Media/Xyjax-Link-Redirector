=== Xyjax Link Redirector ===
Contributors: xyjax
Tags: redirects, links, url, affiliate, outbound
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.2.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Opensource lightweight redirect manager with basic click tracking.

== Description ==

Xyjax Link Redirector is a lightweight redirect manager for WordPress.  
It allows you to create clean, human-readable redirect URLs and send visitors to internal or external destinations using standard HTTP redirects.

The plugin focuses on simplicity, transparency, and reliability.  
It does not rely on external services, JavaScript redirects, or tracking scripts.

== Features ==

* Create redirect links using a simple admin interface
* Customizable link prefix (default: `/go/slug`)
* Supports 301, 302, and 307 redirect types
* Optional enable/disable per link
* Basic click counter per redirect
* CSV export of redirect data
* No external dependencies or SaaS services

== How It Works ==

Redirects are managed under **Link Redirects** in the WordPress admin.

Example:

* Short URL: `https://example.com/go/docs`
* Destination: `https://github.com/example/project`

Visitors accessing the short URL are redirected using standard HTTP headers.

== Privacy & Data Collection ==

This plugin records a simple click count for each redirect.

It does NOT:
* store IP addresses
* store user agents
* set cookies
* track personal data
* load third-party scripts

== What This Plugin Does NOT Do ==

Xyjax Link Redirector intentionally avoids:

* Affiliate link injection
* Keyword auto-linking
* JavaScript-based redirects
* URL cloaking tricks
* Subscription or licensing systems
* External analytics platforms

== Compatibility ==

* Works with Apache and Nginx
* Compatible with standard WordPress permalink structures
* Does not interfere with WordPress admin, REST API, or file requests

== Uninstalling ==

Deactivating the plugin disables redirects immediately.

Uninstalling the plugin does not delete redirect definitions from the database unless explicitly removed by the user.

== Frequently Asked Questions ==

= Can I change the link prefix? =
Yes. The link prefix can be changed under **Link Redirects → Settings**.

= Does this work for affiliate links? =
Yes. The plugin uses server-side redirects and does not rely on JavaScript.

= What happens if two links use the same slug? =
WordPress prevents duplicate slugs for redirect entries.

= Does this plugin work without JavaScript? =
Yes. All redirects are handled server-side.

== Changelog ==

= 0.2.2 =
* Stable prefix-based redirect model
* Customizable link prefix
* Improved admin UI and safety checks
* Updated plugin metadata

= 0.2.0 =
* Initial internal release

== Upgrade Notice ==

= 0.2.2 =
This release stabilizes redirect behavior and prefix configuration.

== Screenshots ==

1. Redirect edit screen showing destination and redirect type
2. Redirect list with click counts
3. Settings screen for configuring the link prefix

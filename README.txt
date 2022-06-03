=== Plugin Name ===
Contributors: mcarter76
Donate link: https://www.kahoycrafts.com/wordpress-plugin-donation/
Tags: products feed generator, woocommerce product feed, xml data feed, google shopping, google shopping feed, structured data, woocommerce, marketing channel, e-commerce, handmade, small shop
Requires at least: 5.6
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generates an XML Products Feed for Google Merchant Center in RSS 2.0 format.

== Description ==

This plugin generates a Data Feed for Google Shopping using an existing catalog of WooCommerce products.

Products Feed Generator for WooCommerce provides all of the necessary features to create a Google Shopping feed and streamlines the process of mapping designated google variant-related fields to custom product attributes. We hope you'll find the interface to be simple, clean, and uncluttered.

Perfect for a small or handmade shop that wants to get up and running quickly. Products Feed Generator takes on the challenge of maintaining a shopping feed with a minimal amount of configuration.

This plugin allows a user to do the following:

- Configure a Google Shopping XML feed and automatically save it to the uploads directory
- Configure a Cron task to generate a feed Daily, Twice Daily, Hour, or Weekly.
- Configure a Feed to include: Parent Products only, Parent Products + Variations, or Variations only.
- Map WooCommerce global product attributes to designated google shopping fields
- Map WooCommerce shipping classes to Google shipping labels
- Add GTIN, MPN, and more...
- Add custom google thumbnail image for parent products
- Optionally add product attributes to a product details section
- Optionally write debug and info messages to WC status log

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `products-feed-generator.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the settings page: WooCommerce > Settings > Products > Products Feed Generator
1. Configure the plugin and any desired options

== Frequently Asked Questions ==

= How many products can be added to a feed =

This version of the plugin is limited to 100 unique products or variations. Handling additional products would likely require batch processing or special resource considerations, subjects that are currently beyond the scope of this project.

= What feed formats are supported =

Only XML is supported at this time, although additional formats are planned for a future release.

= Does this plugin support other marketplaces =

Only Google Shopping is supported at this time, although additional marketplaces are planned for a future release.

== Screenshots ==

1. Main Settings in WooCommerce (Part 1).
2. Main Settings in WooCommerce (Part 2).
3. Product Settings.

== Changelog ==

= 1.0.5 - 2022-03-13 =
* Fix bug with save handlers and duplicate cron tasks.

= 1.0.4 - 2022-03-07 =
* Add support for WooCommerce catalog visibility, hidden products are no longer included in feed.

= 1.0.3 - 2022-02-28 =
* Only show published products in feed

= 1.0.2 - 2022-02-28 =
* Fix bug with default material

= 1.0.1 - 2022-02-28 =
* Renamed elements for title, link and description.
* Added support for default material in parent product

= 1.0.0 - 2022-02-21 =
* First release!

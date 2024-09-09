=== Address Autocomplete Anything ===
Contributors: wpsunshine, sccr410
Tags: address, autocomplete, form, woocommerce, gravityforms, checkout, google
Requires at least: 5.0
Tested up to: 6.6.1
Requires PHP: 7.4
Stable tag: 1.2
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html

Easily integrate Google Address Autocomplete to anything on your WordPress website!

== Description ==

This plugin is unique in that it allows you to add a Google Address Autocomplete to _anything_ on your WordPress website. It is not made to be specific for any one e-commerce, form, LMS, or other WordPress plugin... is compatible with them all!

Address Autocomplete is my favorite feature on any e-commerce site or any time I need to fill out a form on a website. Originally built for our other plugin, [Sunshine Photo Cart](https://wordpress.org/plugins/sunshine-photo-cart/), I realized I could make this available to work for _anything_.

### How it works

By using CSS selectors (don't worry non-tech person, it is easier than you think and a [simple help article and video is available](https://wpsunshine.com/documentation/finding-your-css-selectors/?utm_source=wordpress.org&utm_medium=link&utm_campaign=address-autocomplete-readme)!), you can add Address Autocomplete to Anything! Provide a selector for which input field on your page you want to trigger the address autocomplete when a user types, and then the CSS selectors to target for the address data.

### What you need

You only need to [get a Google Maps API key](https://wpsunshine.com/documentation/google-maps-api-key/?utm_source=wordpress.org&utm_medium=link&utm_campaign=address-autocomplete-readme). Although billing info is required, _most_ sites will never be charged as the free limit is quite high.

[Visit the documentation](https://wpsunshine.com/doccat/address-autocomplete/?utm_source=wordpress.org&utm_medium=link&utm_campaign=address-autocomplete-readme)

### Upgrade to Premium

* Get unlimited instances on your site
* More detailed data fields (latitude, longitude, county, neighborhood, sub localities, etc) to use for population
* Automatically integrate with popular e-commerce and form plugins with one-click set up:
** WooCommerce (Shortcode and Block Checkout, My Address in Account)
** Gravity Forms (Address Field)
** LifterLMS
** Paid Memberships Pro
** ...and more coming _very_ soon!

[Get Premium here](https://wpsunshine.com/plugins/address-autocomplete/?utm_source=wordpress.org&utm_medium=link&utm_campaign=address-autocomplete-readme)

== Installation ==

1. Upload and activate the plugin
2. Get a Google Maps API key
3. Go to Settings > Address Autocomplete and to enter Google Maps API key and form settings
4. Get the CSS selectors for your form and put into the settings

== Screenshots ==

1. Plugin settings
2. Sample form showing how address autocompletes works

== Changelog ==

= 1.2 =
* Set multiple allowed pages for each instance

= 1.1.6 =
* More console logging to help debug
* NL added to list of countries to do reverse street address format

= 1.1.5 =
* Fix - Load Google maps with async

= 1.1.4 =
* Fix - Handle address1 when there is no street number

= 1.1.3 =
* Update - Allows more than just addresses, will now accept establishment names
* Fix - Spaces causing issues in before/after attributes

= 1.1.2 =
* Fix - stripslashes on CSS selectors to handle quotes when saving settings

= 1.1.1 =
* Fix - Enqueue Google Maps requires callback function

= 1.1 =
* Update - Better input replacement method and allow for "before" and "after" attributes
* Add - Minified version of frontend.js for even smaller footprint

= 1.0.2 =
* Fix - Handle special case UK city/county things

= 1.0.1 =
* Add - "wps_aa_load_scripts" filter to allow disabling loading of JS files as requested by user for GDPR compliance

= 1.0 =
* Change - Complete redo of the way selectors and data fields are chosen for population for even more flexibility

= 0.3 =
* Update documentation links throughout and link to review
* Release to repo for the first time

= 0.2 =
* Fix - More esc_* and sanitization

= 0.1 =
* Initial submission to repo

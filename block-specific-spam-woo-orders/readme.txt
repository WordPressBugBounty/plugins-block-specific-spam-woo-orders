=== Block Specific Spam Woo Orders ===
Contributors: wigster
Tags: woocommerce, woo, block, spam, orders
Requires at least: 5.1
Tested up to: 6.6.1
Requires PHP: 5.4
Stable tag: 0.77
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to automatically block spam Woo orders that began in October 2020.

== Description ==

This plugin prevents a specific set of WooCommerce fake/spam orders.
Simply install and activate the plugin; there are no settings or tweaks to be made unless you want to add your own filters.
The plugin extends WooCommerce's built-in checkout validations to check for a specific set of known spam email accounts and names. If triggered, the spam bot simply cannot checkout and importantly does not get to the account creation stage.
The names/emails it checks for would only be used by spam bots, so there is no need to worry about false positives.

If you've found this plugin useful, you can support my work by buying me a coffee at:
[Buy Me a Coffee](https://buymeacoffee.com/alexwigmore).

== How to Use Custom Filters ==

Starting from plugin version 0.77, you can extend the list of blocked email domains and blocked customer names using custom filters.

### Available Filters:

1. **BSSO_extra_domains:** Add custom email domains to block during the checkout process.
2. **BSSO_extra_names:** Add custom first names to block during the checkout process.

### Example Usage

To use these filters, add code to your theme's `functions.php` file or a custom plugin.

#### 1. Blocking Additional Email Domains

If you want to block additional email domains like `exampledomain.com` and `spamdomain.net`, use the `BSSO_extra_domains` filter.

**Code Example:**

<pre><code>
add_filter('BSSO_extra_domains', function () {
    return ['exampledomain.com', 'spamdomain.net'];
});
</code></pre>

#### 2. Blocking Additional First Names

If you want to block additional first names like `spambot` and `faker`, use the `BSSO_extra_names` filter.

**Code Example:**

<pre><code>
add_filter('BSSO_extra_names', function () {
    return ['spambot', 'faker'];
});
</code></pre>

### Complete Example

Here’s how you might use both filters together:

**Code Example:**

<pre><code>
add_filter('BSSO_extra_domains', function () {
    return ['exampledomain.com', 'spamdomain.net'];
});

add_filter('BSSO_extra_names', function () {
    return ['spambot', 'faker'];
});
</code></pre>

### Version Compatibility

Please note that these filters are only available starting from version 0.77 of the plugin. Ensure your plugin is updated to at least this version to use the custom filters.

== Frequently Asked Questions ==

= Will you keep this plugin updated? =

Yes, where possible, I will try my best to add additional checks if the attack vectors change.

== Changelog ==

= 0.77 =
* Added filters for extending blocked email domains and names.
* Tested compatibility with WooCommerce 9.2.3.

= 0.76 =
* Tested compatibility with WP 6.6.1 and WC 9+.
* Added confirmation that this plugin is compliant with the new WooCommerce HPOS (High-Performance Order Storage) / Custom Order Tables (COT) systems.

= 0.75 =
* Tested compatibility with WP 6.5.2 and WC.

= 0.7 =
* Updated logic slightly to simplify checks. Names are now also array-based if people want to manually extend.
* Added ability to translate/localize the Spam Validation message with typical language translators (WPML, etc.).
* Confirmed support with WP 6.4 and the latest WooCommerce.

= 0.6 =
* Added a new function to handle checking against multiple blocked domains, now including ["@fakemail"].
* Confirmed support with WP 6.1 and the latest WooCommerce.

= 0.55 =
* Tested support with WP 6.0 and the latest WC - works fine.

= 0.54 =
* Updated supported versions for WP and WooCommerce.

= 0.53 =
* Added support for readme.txt changelogs.

= 0.52 =
* Updated support for WooCommerce - no code changes, minor updates to comment wording.

= 0.51 =
* Initial release.

<?php

/*
* Plugin Name: Block Specific Spam Woo Orders
* Plugin URI: https://wordpress.org/plugins/block-specific-spam-woo-orders/
* Description: A quick plugin to block on-going issues with spam WooCommerce orders November 2020
* Author: guwii
* Version: 0.80
* Requires at least: 5.1
* Requires PHP: 5.4
* Requires Plugins: woocommerce
* Author URI: https://guwii.com
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: guwii-woo-block-spam-orders
* WC requires at least: 4.3
* WC tested up to: 10.8.1
*/
if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

$bssorders_active_plugins = (array) get_option('active_plugins', []);
$bssorders_network_active_plugins = (array) get_site_option('active_sitewide_plugins', []);

// Only use this plugin if WooCommerce is active
if (
  in_array('woocommerce/woocommerce.php', $bssorders_active_plugins, true)
  || isset($bssorders_network_active_plugins['woocommerce/woocommerce.php'])
) {

  // Add our custom checks to the built-in WooCommerce checkout validation:
  add_action('woocommerce_after_checkout_validation', 'action_woocommerce_validate_spam_checkout', 10, 2);

  // Normalize domains supplied by the default block list or custom filters.
  function bssorders_normalize_domain($domain)
  {
    if (! is_scalar($domain)) {
      return '';
    }

    $domain = strtolower(trim(sanitize_text_field((string) $domain)));

    return ltrim($domain, '@');
  }

  function bssorders_is_blocked_email_domain($billing_email, $blocked_email_domain)
  {
    $blocked_email_domain = bssorders_normalize_domain($blocked_email_domain);

    if ('' === $blocked_email_domain || ! preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $blocked_email_domain)) {
      return false;
    }

    $email_parts = explode('@', strtolower((string) $billing_email));
    $email_domain = count($email_parts) > 1 ? array_pop($email_parts) : '';

    if ('' === $email_domain) {
      return false;
    }

    $subdomain_suffix = '.' . $blocked_email_domain;

    return $email_domain === $blocked_email_domain || substr($email_domain, -strlen($subdomain_suffix)) === $subdomain_suffix;
  }

  // Check over a list of provided domains, to see if $user_email matches any of them:
  function bssorders_is_a_spam_order($fields)
  {
    // Setup the fields we're checking for spam:
    $billing_email = isset($fields['billing_email']) && is_scalar($fields['billing_email']) ? sanitize_email((string) $fields['billing_email']) : '';
    $billing_first_name = isset($fields['billing_first_name']) && is_scalar($fields['billing_first_name']) ? sanitize_text_field((string) $fields['billing_first_name']) : '';

    // Provide the list of email domains we want to block:
    $blocked_email_domains = ['abbuzz.com', 'fakemail.com'];
    // Provide the list of first names we want to block:
    $blocked_names = ['aaaaa', 'bbbbb'];

    // Apply the filters to allow adding extra emails/domains and names
    $extra_domains = apply_filters('BSSO_extra_domains', []);
    $extra_names = apply_filters('BSSO_extra_names', []);

    // Sanitize and validate the extra domains and names
    $extra_domains = is_array($extra_domains) ? $extra_domains : [];
    $extra_domains = array_filter(array_map('bssorders_normalize_domain', $extra_domains), function ($domain) {
      return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain);
    });

    $extra_names = is_array($extra_names) ? $extra_names : [];
    $extra_names = array_filter(array_map(function ($name) {
      return is_scalar($name) ? sanitize_text_field((string) $name) : '';
    }, $extra_names));

    // Merge the default and extra arrays
    $blocked_email_domains = array_merge($blocked_email_domains, $extra_domains);
    $blocked_names = array_merge($blocked_names, $extra_names);

    // Set the default return of false:
    $is_a_spam_order = false;

    // Compare user's email domain with our list of blocked email domains:
    foreach ($blocked_email_domains as $blocked_email_domain) {
      if (bssorders_is_blocked_email_domain($billing_email, $blocked_email_domain)) {
        $is_a_spam_order = true;
        break;
      }
    }

    // If not spam by email domain, check the names
    if (!$is_a_spam_order) {
      $billing_first_name_lower = strtolower($billing_first_name);

      foreach ($blocked_names as $blocked_name) {
        if (strpos($billing_first_name_lower, strtolower($blocked_name)) !== false) {
          $is_a_spam_order = true;
          break;
        }
      }
    }

    return $is_a_spam_order;
  }

  // Run the customer's name & billing email through our func, report "Spam" if true:
  function action_woocommerce_validate_spam_checkout($fields, $errors)
  {
    if (bssorders_is_a_spam_order($fields)) {
      $errors->add('validation', __('Spam.', 'guwii-woo-block-spam-orders'));
    }
  }

  // Optional: Indicate HPOS compatibility
  add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
      \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
  });
}

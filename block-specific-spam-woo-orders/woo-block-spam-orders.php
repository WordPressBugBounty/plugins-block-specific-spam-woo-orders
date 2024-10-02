<?php

/*
* Plugin Name: Block Specific Spam Woo Orders
* Plugin URI:
* Description: A quick plugin to block on-going issues with spam WooCommerce orders November 2020
* Author: guwii
* Version: 0.77
* Author URI: https://guwii.com
* License: GPL3+
* Text Domain: guwii-woo-block-spam-orders
* WC requires at least: 4.3
* WC tested up to: 9.2.3
*/

// Only use this plugin if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {

  // Add our custom checks to the built-in WooCommerce checkout validation:
  add_action('woocommerce_after_checkout_validation', 'action_woocommerce_validate_spam_checkout', 10, 2);

  // Check over a list of provided domains, to see if $user_email matches any of them:
  function bssorders_is_a_spam_order($fields)
  {
    // Setup the fields we're checking for spam:
    $billing_email = sanitize_email($fields['billing_email']);
    $billing_first_name = sanitize_text_field($fields['billing_first_name']);

    // Provide the list of email domains we want to block:
    $blocked_email_domains = ['abbuzz.com', 'fakemail.com'];
    // Provide the list of first names we want to block:
    $blocked_names = ['aaaaa', 'bbbbb'];

    // Apply the filters to allow adding extra emails/domains and names
    $extra_domains = apply_filters('BSSO_extra_domains', []);
    $extra_names = apply_filters('BSSO_extra_names', []);

    // Sanitize and validate the extra domains and names
    $extra_domains = array_filter($extra_domains, function ($domain) {
      return preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain);
    });
    $extra_names = array_map('sanitize_text_field', $extra_names);

    // Merge the default and extra arrays
    $blocked_email_domains = array_merge($blocked_email_domains, $extra_domains);
    $blocked_names = array_merge($blocked_names, $extra_names);

    // Set the default return of false:
    $is_a_spam_order = false;

    // Compare user's email domain with our list of blocked email domains:
    foreach ($blocked_email_domains as $blocked_email_domain) {
      if (strpos($billing_email, $blocked_email_domain) !== false) {
        $is_a_spam_order = true;
        break;
      }
    }

    // If not spam by email domain, check the names
    if (!$is_a_spam_order) {
      foreach ($blocked_names as $blocked_name) {
        if (strpos($billing_first_name, $blocked_name) !== false) {
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

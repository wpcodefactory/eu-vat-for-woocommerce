=== EU VAT for WooCommerce ===
Contributors: omardabbas
Tags: woocommerce, eu, vat, eu vat, woo commerce
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 1.8.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage EU VAT in WooCommerce. Beautifully.

== Description ==

**EU VAT for WooCommerce** plugin lets you collect and validate EU VAT numbers on WooCommerce checkout. It can automatically disable (exempt) VAT for valid numbers. Also there is a tool to add all EU country VAT standard rates to WooCommerce.

= Main Features =

* Set **frontend options**: field label, placeholder, description, position, CSS class etc.
* Set if EU VAT field **is required** on checkout.
* Set if EU VAT field needs to be **validated**.
* Automatically **exempt VAT** for valid VAT numbers.
* Optionally check for **matching billing country code**.
* Optionally allow VAT number **input without country code**.
* Customize **progress messages**.
* Set **display options** (after order table and/or in billing address).
* Optionally always **show zero VAT**.
* **WPML/Polylang** compatible.
* Automatically exempt/not exempt VAT for selected **user roles**.
* EU VAT **report**.

= Premium Version =

[EU VAT for WooCommerce Pro](https://wpfactory.com/item/eu-vat-for-woocommerce/) allows to:

* **Preserve VAT** in selected countries.
* Check **country by IP**.
* **Show field** for selected **countries** only.
* **Show field** for selected **user roles** only.
* Validate **company name**.

= Feedback =

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/eu-vat-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > EU VAT".

== Changelog ==

= 1.8.0 - 23/12/2019 =
* Dev - Plugin author updated.

= 1.7.2 - 12/12/2019 =
* Dev - General - Frontend Options - "Max length" option added.
* Dev - Validation - "Skip VAT validation for selected countries" option moved from to "Advanced" section.
* Dev - Code refactoring.

= 1.7.1 - 05/12/2019 =
* Fix - Validation - Check for matching billing country code - Fixed for Greece (`EL` is replaced with `GR` when comparing country codes).
* Dev - Admin & Advanced - "Force VAT recheck on checkout" option added.
* Dev - Debug - "Error: VAT is not valid" message added to the log.
* Dev - Code refactoring.
* Tested up to: 5.3.

= 1.7.0 - 08/11/2019 =
* Dev - Validation - "Always exempt VAT for selected user roles" and "Always not exempt VAT for selected user roles" options added.
* Dev - Admin & Advanced - Debug - "Country code does not match" message added to the log.
* Dev - Admin & Advanced - Session type - "WC session" option marked as "recommended".
* Dev - Code refactoring.
* WC tested up to: 3.8.

= 1.6.1 - 16/10/2019 =
* Dev - Validation - Check company name - Now converting all values to uppercase before comparing.
* Dev - JavaScript - Better event for company validation.

= 1.6.0 - 15/10/2019 =
* Dev - General - Frontend Options - "Show field for selected user roles only" option added.
* Dev - Validation - "Check company name" option added.
* Dev - Admin & Advanced - Advanced Options - "Debug" option added.
* Dev - Code refactoring.

= 1.5.0 - 13/08/2019 =
* Dev - Admin - Order List - "EU VAT" column added.
* Dev - Admin - Reports - Taxes - "EU VAT" report added.
* Dev - Admin - EU country VAT Rates Tool - Duplicates are no longer added for the country.
* Dev - Admin settings split into sections.
* Dev - Allow VAT number input without country code - Additional country fallback added.
* Dev - Functions - General - `alg_wc_eu_vat_session_start()` - Additional `headers_sent()` check added.
* WC tested up to: 3.7.
* Tested up to: 5.2.

= 1.4.1 - 04/05/2019 =
* Fix - Preserve VAT in selected countries - Bug (when "Allow VAT number input without country code" is enabled) fixed.
* Fix - Show field for selected countries only - Bug (when "Required" is enabled) fixed.
* Dev - Frontend Options - "Confirmation notice" options added.
* Dev - Code refactoring.
* Dev - "WC tested up to" updated.

= 1.4.0 - 06/03/2019 =
* Fix - "Preserve VAT in selected countries" fixed when "Allow VAT number input without country code" is enabled.
* Dev - Frontend Options - "Always show zero VAT" option added.
* Dev - `[alg_wc_eu_vat_translate]` shortcode added.
* Dev - Shortcodes are now also processed in field label, placeholder, description and validation message options.
* Dev - Validation - Preserve VAT in selected countries - "Comma separated list" option added.
* Dev - Frontend Options - "Show field for selected countries only" option added.

= 1.3.0 - 31/01/2019 =
* Fix - Default field value on the checkout fixed.
* Dev - Display Options - Display - Multiple positions are now allowed (i.e. multiselect).
* Dev - Display Options - Display - In billing address - Field is now editable ("My Account > Addresses").
* Dev - Frontend Options - "Label CSS class" option added.
* Dev - Code refactoring.

= 1.2.1 - 30/01/2019 =
* Dev - Advanced Options - "Session type" option added.
* Dev - Admin settings - "Your settings have been reset" notice added.

= 1.2.0 - 12/11/2018 =
* Fix - AJAX - Possible "undefined index" PHP notice fixed.
* Dev - General - "Priority (i.e. position)" option added.
* Dev - General - "Raw" input is now allowed in textarea admin settings.
* Dev - Code refactoring.
* Dev - Plugin URI updated.

= 1.1.0 - 07/06/2018 =
* Dev - General - "Check for matching billing country code" option added.
* Dev - General - "Allow VAT number input without country code" option added.

= 1.0.1 - 05/06/2018 =
* Dev - `%eu_vat_number%` replaced value added to "Message on not valid" option. "Message on not valid" now doesn't check for required (i.e. empty) field.

= 1.0.0 - 24/05/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.

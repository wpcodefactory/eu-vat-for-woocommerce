=== EU/UK VAT Validation Manager for WooCommerce ===
Contributors: wpcodefactory, omardabbas, karzin, anbinder, algoritmika, kousikmukherjeeli, aegkr
Tags: EU VAT, UK VAT, tax, vat validation, VAT
Requires at least: 6.1
Tested up to: 6.7
Stable tag: 4.3.7
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage EU/ UK VAT in WooCommerce, validate VAT numbers real time with VIES, exempt or preserve VAT with various settings & cases.

== Description ==

> “Great Support: The plugin does exactly what it says and it have worked really well so far. I had a small issue but the developer released a new update the day after which fixed it. Can highly recommend!” – ⭐⭐⭐⭐⭐  [soccing](https://wordpress.org/support/topic/great-support-2527/)

[Main Page](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Main Page**") | [Support Forum](https://wpfactory.com/support/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Support Forum**") | [Documentation & How to](https://wpfactory.com/docs/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Documentation & How to**") | [Demo](https://euvatvalidation.instawp.xyz/)

---

**Unlock Seamless B2B Transactions with the UK/EU VAT Manager**

In the rapidly evolving eCommerce landscape, the ability to cater to both B2C and B2B customers has become essential for growth and scalability.

For store owners selling across the UK and EU, navigating the complexities of Value Added Tax (VAT) can be challenging.

B2B transactions within this region often need validation of VAT numbers to ensure legitimate business purchases, allowing sellers to exempt these buyers from VAT charges, where they report it to their local tax authorities.

With our **EU/UK VAT Validation Manager for WooCommerce** plugin, you can effortlessly validate UK/EU VAT numbers using [VIES (VAT Information Exchange System) services](https://ec.europa.eu/taxation_customs/vies/#/vat-validation "VIES (VAT Information Exchange System) services"), enabling you to sell to businesses in the EU without the additional tax burden.

This not only streamlines your checkout process but ensures compliance, boosting trust and confidence among your B2B clientele.

In a nutshell, here is what this plugin does:

* **Collect & Validate VAT numbers:** Adds EU VAT field to checkout page to collect VAT numbers and validate in real time

* **Collect VAT on Signup:** Add the EU VAT field to your WooCommerce signup form, entries will be automatically saved in customer data fields

* **Preserve (keep) or Exempt VAT:** Based on tax laws, select to remove VAT for valid VAT numbers or keep it, per country

* **Custom Progress Messages:** Customize & show messages during validation so customers are informed what's happening behind while communicating with VIES

* **Set Field Requirement:** Go beyond optional & required, the plugin allows you to make the field customization based on different cases (more details below)

* **Checkout Block-based Compatible:** The plugin works seamlessly with the new block-based Checkout page

* **Add VAT Number to PDF Invoices:** Compatibility with the renowned [PDF Invoice & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/ "PDF Invoice & Packing Slips") plugin, or any other plugin manually using field_id `_billing_eu_vat_number`

**Important Note: Throughout this description, we use "EU VAT or VAT" to refer to both EU and UK VAT for simplicity and flow. All options and rules specified for the EU also apply to the UK.**

___
## 🤝 Recommended By##

* [CrocoBlock: 3 Best WooCommerce Tax Plugins to Make Managing Tax Quicker and Easier](https://crocoblock.com/blog/best-woocommerce-tax-plugins/ "CrocoBlock: 3 Best WooCommerce Tax Plugins to Make Managing Tax Quicker and Easier")

* [Common Ninja: Handle EU VAT on WooCommerce](https://www.commoninja.com/discover/wordpress/plugin/eu-vat-for-woocommerce "Common Ninja: Handle EU VAT on WooCommerce")

* [Better Studio: 6 Best WooCommerce Tax Exempt Plugins 🥇](https://betterstudio.com/wordpress-plugins/best-woocommerce-tax-exempt-plugins/ "Better Studio: 6 Best WooCommerce Tax Exempt Plugins 🥇")

* [WPLift: 10 Best WooCommerce Tax Plugins](https://wplift.com/best-woocommerce-tax-plugins/ "WPLift: 10 Best WooCommerce Tax Plugins")

* [LearnWoo: 9 Best WooCommerce Tax Exempt Plugins](https://learnwoo.com/woocommerce-tax-exempt-plugins/ "LearnWoo: 9 Best WooCommerce Tax Exempt Plugins")

___

## 🚀 Main Features: FREE Version##

### 🚀 Collect & Validate EU & UK VAT Numbers ###

* Add VAT field to checkout and/or signup forms to allow customers to enter their VAT numbers

* In real time, check VAT numbers on VIES services to verify if they are valid

* Validate VAT numbers using SOAP web service, with fallback methods like cURL & Simple

### 🚀 Decide to Deduct or Keep VAT For Valid Numbers ###

Once results return if VAT number is valid, you can select what VAT charges to apply:

* Remove VAT completely (generally when selling for businesses outside store base country)

* Preserve VAT for store base country

* Preserve VAT in selected countries of your choice

* Preserve VAT if shipping country is different from billing country

### 🚀 Checkout Block-based Compatible ###

With the recent updates to WooCommerce block-based checkout page, our plugin is now compatible with it, to ensure a seamless integration without the need to use classic editor or workarounds.

### 🚀 Control Field Visibility & Appearance ###

* Customize field name to reflect the common name for VAT for your audience (like USt, TVA, IVA)

* Customize field placeholder (text appearing inside the field)

* Add description to the field so customers are informed on what to do

* Control field position in checkout page in accordance to other fields

* Customize & label field class for CSS

* Compatible with checkout pages built using page builders by manually adding EU VAT through field_id `_billing_eu_vat_number`

### 🚀 Set Field To Multiple Required/Optional Cases ###

* Select to make the field always required (if only selling B2B for example)

* Make field completely optional for all customers

* Make field required in selected countries only (and optional in the rest)

* Likewise, make field optional in some countries (and required in the rest)

* Select the field to be required ONLY if customer filled "Company" field

* Warn users when field is set to optional by showing customized notification text message

* For Belgium TVA Compatibility: Allow customer to select if they are individual or business, making the option required or optional based on selection

### 🚀 More Options to Validate/Invalidate VAT by Countries & User Roles ###

* Always exempt VAT for selected user roles only

* Always charge VAT for selected user roles only

* Skip VAT validation for selected countries

* Invalidate VAT checking and reserve VAT if country code in VAT number isn't matching billing country code

### 🚀 Keep Customers Informed with Progress Messages ###

* Enhance checkout experience by showing custom messages related to VAT validation

* Select to show messages on different cases:

1. Validating: While communicating with VIES services
2. Valid: When results return **Valid**
3. Invalid: When results return **Invalid**
4. Validation failed: When communicating with VIES failed for technical reasons
5. When customer select different billing & shipping countries (if that option is enabled)
6. When customer uses a wrong company name (if that option is enabled "Pro option")

* Control all messages with CSS: All messages have their own CSS classes to customize them along with your brand colors & guidelines

### 🚀 EU VAT Management for Admin ###

* Add an EU VAT number summary meta box to admin order edit page

* Add an EU VAT number column to admin orders list

* Debug & logging options to monitor all validation events

### 🚀 VAT Reporting ###

With this feature, you can view a detailed breakdown of sales by each EU country, clearly highlighting total tax amount and transactions where zero tax was applied due to valid VAT number validation, offering clarity on your B2B transactions, ensuring transparency and aiding in compliance.

### 🚀 More Advanced Options ###

* Import all standard EU Tax rates with a 1-click importer using our tool, accessible on WordPress Tools >> EU Country VAT Rates

* If your customers are used to provide their VAT numbers without preceding country code, you can tolerate this and treat both numbers the same with a seamless experience for your customers.

* **PDF Invoice & Packing Slips** compatibility:  VAT number will be inserted to PDF invoices generated by [PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/ "PDF Invoices & Packing Slips") for WooCommerce plugin

* For other invoicing plugins, you can also add VAT number to invoice by field_id `_billing_eu_vat_number`

* Multi-Language Support: Plugin is WPML & Polylang compatible, you can use shortcodes to show different languages messages

* Seamless validation & update Checkout page in real time using AJAX (without refreshing the page)

___
> “Best plugin for EU VAT: I was using another plugin that is not supported anymore. Then I found EU/UK VAT Manager for WooCommerce, and really, I’m very happy the other plugin is not supported anymore, not because it was not a good plugin, on the contrary, it was. But in another case probably I didn’t find this amazing plugin, that is even a lot better. EU/UK VAT Manager for WooCommerce is the best plugin to manage the EU VAT. I love the VAT check that is made via Ajax in the background when you are in the checkout. Really kudos for this amazing plugin!” – ⭐⭐⭐⭐⭐ [Jose](https://wordpress.org/support/topic/best-plugin-for-eu-vat/)

> “Perfect PLUGIN & Service: I was looking for a plugin for a european based b2b shop which will deduct the VAT from other eu countries. Found this plugin, installed it and it worked perfectly! The VAT was deducted on point and i was very happy. But then i noticed that orders from the same country will also deduct the VAT which I thought was not possible. I contacted Omar and he replied that this new feature will be released soon. Today he messaged me to update the plugin and i did! And guess what, now it works exactly as i needed it! Thanks a lot for this plugin and service!” – ⭐⭐⭐⭐⭐ [khang1985](https://wordpress.org/support/topic/perfect-plugin-service-2/)
___

## 🏆 Do More: PRO Version ##

All the features mentioned above, and many more, are available in the free version. But if you're looking to take things up a notch, consider our enhanced [EU/UK VAT Validation Manager for WooCommerce Pro](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme) plugin.

Upgrade to the Pro version and unlock additional more features, including:

### 🏆 Show Specific Payment Method Only for Valid VAT  ###

If you're selling to businesses and wish to offer them specific payment gateways (such as cheques), you can hide that payment option unless a valid VAT number is provided at checkout.

### 🏆 Show/Hide EU VAT Field by Country & User Role  ###

* Choose the countries where the VAT field should be displayed (Use case example: Hide it for sales within the store's base country, as VAT will always be charged to these customers)

* Show field for selected user roles only

### 🏆 More Advanced Pro Options ###

* **Country Match:** Check if customer's country (located by customer's IP) matches the country used in VAT number

* **Company Name Matching:** Check if company name matches the VAT number, and show custom message if not

* **Local VAT numbers Handling:** Allow checkout in countries with local VAT numbers which can't be validated (not part of VIES)

* Premium Support

And more...

___
## 💯 Why WPFactory?##

* **Experience You Can Trust:** Over a decade in the business
* **Wide Plugin Selection:** Offering 65+ unique and powerful plugins
* **Highly-Rated Support:** Backed by hundreds of 5-star reviews
* **Expert Team:** Dedicated developers and technical support at your service

___

## What's Next? Discover More Plugins by WPFactory ##

WPFactory has a diverse range of plugins tailored to enhance your experience, some of our top-selling plugins are:

* [**Min Max Step Quantity**](https://wpfactory.com/item/product-quantity-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Min Max Step Quantity**"): Set minimum, maximum, step, and default product quantities, including dropdowns and decimal options on WooCommerce (**[Free version](https://wordpress.org/plugins/product-quantity-for-woocommerce/ "Free version")**)

* [**Cost of Goods for WooCommerce**](https://wpfactory.com/item/cost-of-goods-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Cost of Goods WooCommerce**"): Enhance profit maximization and financial management by accurately calculating your WooCommerce store's COGS (**[Free version](https://wordpress.org/plugins/cost-of-goods-for-woocommerce/ "Free version")**)

* [**Maximum Products per User**](https://wpfactory.com/item/maximum-products-per-user-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Maximum Products per User**"): Impose personalized purchase limits based on user roles and date ranges to manage customer buying behavior (**[Free version](https://wordpress.org/plugins/maximum-products-per-user-for-woocommerce/ "Free version")**)

* [**Order Minimum/Maximum Amount**](https://wpfactory.com/item/order-minimum-maximum-amount-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Order Minimum/Maximum Amount**"): Customize order limits by amount, quantity, weight, or volume, including user role, category, and more (**[Free version](https://wordpress.org/plugins/order-minimum-amount-for-woocommerce/ "Free version")**)

* [**EU/UK VAT Validation Manager for WooCommerce**](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**EU/UK VAT Validation Manager for WooCommerce**"): Automate VAT compliance for your WooCommerce store, including settings and VIES validation for a seamless experience (**[Free version](https://wordpress.org/plugins/eu-vat-for-woocommerce/ "Free version")**)

* [**Email Verification for WooCommerce**](https://wpfactory.com/item/email-verification-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Email Verification for WooCommerce**"): Boost security and credibility by verifying customer emails, reducing spam, and improving email marketing (**[Free version](https://wordpress.org/plugins/maximum-products-per-user-for-woocommerce/ "Free version")**)

* [**Free Shipping Over Amount for WooCommerce**](https://wpfactory.com/item/amount-left-free-shipping-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Free Shipping Over Amount for WooCommerce**"): Encourage higher spending by offering free shipping based on amount, with a progress bar for customers (**[Free version](https://wordpress.org/plugins/amount-left-free-shipping-woocommerce/ "Free version")**)

* [**Dynamic Pricing & Bulk Quantity Discounts**](https://wpfactory.com/item/product-price-by-quantity-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme "**Dynamic Pricing & Bulk Quantity Discounts**"): Advanced dynamic pricing and discount rules for WooCommerce, encouraging bulk purchases and driving more sales (**[Free version](https://wordpress.org/plugins/wholesale-pricing-woocommerce/ "Free version")**)

## ❤️ User Testimonials: See What Others Are Saying!##

> “Great plugin. We had a small problem and after contact we received the solution very fast. It works 100% now, thanks!” – ⭐⭐⭐⭐⭐ [niek rijt](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme)

> “The plugin works great, and does everything I need for selling to other countries in Europe! Support is great and they help develop new features to make the plugin even more compliant with the laws.” – ⭐⭐⭐⭐⭐ [Vincent Bus](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme)

> “It is great! And support is very helpful even with free version. 5 stars!” – ⭐⭐⭐⭐⭐ [Vera](https://wpfactory.com/item/eu-vat-for-woocommerce/?utm_source=wporg&utm_medium=organic&utm_campaign=readme)

> “Great support!: The plugin works great, and does everything I need for selling to other countries in Europe! Support is great and they help develop new features to make the plugin even more compliant with the laws.” – ⭐⭐⭐⭐⭐ [kingwebshops](https://wordpress.org/support/topic/great-support-4410/)

== Installation ==

**Follow these simplified steps to get your plugin up and running:**

**From the WordPress Admin Panel:**
1. Navigate to “Plugins” > “Add New”.
2. Use the search bar and find the plugin using the exact name.
3. Click “Install Now” for the desired plugin.
4. Once the installation is finished, click “Activate”.

**Manual Installation Using FTP:**
1. Download the desired plugin from WordPress.org.
2. Using your preferred FTP client, upload the entire plugin folder to the /wp-content/plugins/ directory of your WordPress installation.
3. Go to “Plugins” > “Installed Plugins” in your dashboard and click “Activate”.

**Manual download & upload from the WordPress Admin Panel:**
1. Download the desired plugin in a ZIP format.
2. On your site, navigate to “Plugins” > “Add New” and click the “Upload Plugin” button.
3. Choose the downloaded plugin file and click “Install Now”.
4. After the installation is complete, click “Activate”.

**Post-Activation:**
Once activated, access the plugin's settings by navigating to the “WPFactory” menu and look for the relevant tab.

== Screenshots ==

1. Main Page - General
2. Validation & Progress
3. Admin settings & Advanced options

= Feedback =

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/eu-vat-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WPFactory > EU VAT".

== Changelog ==

= 4.3.7 - 02/04/2025 =
* Fix - Block Checkout - `VIES_UNAVAILABLE` - `%vies_error%` placeholder fixed.
* Dev - Validation - Check company name - "Accept empty responses" option added (defaults to `no`).

= 4.3.6 - 27/03/2025 =
* Fix - Display - In billing address - "Is checkout" check reversed for the block-based checkout.
* Dev - "Compatibility" section added.
* Dev - Compatibility - PDF Invoices & Packing Slips for WooCommerce - "Prefix" option added (defaults to an empty string).

= 4.3.5 - 25/03/2025 =
* Fix - Checkout block field - Reverse updated script dependencies. Instead add "Advanced > Checkout block field > Add script dependency" option (defaults to `no`).

= 4.3.4 - 23/03/2025 =
* Fix - Display - In billing address - "Is checkout" check removed.
* Dev - Checkout block field - Update script dependencies.

= 4.3.3 - 21/03/2025 =
* Fix - Admin - Fix the EU VAT field doubling on the admin order edit page when the "Display" option is set to "In billing address".
* Dev - Developers - `alg_wc_eu_vat_get_field_data` filter added.
* WC tested up to: 9.7.

= 4.3.2 - 27/02/2025 =
* Fix - Switch the UK VAT validation to VATSense.com API.

= 4.3.1 - 25/02/2025 =
* Dev - Checkout block field - Code refactoring.
* Dev - Validation - Minor code refactoring.
* Dev - Admin settings - Descriptions updated.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_no_soap_api_url` filter added.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_curl_disable_ssl` - Apply everywhere.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_response` - More arguments added.

= 4.3.0 - 20/02/2025 =
* Dev - Admin - "Admin new order email" option added (defaults to `no`).

= 4.2.9 - 18/02/2025 =
* Fix - VAT parsing - Possible "PHP Warning: Trying to access array offset on false" fixed.
* Fix - Compatibility - "PDF Invoices & Packing Slips for WooCommerce" by "WP Overnight" - EU VAT doubling when the "Display" option is set to "In billing address" fixed.
* Dev - Admin settings - Descriptions updated.

= 4.2.8 - 13/02/2025 =
* Fix - Block-based checkout compatibility - "Show zero VAT" option fixed.
* Dev - Admin settings - General - Descriptions updated; settings rearranged; subsections added.
* Dev - Code refactoring.

= 4.2.7 - 11/02/2025 =
* Fix - Admin - Validate VAT and remove taxes - "Request Identifier" fixed.
* Fix - SOAP - "Get VAT details" + "Request Identifier" fixed.
* Fix - EU VAT report fixed.
* Dev - Advanced - "Force price display including tax" option added (defaults to `no`).
* Dev - Admin settings - General - Descriptions updated.
* Dev - Developers - EU VAT report - `alg_wc_eu_vat_report_order_statuses` filter added.
* Dev - Code refactoring.

= 4.2.6 - 08/02/2025 =
* Dev - Messages - "Wrong billing country" option added.
* Dev - Admin settings - Messages - Section renamed (was "Progress"); descriptions updated.

= 4.2.5 - 05/02/2025 =
* Fix - Checkout block field - "Keep VAT if shipping country is different from billing country" option fixed.
* Fix - Do not use session in the "Get VAT details" in admin.
* Fix - Do not use transients in the "Get VAT details" admin notices.
* Dev - VIES error - "cURL" and "Simple" validation methods included.
* Dev - Admin settings - Validation - Descriptions updated.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_methods` filter added.
* Dev - Developers - `alg_wc_eu_vat_is_checkout` filter added.
* Dev - Code refactoring.
* Readme - Changelog cleanup.

= 4.2.4 - 03/02/2025 =
* Fix - Hide EU VAT field from checkout - Fixed.
* Dev - Security - Output escaped.
* Dev - Languages - POT file regenerated with WP-CLI.
* Dev - Code refactoring.

= 4.2.3 - 31/01/2025 =
* Dev - Request identifier - "cURL" and "Simple" validation methods included.
* Dev - Compatibility - "Fluid Checkout for WooCommerce" plugin option added (defaults to `no`).
* Dev - Admin settings sections rearranged ("Validation & Progress" and "Admin & Advanced" split).
* Dev - Code refactoring.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_curl_disable_ssl` filter added.
* Dev - Developers - Validation - `alg_wc_eu_vat_validation_response` filter added.

= 4.2.2 - 28/01/2025 =
* Dev - Admin - Validate VAT and remove taxes - Update the `is_vat_exempt` meta.
* Dev - Admin - Validate VAT and remove taxes - Update the "Request Identifier" meta.
* Dev - PHP notices fixed.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* Dev - Coding standards improved.

= 4.2.1 - 26/01/2025 =
* Fix - Empty EU VAT field - Do not validate when optional; correct message when required ("Progress Messages > Is required" option added).
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring and cleanup.

= 4.2.0 - 25/01/2025 =
* Dev - Security - Output escaped.
* Dev - Admin & Advanced - "Request identifier" options added (disabled by default).
* Dev - Code refactoring.

= 4.1.0 - 23/01/2025 =
* Fix - Keep VAT in selected countries - Fixed.
* Dev - Security - Output escaped.
* Dev - Security - Input sanitized.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring and cleanup.
* Dev - Coding standards improved.
* WC tested up to: 9.6.

= 4.0.0 - 19/01/2025 =
* Fix - Sessions - Session status check added.
* Fix - Keep VAT for specific products - "get_cart was called incorrectly" PHP notice fixed (`did_action( 'wp_loaded' )` check added).
* Fix - Exempt VAT - Fixed.
* Fix - Required / Optional in countries - Fixed.
* Fix - Checkout block field - "Keep VAT in selected countries" option fixed.
* Fix - Checkout block field - User meta copied (on `woocommerce_customer_save_address` and `woocommerce_created_customer`).
* Fix - Checkout block field - The duplicated EU VAT field removed from the admin order edit page.
* Fix - Checkout block field - The EU VAT field removed from the "My account > Account details".
* Dev - Security - Output escaped.
* Dev - Validation Options - 'Validate in "My account"' option added (defaults to `no`).
* Dev - General - "Show VAT details in checkout" option added (defaults to `no`).
* Dev - "Get VAT details" link added in order for retrieving VAT details.
* Dev - VAT IDs with non-alphanumeric symbols are not allowed now.
* Dev - Checkout block field - Block-related JavaScript code moved to a dedicated block file.
* Dev - Admin settings descriptions updated.
* Dev - Major code refactoring and cleanup.
* Dev - Coding standards improved.

= 3.2.4 - 10/01/2025 =
* Fix - Checkout block field - Default value fixed.

= 3.2.3 - 07/01/2025 =
* Fix - When the "Admin > Checkout block field" option was enabled, the EU VAT field was hidden on the admin order edit page.

= 3.2.2 - 31/12/2024 =
* Fix - Show field for selected user roles only.

= 3.2.1 - 29/12/2024 =
* Dev - Code refactoring and cleanup.

= 3.2.0 - 28/12/2024 =
* Fix - "High-Performance Order Storage (HPOS)" compatibility.
* Dev - Code refactoring.

= 3.1.6 - 27/12/2024 =
* Dev - Compatibility - WPML/Polylang - `wpml-config.xml` file added.
* Dev - Composer - `autoloader-suffix` param added.
* Dev - Key Manager - Library updated.
* WC tested up to: 9.5.

= 3.1.5 - 26/11/2024 =
* Fix - VAT validation issue on block checkout.
* Fix - Enable/disable progress messages.

= 3.1.4 - 16/11/2024 =
* Fix - VAT error message not disappearing after company name update.
* Dev - Code refactoring and cleanup.

= 3.1.3 - 15/11/2024 =
* Plugin name updated (was "EU/UK VAT Manager for WooCommerce").

= 3.1.2 - 14/11/2024 =
* Fix - Missing library files uploaded.
* Tested up to: 6.7.
* WC tested up to: 9.4.

= 3.1.1 - 09/11/2024 =
* Dev - Initializing the plugin on the `plugins_loaded` action.
* Dev - Code refactoring.

= 3.1.0 - 08/11/2024 =
* Dev - Plugin settings moved to the "WPFactory" menu.
* Dev - "Recommendations" added.
* Dev - "Key Manager" added.
* Dev - Code refactoring and cleanup.

= 3.0.1 - 30/10/2024 =
* Fix - Adjusted the VAT validation message location during account creation.
* Fix - Resolved localization shortcode {billing_eu_vat_number} issue in the block checkout.
* Fix - Resolved error "Function get_cart was called incorrectly".
* Dev - Code cleanup.

= 3.0.0 - 23/10/2024 =
* Fix - Localization issue.
* Fix - Tax calculation on country change.
* Fix - Cross-Site Request Forgery vulnerability.
* Dev - Keep VAT for specific products.
* Dev - General - "Enable plugin" option removed.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring and cleanup.
* WC tested up to: 9.3.
* WooCommerce added to the "Requires Plugins" (plugin header).

[See changelog for all versions](https://plugins.svn.wordpress.org/eu-vat-for-woocommerce/trunk/changelog.txt).

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
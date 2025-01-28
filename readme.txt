=== EU/UK VAT Validation Manager for WooCommerce ===
Contributors: wpcodefactory, omardabbas, karzin, anbinder, algoritmika, kousikmukherjeeli, aegkr
Tags: EU VAT, UK VAT, tax, vat validation, VAT
Requires at least: 6.1
Tested up to: 6.7
Stable tag: 4.2.2
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
* WC tested up to: 9.4.
* Tested up to: 6.7.

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
* Add - Keep VAT for specific products.
* Dev - General - "Enable plugin" option removed.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring and cleanup.
* WC tested up to: 9.3.
* WooCommerce added to the "Requires Plugins" (plugin header).

= 2.12.14 - 26/09/2024 =
* Fix - Cross-Site Scripting vulnerability.
* Add - EU VAT filed to "My Account" - "Edit Address".

= 2.12.13 - 23/09/2024 =
* Fix - Cross-Site Scripting vulnerability.

= 2.12.12 - 12/09/2024 =
* Fix - HPOS error issue.

= 2.12.11 - 06/09/2024 =
* Add - Validation & Progress - Remove validation color.
* WC tested up to: 9.2

= 2.12.10 - 30/08/2024 =
* Fix - skip validation when create user through API.

= 2.12.9 - 28/08/2024 =
* Fix - Order metabox HPOS function support.
* Fix - Added a condition for the EU VAT field on the checkout and cart pages, restricting it from appearing on other pages where the object is not available.
* Fix - skip validation when create user through API.

= 2.12.8 - 01/08/2024 =
* Compatibility with WordPress 6.6 verified.
* Fix - alg_wc_eu_vat_parse_vat() trim vat number with ['-','.',' ']

= 2.12.7 - 30/07/2024 =
* Fix - Accept VAT when VIES is down (extra catch soapFault).

= 2.12.6 - 29/07/2024 =
* Accept VAT when VIES is down (extra catch soapFault).
* WC tested up to: 9.1.

= 2.12.5 - 18/07/2024 =
* Add - Admin & Advanced > VAT Validation for Orders Created Manually from the Admin Side.
* WC tested up to: 9.0.

= 2.12.4 - 17/07/2024 =
* Fix - added condition for filter_available_payment_gateways_allowed() for preserved countries.
* Add - YITH invoice EU VAT field.

= 2.12.3 - 02/07/2024 =
* Fix - `eu_vat_admin_footer()` for admin order creation.

= 2.12.2 - 01/07/2024 =
* Fix - `admin_inline_js()` for admin order creation.

= 2.12.1 - 30/06/2024 =
* Fix - Deployment.

= 2.12.0 - 30/06/2024 =
* Fix - Checkout block compatibility.

= 2.11.12 - 29/06/2024 =
* Add - Autofill company name from VAT ID.

= 2.11.11 - 28/06/2024 =
* Add - Accept the VAT number if VIES is not available.

= 2.11.10 - 27/06/2024 =
* Add - Checkout block field.

= 2.11.9 - 27/06/2024 =
* Add - Force validate on cart and checkout page load/reload.

= 2.11.8 - 26/06/2024 =
* Fix - React constant error.

= 2.11.7 - 26/06/2024 =
* Update - WC Order HPOS EU VAT filter.

= 2.11.6 - 25/06/2024 =
* Fix - Checkout block VAT validation.

= 2.11.5 - 18/06/2024 =
* Fix - "Required if customer fills the company field" and "Show field for selected countries only" cross logic check and fixed.
* Fix - Checkout block move EU VAT field to customer info section.
* Fix - Some error notice.
* Fix - Issue with "Keep VAT in selected countries".

= 2.11.4 - 13/06/2024 =
* Fix - "Allow VAT number input without country code" for Greek VATs.

= 2.11.3 - 13/06/2024 =
* Dev - HPOS - Admin orders list for EU VAT.

= 2.11.2 - 09/06/2024 =
* Fix - Checkout block error.

= 2.11.1 - 05/06/2024 =
* Dev - Update condition in `alg_eu_vat_update_block_order_meta_eu_vat()`.

= 2.11.0 - 28/05/2024 =
* Checkout block support.
* WC tested up to: 8.9

= 2.10.3 - 04/04/2024 =
* Fix - error in alg_wc_eu_vat_validate_vat_no_soap() function.
* Compatibility with WordPress 6.5 verified.
* WC tested up to: 8.7

= 2.10.2 - 26/03/2024 =
* Fix - Optional text translation support.
* Fix - remove woocommerce-validated class on first load.

= 2.10.1 - 26/03/2024 =
* Add - Admin & Advance > Reduce concurrent request to VIES
* Fix - code check for manual validation at checkout.

= 2.10.0 - 21/03/2024 =
* Add - filter alg_wc_eu_vat_get_field_id, alg_wc_eu_vat_confirmation_bg_yes, alg_wc_eu_vat_confirmation_bg_no.
* Fix - $ is undefined in alg-wc-eu-vat-place-order.js
* Fix - Translation of yes / no button.

= 2.9.21 - 20/03/2024 =
* Add - Admin & Advance > Remove country from VAT number in REST API

= 2.9.20 - 19/03/2024 =
* Fix - The "wpo_wcpdf_after_order_details" filter requires an additional condition to retrieve values from "exempt_vat_from_admin = yes".

= 2.9.19 - 29/02/2024 =
* Add - General - Remove the VAT field if the tax status is "none"

= 2.9.18 - 04/12/2023 =
* Add - Admin & Advanced > Enable manual validation of VAT numbers.
* Add - Admin & Advanced > VAT numbers to pass validation.

= 2.9.17 - 01/12/2023 =
* Add - Admin & Advanced > VAT shifted text.

= 2.9.16 - 29/11/2023 =
* Fix - Update API url of VIES curl validation.
* Fix - Update "Add progress messages" to default "yes".
* Add - Validation & Progress > Validate action trigger.
* Checked compatibility with WC 6.4 & WP 8.3

= 2.9.15 - 15/11/2023 =
* Fix - Company name match in lowarcase.

= 2.9.14 - 09/11/2023 =
* WC tested up to: 8.2
* Add: Validation & Progress > Validate at signup form.

= 2.9.13 - 21/09/2023 =
* Compatibility with WordPress 6.3 verified
* Update filter text "EU VAT not provided".
* WC tested up to: 8.1
* Adjust HPOS compatibility.

= 2.9.12 - 12/08/2023 =
* Dev - HPOS compatibility.

= 2.9.11 - 12/08/2023 =
* Dev - Add new filter "EU VAT provided" to user table.
* WC tested up to: 7.9.

= 2.9.10 - 27/06/2023 =
* Dev - Update JS function `alg_wc_eu_vat_validate_vat` with load flag.

= 2.9.9 - 15/06/2023 =
* Dev - Extend validation result with new hook `alg_wc_eu_vat_check_alternative`.
* WC tested up to: 7.8.

= 2.9.8 - 30/05/2023 =
* Dev - Update with extra character trim with eu VAT validator response.
* WC tested up to: 7.6.

= 2.9.7 - 11/04/2023 =
* Update woocommerce_before_calculate_totals priority from MAX to 99, so users can run their own overwrite.
* Compatibility with WordPress 6.2 verified.

= 2.9.6 - 31/03/2023 =
* Move to WPFactory.
* Enhanced field validation on page load.

= 2.9.5 - 18/03/2023 =
* PDF invoicing compatibility is part of the free version
* Fixed a bug in field starting validation on checkout page load
* Altered classname to allow more control on the field
* Verified compatibility with WooCommerce 7.5

= 2.9.4 - 08/03/2023 =
* Enhanced field border color for validation (before & after entering values)

= 2.9.3 - 21/02/2023 =
* Fixed a bug to make VAT field mandatory if a company field is not empty
* Enhanced checkout VAT recalculations once VAT number changed/removed
* Verified compatibility with WooCommerce 7.4
* More compatibility with PHP 8.2 introduced by addressing deprecated methods

= 2.9.2 - 06/02/2023 =
* Fixed a bug in free version regarding "Preserve VAT in selected countries"
* Enhanced handling for "Undefined_constant" errors

= 2.9.1 - 30/01/2023 =
* Fixed warning message for Taxes group
* Enhanced VAT handling when manually editing an order for a preserved country
* Error messages in PHP 8.2 (creation of dynamic property)
* Reverted options to select validation methods

= 2.9 - 14/01/2023 =
* Improved session handling
* Fixed a bug showing PHP warning (title not defined)
* Verified compatibility with WooComemrce 7.3

= 2.8.5 - 06/12/2022 =
* Removed cURL & Simple validation methods as they are no longer used on VIES
* Added new shortcodes to translate EU VAT field in WPML & Polylang
* Enhancement to UK validation method

= 2.8.4 - 29/11/2022 =
* Enhanced session validation on checkout & cart pages, leading to better performance
* Added validation messages class names to tooltips

= 2.8.3 - 23/11/2022 =
* Enhanced validation checks when using PHP sessions, making more compatibility with stores using multisites plugins
* New option to completely remove the field from checkout so you can control it using field ID with checkout page builders
* Progress messages got class names, allowing customizing them using CSS

= 2.8.2 - 18/11/2022 =
* The plugin will automatically add VAT number to invoices in the popular plugin (PDF Invoices & Packing Slips)
* Bug fixes in cache handling

= 2.8.1 - 13/11/2022 =
* New feature: You can now show a custom message when VAT is valid but not matching company name (probably a minor typo)
* Fixed a bug in show/hide field for countries
* Enhancements on registration values passed to VAT validation
* Compatibility with WooCommerce 7.1 and WordPress 6.1 verified

= 2.8 - 20/09/2022 =
* Fixed a bug blocking checkout on valid numbers
* Allowed checking out if billing & shipping countries are different
* Enhanced VAT calculation when "Shipping to a different address" is unchecked without a refresh
* Compatibility with WooCommerce 6.9

= 2.7.4 - 02/09/2022 =
* Hotfix for a bug caused by SiteGround optimizer plugin and add related setting under Advanced tab

= 2.7.3 - 01/09/2022 =
* Fixed several bugs when field is required/optional
* Fixed bug allowing checkout on black VAT even field is required
* Fixed a bug on option "Show field in these countries"
* Enhanced caching mechanism on SiteGround hosting
* New feature: You can now verify if shipping country is same as billing country and preserve VAT if so

= 2.7.2 - 10/08/2022 =
* Added a new option to make field required in all countries except selected
* Compatibility with WooCommerce 6.8

= 2.7.1 - 04/07/2022 =
* Verified compatibility with WooCommerce 6.6

= 2.7 - 11/06/2022 =
* Update: Preserve VAT in shop base country/specific countries is now in FREE version
* Verified compatibility with WooCommerce 6.5 & WordPress 6.0

= 2.6.3 - 15/04/2022 =
* Fixed an issue in validating VAT on signup if field was empty
* Fixed a PHP Deprecated warning message & PHP Uncaught TypeError: explode()
* Verified compatibility with WooCommerce 6.4

= 2.6.2 - 26/03/2022 =
* Added dependency to the wp_enqueue_script function related to Ajax handling
* Fixed an issue when EU VAT field is optional while creating new users using REST-API

= 2.6.1 - 19/03/2022 =
* Verified compatibility with WooCommerce 6.3
* Added a new option in the revamped "Required" section to make the VAT field required if company field is filled

= 2.6 - 27/02/2022 =
* Added a new option to make the field required on selected countries only
* Enhanced how coupons tax should be handled on checkout
* Changed PHP_MAX_INT priority from server max. to 99 to allow more control for admins
* Fixed Uncaught Error: Call to undefined function message

= 2.5.4 - 18/02/2022 =
* Added an option to allow checkout even if VAT is not registered in VIES
* Added a new option to filter orders with VAT numbers in order admin page
* Verified compatibility with WooCommerce 6.2

= 2.5.3 - 28/01/2022 =
* Verified compatibility with WordPress 5.9 & WooCommerce 6.1
* Added an option to remove tax if customer is out of EU (Belgium regulations)
* Added an option to collect & validate VAT numbers in signup forms

= 2.5.2 - 11/12/2021 =
* Compatibility issue with Wholesale plugin user roles

= 2.5.1 - 10/12/2021 =
* New feature added: Allow specific payment gateway if VAT is valid (i.e. for B2B to allow wire transfers)
* Verified compatibility with WooCommerce 5.9

= 2.5 - 06/11/2021 =
* Fixed a bug in showing EU VAT label if not filled.
* Verified compatibility with WooCommerce 5.8

= 2.4.5 - 10/10/2021 =
* Enhanced EU VAT appearance in billing section so it's easily identified
* UK VAT numbers are space-tolerated so plugin will read VAT numbers with/without spaces
* Verified compatibility with WooCommerce 5.7

= 2.4.4 - 20/09/2021 =
* NEW: The plugin now validates UK VAT numbers as well
* Verified compatibility with WooCommerce 5.6

= 2.4.3 - 06/08/2021 =
* Fixed a warning message regarding AJAX being broken

= 2.4.2 - 26/07/2021 =
* Added an option to hide validation messages in preserved countries
* Fixed a bug in removing VAT if shipping address is a forwarding address
* Verified compatibility with WordPress 5.8

= 2.4.1 - 13/07/2021 =
* Added an option to validate VAT based on final destination (if order is sent to a forwarding address)
* Fixed undefined index & order ID warning messages
* Verified compatibility with WooCommerce 5.5

= 2.4 - 24/06/2021 =
* Added a popup section to open official VIES website in orders backend (to verify VAT info on order)
* Verified compatibility with WooCommerce 5.4

= 2.3.3 - 16/05/2021 =
* Fixed a bug was showing "Undefined index" errors when connecting through SSH
* Verified compatibility with WooCommerce 5.3

= 2.3.2 - 03/05/2021 =
* Fixed a bug in session not firing in store
* Added tolerance for dash (-) in case VAT number was entered with a dash

= 2.3.1 - 30/04/2021 =
* Enhanced session configuration
* Added a feature to preserve tax if valid VAT number holders are not exempted (useful in Belgium)
* Tested compatibility with WooCommerce 5.2

= 2.3 - 20/04/2021 =
* Added new option to allow user to select VAT option
* Added banners on the sidebar
* Added a filter to control changes updates
* Checked compatibility with WC 5.1 & WP 5.7

= 2.2.5 - 28/02/2021 =
* Tested compatibility with WC 5.0

= 2.2.4 - 27/01/2020 =
* Tested compatibility with WC 4.9

= 2.2.3 - 30/12/2020 =
* Tested compatibility with WC 4.8 & WP 5.6
* Changed default session type to WooCommerce session

= 2.2.2 - 21/11/2020 =
* Tested compatibility with WC 4.7
* Plugin name updated

= 2.2.1 - 14/10/2020 =
* Fixed a warning message that was appearing the Site Health Check

= 2.2 - 02/10/2020 =
* Added more strings to be translatable using multi-language sites
* Tested compatibility with WC 4.5

= 2.1 - 20/08/2020 =
* Fixed a bug that wasn't exempting VAT on manual orders (WP backend)

= 2.0.1 - 15/08/2020 =
* Tested compatibility with WP 5.5
* Tested compatibility with WC 4.3

= 2.0 - 25/06/2020 =
* Fixed a bug that prevented showing the correct message (valid successful) for compatibility with some themes JS
* Enhanced the SOAP method via using better communication method with EU VAT servers

= 1.9 - 17/06/2020 =
* Stopped calling the main JS file on all pages and keep it only on checkout for better performance
* Removed the string from a deprecated argument to get list of countries
* Fixed a minor issue that was causing error (failed to load external entity ) in communicating with VIES servers in some cases

= 1.8.1 - 25/03/2020 =
* Checked all plugin features compatibility with WC 4

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
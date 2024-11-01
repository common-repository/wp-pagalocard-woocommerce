=== Pagalo - WooCommerce Payment Gateway ===
Contributors: XicoOfficial, digitallabs
Donate link: https://digitallabs.agency
Tags: pagalocard, pagalo card, pagalo, visanet, guatemala
Requires at least: 3.8
Requires PHP: 5.2.4
Tested up to: 6.6
Stable tag: 2.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows your store to make payments via Pagalo service.


== Description ==

= Pagalo - WooCommerce Payment Gateway =

If the transaction is successful the order status will be changed to “processing”. If the payment charge failed the order status will be changed to “cancelled”. If something is wrong with the connection between your server and the Pagalo server the order status will be changed to “on-hold”. After successful transaction the customer is redirected to the default WP thank you page.

= Support =

Use the wordpress support forum for any questions regarding the plugin, or if you want to improve it.

= Get Involved =

Looking to contribute code to this plugin? Go ahead and [fork the repository over at GitHub](https://github.com/DigitalLabsAgcy/wp-pagalo-woocommerce).
(submit pull requests to the latest "release-" tag).

== Usage ==

To start using the "Pagalo - WooCommerce Payment Gateway", first create an account at [Pagalo.co](https://pagalo.co). They will provide you with your account "IdenEmpresa", "Token", "Private key" and "Public Key".


After you have your Pagalo or sandbox account active:

1. Head to Woocommerce Settings and click on the Checkout tab.
2. On checkout options you should see the option "Pagalo", click on it.
3. Enable the payment gateway byt checking the checkbox that reads "Enable this payment gateway".
4. Fill the form with your account information. Don't forget to check the "Enable Test Mode" box if your account is from the sandbox.
5. Click on save changes and you should be ready to start accepting credit cards with Pagalo.

== Installation ==

Installing "Pagalo - WooCommerce Payment Gateway" can be done using the following steps:
	
1. Download the plugin.
1. Upload the ZIP file through the "Plugins > Add New > Upload" screen in your WordPress dashboard.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I contribute? =

We encourage everyone to contribute their ideas, thoughts and code snippets. This can be done by forking the [repository over at GitHub](https://github.com/DigitalLabsAgcy/wp-pagalo-woocommerce).

== Screenshots ==

1. The Pagalo payment gateway settings page showing the texts and description that can be customized.

2. The Pagalo payment gateway settings page showing the fields that need to be filled with each individual account information.

3. The checkout page with the pagalo payment credit card form.

== Changelog ==

= 2.1.0 =
* - For advanced discounts that don't substract the discount to every product, we added an option to send a single product to pagalo(with the order Total) instead of full order details.

= 2.0.0 =
* - Add support for Pagalo V2 and installments

= 1.2.2 =
* - Add more detailed notices for both clients and websites admins.

= 1.2.1 =
* - Remove unnecesary messages when payment fails.

= 1.2.0 =
* - Add support for Woocommerce compatibility check.
* - Go back to WP API instead of Curl

= 1.1.1 =
* Go back to using Curl isntead of WP API due to problems with multicurrency(will keep testing and go back to WP API once its fully functional for multi-currency)
* Reorder the post fields on wordpress settings page to match the orther they are displayed on the Pagalo platform.

= 1.1.0 =
* Add banner and icon images
* Add readme.txt file

= 1.0.0 =
* Integration with Pagalo to acept credit card payments. 


== Upgrade Notice ==

= 1.2.0 =
* Add support for Woocommerce compatibility check.

= 1.1.0 =
* Get the plugin ready to upload to the WordPress repo

= 1.0.0 =
* Initial release. Yeah!


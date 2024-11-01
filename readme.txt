=== Spoddano - Cardano For Woocommerce ===
Contributors: stixen84
Tags: Cryptocurrency, Cardano, Woocommerce
Donate link: https://www.paypal.com/donate?business=JBLNTNZBHX9NN&no_recurring=1&currency_code=AUD 
Requires at least: 5.8.0
Tested up to: 6.6.2
Stable tag: 1.2.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple Cardano (ADA) payment gateway for Woocommerce.

== Description ==

**PLEASE NOTE: THIS PLUGIN ONLY WORKS WITH THE CLASSIC WOOCOMMERCE CHECKOUT**

Spoddano - Cardano For Woocommerce enables sellers to accept Cardano (ADA) cryptocurrency as payment for their products on woocommerce stores.

It uses real time exchange rates provided by coinmarketcap.com api. Sellers enter in their receiving address and a QR code image(optional) and then it's up to the buyer to process the transaction using their wallets. 

Once they have sent the ADA they enter in a transaction ID and place the order. They will get sent the processing order email as standard by Woocommerce. The seller will need to then confirm the transaction on their end and complete the order.

== Installation ==

From your WordPress dashboard

1. Plugins > Add New
2. Search for "Spoddano" and Install
3. Activate Spoddano from your Plugins page
4. Get your free API key from: <a href="https://pro.coinmarketcap.com/signup/" target="_blank">https://pro.coinmarketcap.com/signup/</a>
5. Go to Woocommerce > Settings > Payments and click on manage
6. Enable the plugin and add your API key.
7. Add the RECEIVING ADDRESS of your Cardano Wallet.
8. If your wallet provides a QR Code Image, upload this to your Wordpress media library and copy paste the URL to it in the field provided.
9. Add in any other details you want for the checkout page.
10. Click on 'Save Changes' and you're DONE!

== Frequently Asked Questions ==

= Where do i get the API key from? =

coinmarketcap.com offers a free basic API key to call the current exchange rate of the currency used in your store. 
Get yours here: <a href="https://pro.coinmarketcap.com/signup/" target="_blank">https://pro.coinmarketcap.com/signup/</a>

= Where does one enter the transaction ID to confirm the transaction? =

After you get sent the transaction ID you can follow this up on the Cardano Blockchain Explorer by clicking on the link provided in the orders dashboard or admin email.

If there are multiple transactions for the specified ID, do a search for your address within the list of "To" addresses.

= Support =
If you find an issue or bug, please email me at steven@spiraloutdesigns.com

== Screenshots ==

1. Payment Settings
2. The QR Code and Copy Address
3. The Transaction ID field and Disclaimer
4. Email invoice with details

== Changelog ==

= 1.2.1 =
* Switch Sessions to use Transients instead, better performance, less api calls

= 1.2.0 =
* Rework API retrieve function to only make the call once
    -Increase performance of checkout page
    -Less credits required for the coinmarketcap API 

= 1.1.1 =
* Style Tweak - fix QR code image not centred

= 1.0.9 =
* Fix address on some stores from breaking outside container

= 1.0.8 =
* Fix amount from showing twice

= 1.0.7 =
* Add a copy amount button, for easier usability.

= 1.0.6 =
* Fix Global Variable that may have conflicted with other gateways

= 1.0.5 =
* Fix email error logging issue

= 1.0.4 =
* Add the ability to disable the transaction id field
* Further code optimisations

= 1.0.3 =
* Tidy up transaction details on email and thankyou page
* Provide direct link to Cardano Explorer with provided transaction id on email, thankyou page, orders dashboard
* Tidy up code

= 1.0.2 =
* Add validation for transaction id/hash number
    -must be 64 characters
    -must not contain any uppercase letters
    -must only contain letters between a-f
    -must be made up of both letters and numbers
* Fix bug whereby Cardano details showing up on thankyou page and emails for other payment methods used

= 1.0.1 =
* Fix bug that required transaction id field when other payment methods were used
* Fix bug in Firefox returning false page when clicking on "Copy Address" button

= 1.0.0 =
* First Stable version released
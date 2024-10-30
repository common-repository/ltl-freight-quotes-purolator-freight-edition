=== LTL Freight Quotes - Purolator Edition ===
Contributors: enituretechnology
Tags: eniture,Purolator,,LTL freight rates,LTL freight quotes, shipping estimates
Requires at least: 6.4
Tested up to: 6.6
Stable tag: 2.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Real-time LTL freight quotes from Purolator Freight. Fifteen day free trial.

== Description ==

Purolator is headquartered in Mississauga, Ontario and is Canada’s premier shipping company. If you don’t have a Purolator account number, contact them at 888-744-7123, or register online[https://eshiponline.purolator.com/ShipOnline/SecurePages/Public/Register.aspx].

**Key Features**

* Displays negotiated LTL shipping rates in the shopping cart.
* Provides quotes for shipments within Canada and to the United States.
* Define a custom label to identify the LTL freight rate in the cart.
* Elect to display carrier transit times with rate estimates.
* Define multiple warehouses.
* Identify products that drop ship from vendors.
* Product specific shipment parameters: weight, dimensions, freight class.
* Option to set a product’s freight class automatically through the built in density calculator.
* Support for variable products. Shipment parameters can be set differently for each product variation.
* Option to always include the residential delivery charge.
* Option to include lift gate delivery charge.
* Option to mark up shipping rates by a set dollar amount or by a percentage.
* Works seamlessly with other quoting apps published by Eniture Technology.


**Requirements**

* WooCommerce 6.4 or newer.
* A Purolator account number.
* A Purolator production key.
* A Purolator production password
* A API key from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* A Purolator account number.
* A Purolator production key.
* A Purolator production password

If you need assistance obtaining any of the above information, contact your local Purolator
or call the [Purolator](http://purolator.com) corporate office at 888-744-7123.

A more extensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-purolator-ltl-freight/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "eniture purolator freight quotes", and click Install Now.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get a API key from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-purolator-ltl-freight/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your API keys and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the API key. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => Purolator Freight. Use the *Connection* link to create a connection to your purolator account.

**4. Select the plugin settings**
Go to WooCommerce => Settings => Purolator Freight. Use the *Quote Settings* link to enter the required information and choose
the optional settings.

**6. Define warehouses and drop ship locations**
Go to WooCommerce => Settings => Purolator Freight. Use the *Warehouses* link to enter your warehouses and drop ship locations.  You should define at least one warehouse, even if all of your products ship from drop ship locations. Products are quoted as shipping from the warehouse closest to the shopper unless they are assigned to a specific drop ship location. If you fail to define a warehouse and a product isn’t assigned to a drop ship location, the plugin will not return a quote for the product. Defining at least one warehouse ensures the plugin will always return a quote.

**7. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the Shipping Zones link. Add a US domestic shipping zone if one doesn’t already exist. Click the “+” sign to add a shipping method to the US domestic shipping zone and choose Purolator Freight from the list.

**8. Configure your products**
Assign each of your products and product variations a weight, Shipping Class and freight classification. Products shipping LTL freight should have the Shipping Class set to “LTL Freight”. The Freight Classification should be chosen based upon how the product would be classified in the NMFC Freight Classification Directory. If you are unfamiliar with freight classes, contact the carrier and ask for assistance with properly identifying the freight classes for your  products.

== Frequently Asked Questions ==

= How do I get a Purolator account number?

Contact them at 888-744-7123, or register online[https://eshiponline.purolator.com/ShipOnline/SecurePages/Public/Register.aspx].

= Where do I find my purolator.com username and password?

Contact Purolator customer service at 888-744-7123, or try to recover it online[https://purolator.com/].

= Where do I get my Purolator Activation Key?

Obtaining a Purolator Activation Key is done on purolator.com and requires a few steps. Refer to the Documentation tab on https://eniture.com/woocommerce-purolator-ltl-freight/ for detailed instructions.
You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or purchased a API key outright. At the conclusion of the registration process an email will be sent to you that will include the API key. You can also login to eniture.com using the username and password you created during the registration process and retrieve the API key from the My Licenses tab.

= How do I get a API key for the plugin?

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or purchased a API key outright. At the conclusion of the registration process an email will be sent to you that will include the API key. You can also login to eniture.com using the username and password you created during the registration process and retrieve the API key from the My API keys tab.

= How do I change my plugin API key from the trial version to one of the paid subscriptions?

Login to eniture.com and navigate to the My API keys tab. There you will be able to manage the licensing of all of your Eniture Technology plugins. Refer to the Documentation tab on this page for more thorough instructions.

= How do I install the plugin on another website?

The plugin has a single site API key. To use it on another website you will need to purchase an additional API key. If you want to change the website with which the plugin is registered, login to eniture.com and navigate to the My API keys tab. There you will be able to change the domain name that is associated with the API key.

= Do I have to purchase a second API key for my staging or development site?

No. Each API key allows you to identify one domain for your production environment and one domain for your staging or development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website?

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > Purolator Freight > Connections) then you have one or more of the following licensing issues: 1) You are using the API key on more than one domain. The API keys are for single sites. You will need to purchase an additional API key. 2) Your trial period has expired. 3) Your current API key has expired and we have been unable to process your form of payment to renew it. Login to eniture.com and go to the My API keys tab to resolve any of these issues.

= Why are the shipment charges I received on the invoice from Purolator different than what was quoted by the app?

Common reasons include a difference in the quoted versus billed shipment parameters (weight, dimensions, freight class), or additional services (such as residential delivery) were required. Compare the details of the invoice to the shipping settings on the products included in the shipment. Consider making changes as needed. Remember that the weight of the packing materials is included in the billable weight for the shipment. If you are unable to reconcile the differences call Purolator customer service for assistance. (888-744-7123)

= Why do I sometimes get a message that a shipping rate estimate couldn’t be provided?

There are several possibilities:

The most common reason is that one or more of the products in the shopping cart did not have its shipment parameters (weight, dimensions, freight class) adequately populated. Check the settings for the products on the order and make corrections as necessary.

1) The city entered for the shipping address may not be valid for the postal code entered. A valid City+State+Postal Code combination is required to retrieve a rate estimate. Contact us by phone (404-369-0680) or email (support@eniture.com) to inquire about Address Validation solutions for this problem.

2) Your shipment exceeded constraining parameters of Purolator’s web service.

3) The Purolator web service isn’t operational.

4) Your Purolator account has been suspended or cancelled.

5) There is an issue with the Eniture Technology servers.

6) Your subscription to the plugin has expired because payment could not be processed.

7) There is an issue your server.

= How do I determine the freight class for my product(s)?

The easiest thing to do is to contact your Southeastern Freight Lines representative and ask for assistance. However, the official source is the National Motor Freight Classification (NMFC) directory maintained by the National Motor Freight Transportation Agency (NMFTA.org). You can purchase a hard copy of the directory or obtain an online subscription to it from their web site.

= How does the density calculator work?

The density calculator will calculate a freight class by performing a calculation using the product weight and dimensions as inputs. In most cases the returned freight class will match the product’s (commodity’s) freight class as recorded in the National Motor Freight Classification (NMFC) directory. However, this is not always true and in the event there are differences, the freight class recorded in the National Motor Freight Classification (NMFC) directory takes precedence. An incorrectly identified freight class can result in unexpected shipping charges and even fees. You are solely responsible for accurately identifying the correct freight class for your products. If you need help doing this, contact your Southeastern Freight Lines Freight representative for assistance.


== Screenshots ==

1. Quote settings page
2. Warehouses and Drop Ships page
3. Quotes displayed in cart

== Changelog ==

= 2.2.3 =
* Update: Updated connection tab according to WordPress requirements 

= 2.2.2 =
* Fix: Fixed the Test Connection visibility issue that occurred in conflict with WooCommerce 9.1.2

= 2.2.1 =
* Update: Introduced capability to suppress parcel rates once the weight threshold has been reached.
* Update: Compatibility with WordPress version 6.5.3
* Update: Compatibility with PHP version 8.2.0
* Fix:  Incorrect product variants displayed in the order widget.

= 2.2.0 =
* Update: Display “Free Shipping” at checkout when handling fee in the quote settings is -100% .
* Update: Introduced the Shipping Logs feature.
* Update: Introduced “product level markup” and “origin level markup”.

= 2.1.4 =
* Update: Introduced the handling unit feature.
* Update: Updated the description text in the warehouse.

= 2.1.3 =
* Update: Updated URL  in the create plugin webhook. 

= 2.1.2 =
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations.
* Fix: Fixed space character issue in city name.

= 2.1.1 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 2.1.0 =
* Update: Compatibility with WooCommerce 5.6

= 2.0.6 =
* Update: Compatibility with WooCommerce 5.5.

= 2.0.5 =
* Update: Compatibility with WooCommerce 5.4.

= 2.0.4 =
* Update: Include Residential delivery and TailGate delivery in standard plan.

= 2.0.3 =
* Update: Introduced TailGate and option feature.

= 2.0.2 =
* Update: Compatibility with WooCommerce 5.1.

= 2.0.1 =
* Fix: Identify one warehouse and multiple drop ship locations in basic plan.

= 2.0.0 =
* Update: Introduced new features and Basic, Standard and Advanced plans.

= 1.1.0 =
* Update: Compatibility with WooCommerce 4.9.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==


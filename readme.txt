=== Automatic Alternative Text ===
Contributors: JakePT
Tags: image,images,media,gallery,accessibility,a11y,alt,attribute,alt attribute,alt tag,alt text
Requires at least: 4.4
Tested up to: 4.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.0.1

Automatically generate alt text for images with Microsoft's Cognitive Services Computer Vision API.

== Description ==
Automatic Alternative Text makes accessible images easy and fast by automatically generating alt text for images with <a href="https://www.microsoft.com/cognitive-services/en-us/computer-vision-api">Microsoft's Cognitive Services Computer Vision API</a>.

The Computer Vision API’s algorithms analyze the content found in an image and generates complete sentences of human readable language describing what is found in the image. The Automatic Alternative Text plugin gets this description and adds it as the alt text for each image uploaded while the plugin is active.

== Installation ==
The two methods of installing the plugin are:

1. Search for "Automatic Alternative Text" in Plugins > Add New and click the Install button.
2. Unzip the archive and put the `automatic-alt-text` folder into your site's `/wp-content/plugins/` folder.

Then activate Automatic Alt Text from the Plugins menu.

= Usage =
Before the plugin will work you need to supply an API key for Microsoft's Cognitive Services Computer Vision API. You can sign up for a free key <a href="https://www.microsoft.com/cognitive-services/en-us/computer-vision-api">here</a>. Once you've signed up your key will be accessible <a href="https://www.microsoft.com/cognitive-services/en-US/subscriptions">here</a>. Go to the Settings > Media page to enter the key.

Once your key is entered any image you upload from that point forward will automatically have its alt text set to a caption supplied by Microsoft's Cognitive Services.

== Frequently Asked Questions ==

= Why aren't my images receiving alt text? =
There's several reasons an image might not get alt text:
1. Your API key is missing or incorrect. Make sure that your key is saved and accurate in Settings > Media. Also make sure that the key is for the Computer Vision API.
2. The service is not confident enough in the caption. By default the plugin will only use the received caption as alt text if the service is at least 15% confident in the result. You can adjust this confidence threshold in Settings > Media.
3. The service cannot connect to your server or is prevented from accessing the image for any reason. Try entering your image URL on <a href="https://www.microsoft.com/cognitive-services/en-us/computer-vision-api">this page</a> to see if the service can connect outside the context of this plugin.
4. You've exceeded your key's request quota. You can view your usage and upgrade your quota <a href="https://www.microsoft.com/cognitive-services/en-US/subscriptions">here</a>.

= Can I automatically add alt text to images I've already uploaded? =
Not yet. I might add this functionality in a future version.

== Screenshots ==
1. Dismissable admin notice on installation.
2. Plugin settings.
3. An uploaded image with automatic alt text added.

== Changelog ==

= 1.0.1 =
* Fixed typo in settings.

= 1.0 =
* First release.

== Privacy ==
The image, voice, video or text understanding capabilities of Automatic Alternative Text uses Microsoft Cognitive Services. Microsoft will receive the images, audio, video, and other data that you upload (via this app) for service improvement purposes. To report abuse of the Microsoft Cognitive Services to Microsoft, please visit the Microsoft Cognitive Services website at https://www.microsoft.com/cognitive-services, and use the “Report Abuse” link at the bottom of the page to contact Microsoft. For more information about Microsoft privacy policies please see their privacy statement here: https://go.microsoft.com/fwlink/?LinkId=521839.
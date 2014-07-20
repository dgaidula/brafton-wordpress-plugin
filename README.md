Brafton Content Importer
==================

We have the feed why so many settings options. If youc an figure out whether an option should be enabled based on feed, go ahead and make it happen.

Dependencies: 
PHP 5.3 or newer, cURL and DOMXML libraries, and MYSQL 5.0 database or newer. 

Installation

To install this plugin, log into your WordPress administration panel ( generally /wp-admin ) and click the "Plugins" menu item on the left sidebar.  Select the button "Add New" and then "upload" before choosing the zip file downloaded from ContentLEAD's github repository. Then select "Install Now". This should automatically upload and install the plug-in. You may need to activate it manually by visiting the master plug-in page and clicking “Activate” under “Brafton WordPress Plugin”.

Configuration

Navigate to the Brafton settings page by clicking the "Brafton" menu item in the administration panel. Select the appropriate options. Find detailed explanations of all fields in settings page below.

Article Settings

	Product - the company providing the content. ( Brafton, ContentLEAD or Castleford )

	API Key - The base API key for the feed (obtained from your account manager at Brafton, Contentlead, or Castleford).  Please note your API Key is not a full web address, it is a sequence of letters and numbers and dashes.  Again, this field should not contain http://www.brafton.com/ or http://www.contentlead.com/ or http://www.castleford.com.au/.

	Articles - enables or disables article content publishing. Disabled by default.

	For the basic configuration, updating these three options is sufficient. Saving your settings triggers the plugin's first import run. 

	For additional configuration options navigate to advanced and developer tabs on the Brafton settings page. There you can find these additional options.

	Post Author - Select author to attribute imported content from blog's user list.

	Default Post Status - Specifies whether imported content is published or saved as drafts on each successful import.

Advanced Settings

	Dynamic Authorship - Enables or disables default post author setting in Article settings tab. Enable this option to have your editorial team specify individual article authors using our xml feed's byline field. Note: each author in authors list provided to editorial must already be a registered user in your WordPress blog. Further, each authors display name must match the names editorial uses for bylines fields. 


	Images - Enables or disables image downloading. Disable this option if images are not included in your content package.

	Categories - Enablies or disables category importing. By default, each imported article will include categories as defined in the xml feed. Parent child category assications will not be imported, but will be maintained on future imports once set manually.

	Tags - These options set whether your importer will add Tags to posts the same way it can add Categories. By default, no tags are added to imported content. The options and their explanations are below:

		‘Brafton Categories as Tags’ - When solely using tags, select ‘Brafton Categories as Tags.’

		‘Brafton Keywords as Tags’ - If the keywords field is being filled, select ‘Brafton Keywords as Tags.’  

		‘None’ – If tags aren’t desired, select ‘None.’  This is the default.

	Custom Categories - Enter single space delimited list of categories to assign to all imported content.

	Custom Tags - Enter single space delimited list of tags to assign to all imported content.

	Post Date - Content publish date or last modified date or created date. This option will change the date of your imported post.  The options and their explanations are below:

		Publish Date – The Date and Time the article in question is approved and is published to Brafton’s Feed.

		Last Modified Date – The Date and Time the article in question was last modified on Brafton’s Feed.

		Created Date – The Date and Time the article was created and submitted for approval by the client.

Developer Settings

	Custom Article Post Type - Distinguish imported content using a custom post type. Note: if canonical urls are set in permalink settings, this value will be in url structure for all imported content front end views.

	Custom Video Post Type - Distinguish imported videos using a custom post type. Note: if canonical urls are set in permalink settings, this value will be in url structure for all imported content front end views.

	Overwrite - This option updates all posts to be exactly the same as the content on the Brafton Servers every time the plugin runs.  This option is recommended to be turned ‘Off’ if you intend to modify the contents of the posts after they are imported.  If overwrite is set to ‘On’ and you make edit  imported content, these edits will be overwritten the next time the plugin runs.  Please note this applies to adding or removing Categories and Tags for specific posts as well.

	Deactivation - This option determins plugin behavior upon deactivation. Options explained below: 

		"Stop Importing Content" - Simply disables automatic content importing.
		
		"Delete All Brafton Content" - Deletes All imported content. To be used for testing purposes only. Deleting live content is not recommended.

		"Purge this plugin entirely" - Deletes All imported content and disables automatic content importing.

	Errors - Enable brafton error reporting. Brafton plugin specific errors can be found on the Error Log sub menu page.

Video Settings
	
	Videos - enables or disables video content publishing. Disabled by default.

	Private Key - Video feed private key (obtained from your account manager at Brafton, Contentlead, or Castleford). 

	Public key - Video feed feed public key (obtained from your account manager at Brafton, Contentlead, or Castleford). 

	Feed Number - Video feed feed number (obtained from your account manager at Brafton, Contentlead, or Castleford).

	Video Player - Atlantis js is recommended. If enabled, adds atlantis js script to header.php dynamically.

	Import JQuery - Atlantis js video player depends on jQuery. If enabled, adds
	jQuery script to header.php dynamically.

	Player CSS - Include default atlantis js video player css rules. If enabled, adds Atlantis player css to header.php dynimacally. 
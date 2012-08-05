Janrain Engage Extension for Joomla!
=========================================

Installation Notes
-----------------------------------------
This extension is a package of 2 plugins: Engage and Register. Engage provides is the main plugin for authentication. Register is a simple plugin that overrides the default registration form.
Upon installation this will create a table called "janrainengage" to store user identifiers in a one-to-many relationship with the users table. Uninstalltion removes this table, which will permenantly disable authentication for previously registered users. However, the users will not be deleted from the users table. This means that if you are testing, you will want to remove the registered users after uninstalling. In any case, you should ALWAYS backup your database before installing and uninstalling this extension.

Note: These instructions were written for Joomla! 2.5 instructions for previous versions may vary.

Installation Instructions
-----------------------------------------
1.  Backup your Joomla! database.
2.  From the Joomla! Administration panel click Extensions->Extension Manager and make sure you are on the Install tab.
3.  Copy/Paste the following link into the 'Install from URL' textbox: https://github.com/janrain/joomla_plugin_engage/raw/release-alpha/janrain_engage.zip
4.  Click Install.
5.  From the Joomla! Administration panel click Extensions->Plugin Manager
6.  A quick way to find the newest plugins is to sort by ID field. So, click the ID column header twice.
7.  Click the checkbox next to both "User - Janrain Register" and "Content - Janrain Engage" plugins and click 'Enable' at the top.
8.  Click the "Content - Janrain Engage" plugin title to edit the plugin.
9.  Enter your API key at the top making sure "Update Settings" says "Refresh".
10. Click Save then Refresh your browser. You should now see your Auto-Populated settings appear. If not, try closing and reopening this plugin editor page.
11. To enable autogenerated Social-Sign in buttons choose an option from the Social-Sign in: 'Generate Buttons' setting. Choosing 'In-Module' is the simplist setup which places the button in the default Login Form. If you choose 'In-Content', place {janrainengage auth} anywhere you want them to appear, either in article content or a Custom HTML Module. 
12. To enable autogenerated Social Sharing buttons first enable the 'Generate Buttons' from the Social Sharing section. Then place {janrainengage share} anywhere you want them to appear, either in article content or a Custom HTML Module.

Notes: 
*Only one type of each is allowed per page. The buttons may 'appear' in more than one place on a page if you place them so. However, only the first will function.
*For security reasons Janrain's Javascript will not inject into Login and Registration pages. So if you place your buttons in a module, be sure not to place them on these pages.
*If you want to use the buttons in your Template(s) instead, you must place buttons yourself. The code is available under Deployment -> Sign-in for Web -> Get the Code on your Engage Dashboard: https://rpxnow.com/
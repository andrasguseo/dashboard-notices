=== Dashboard Notices ===
Contributors: aguseo
Donate link: https://paypal.me/guseo?country.x=CH&locale.x=en_US
Tags: notices, dashboard, admin
Requires at least: 5.8.6
Tested up to: 6.4.3
Requires PHP: 8.0
Stable tag: 1.2.0
License: GPL version 3 or any later version
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Show admin notices in WordPress on a dedicated page.

== Description ==

The plugin hides admin notices and shows them only on a dedicated page. It also adds a badge to the right side of the admin bar showing how many notices there are.

== Installation ==

Install and activate like any other plugin!

* You can upload the plugin zip file via the *Plugins ‣ Add New* screen
* You can unzip the plugin and then upload to your plugin directory (typically _wp-content/plugins_) via FTP
* Once it has been installed or uploaded, simply visit the main plugin list and activate it

== Frequently Asked Questions ==

= What if there are still notices appearing after I activate the plugin? =

Dashboard Notices tries its best to hide admin notices that were created by using the `admin_notices` hook according to the [official documentation](https://developer.wordpress.org/reference/hooks/admin_notices/). As a result, notices that are not following those guidelines might still show up. A later version might handle those notices. Feel free to let me know about these on the [Issues](https://github.com/andrasguseo/dashboard-notices/issues) section of the repository.

= What if I experience problems? =

I'm always interested in your feedback. The [Issues](https://github.com/andrasguseo/dashboard-notices/issues) section of the repository is the best place to flag any issues. Do note, however, that the degree of support I provide tends to be very limited.

== Changelog ==

= [1.2.0] 2024-02-23 =

* Feature - Ensure the admin notice stays dismissed.
* Feature - Added the `agu_dashboard_notices_dismiss_notice_days` filter to change the number of days the notice should stay dismissed.
* Feature - Make the plugin translatable.

= [1.1.0] 2024-02-22 =

* Tweak - Renamed the plugin to Dashboard Notices to match the name with what it is handling.
* Feature - Added an option to the toolbar to show the notices.
* Feature - Added a dismissible admin notice to show there are notices available. :)
* Tweak - Various tweaks and code enhancements.

= [1.0.0] 2024-02-14 =

* Initial release
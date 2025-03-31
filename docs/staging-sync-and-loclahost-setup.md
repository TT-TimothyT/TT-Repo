# Trek Travel Staging Sync Steps
We have to follow a few steps to sync the staging with production and given the project specifics and the different NetSuite systems, we do not want to use the Pagely sync command directly.

A few of the reasons why we don't wnat to have 1:1 sync:
* NetSuite connection
* Algolia connection
* Cybersource integration

This is why we'll sync the content pieces manually.
===

## Before we start
* Do a full DB backup on staging
* Make sure to have a few defines set up (list down the defines)
  * `DX_DEV` must be set as `true`
  * `BE_MEDIA_FROM_PRODUCTION_URL` must be set `'https://trektravel.com/'`
* Disable temporarily sync cron

## Production DB export or Pagely sync
1) Connect to the server and do a production database export
```
wp db export
```
2) Then ZIP the `.sql` file, move the `.zip` filr to the `~` folder and delete the `.sql` file
```
zip ***.zip ***.sql
```
```
mv ***.zip ~
```
```
rm ***.sql
```

## Staging DB import
1) Connect to the server and do a staging database reset
```
wp db reset --yes
```
2) Upload the archive file to the Staging environment in the WordPress installation folder, unzip it, and then import it
```
wp db import ***.sql
```
3) Once the database has been imported, make sure you’ll update the site URL across all tables
```
wp search-replace "https://trektravel.com" "https://staging.trektravel.com" --allow-root --all-tables --precise;
```

4) Make sure the staging is set as noindex and nofollow:
```
wp option update blog_public 0 --skip-plugins --skip-themes
```

## Staging 3 Sync
Instead of updating with SQL, you might use something like:
```
pagely sync --rename ~/sites/trektravel.com ~/sites/staging3.trektravel.com --ignore-path=.git --ignore-path=wp-content/uploads  --ignore-table=wp_tt_common_error_logs --ignore-table=wp_guest_bookings --ignore-table=wp_netsuite_trips --ignore-table=wp_netsuite_trip_detail --dry-run
```

Once Staging 3 is synced, you can sync S2 and S1 from S3.


5) Make sure the Pagely CDN settings are set properly:
Go to /wp-admin/admin.php?page=press_cdn and set the following options:
Staging 1:
* CDN URL - http://s43691.pcdn.co
* HTTPS CDN URL	- https://s43691.pcdn.co

Staging 2:
* CDN URL - http://s44378.pcdn.co
* HTTPS CDN URL	- https://s44378.pcdn.co

Staging 3:
* CDN URL - http://s45155.pcdn.co
* HTTPS CDN URL	- https://s45155.pcdn.co

**Check the values in case of staging updates**

## Prepare a clean staging setup
* Checkout to the master branch and pull the latest changes
* Deactivate the active production plugins.

```
wp plugin deactivate dxsf-wordpress-proxy mailgun microsoft-clarity wps-hide-login wp-2fa
```
⚠️ Important Note: The Email plugins might send emails to real users, so it’s **very important they will be deactivated/removed if needed**.

* Activate the staging plugins.
```
wp plugin activate be-media-from-production dx-localhost password-protected query-monitor
```

* Delete all users, but keep TT and DX administrators. For `--reassign` use the ID from any of those account
```
wp user delete $(wp user list --role=customer --field=ID) --quiet --reassign=14327 # where the ID is just an example, you have to update it with the new user you'll create
```

Repeat the step above for other user roles as well like subscriber, editor, author, etc.
* Clean all WooCommerce sensitive data including orders
 * Add this file as a MU Plugin [WC Clear Sensitive Data](https://github.com/DevriX/devrix-cli/blob/main/wc-clear-sensitive-data.php)
 * Then use the following WP-CLI command
 ```
 wp clear wc_data --url=https://staging.trektravel.com
 ```
* Delete all the data from the custom DB tables. You can use phpMyAdmin.
  * Delete all Error Logs from `tt_common_error_logs`
  * Delete all Bookings from `guest_bookings`
  * Delete all Trips from `netsuite_trips`
  * Delete all Trip Details from `netsuite_trip_detail`
  * Delete all Trip Bikes from `netsuite_trip_bikes`
  * Delete all Trip Hotels from `netsuite_trip_hotels`
  * Delete all Trip AddOns from `netsuite_trip_addons`

* Delete All Simple Products While Keeping Grouped and Line Item Products

To list all grouped products before deletion, you can use the following command:
```
wp ttnsw product list --product_type=grouped
```
To list all Line Items products before deletion, you can use the following command:
```
wp ttnsw product list --line-item
```
Run the following command to delete all simple products while preserving grouped and Line Item products:

```
wp post delete $(wp post list --post_status='publish' --post_type='product' --format=ids --post__not_in=78972,73798,73795,116066,115747,115746,114464,114446,114443,108664,107458,107389,103337,104574,103360,103353,103350,102872,102610,102354,101819,98996,97732,90498,90492,90490,90038,86572,86533,86372,86328,86296,86077,86074,86063,86062,86060,84633,84623,84638,84616,84305,83863,83793,83735,83665,81034,80392,80376,80374,79911,74455,71297,70795,69553,69314,69018,68871,68495,68524,68505,68271,68244,67946,67916,67740,67592,67559,67477,67464,67388,67272,67250,67249,67022,66283,1556,1557,1558,1491,1486,1488,1489,1490,1482,1485,1477,1478,1480,1481,1476,1468,1471,1472,1465,1466,1467,1136,1140,1132,1134,1135,1127,1128,1129,1130,1131,1122,1123,1124,1125,1126,1117,1121) --force
```
This command removes all published products except for grouped and Line Item products, identified by their IDs in the `--post__not_in` parameter.

## Dashbord Settings preparation
Once we have a clean database we can now make the specific staging settings

### NetSuite Integration for WooCommerce Plugin [API Credentials](https://app.asana.com/0/1205472772784381/1206055348045389/f)
We should **NOT** use the production settings for `TM Woocommerce NetSuite Integration` as this will override staging <> production data and it's dangerous
* Navigate to the plugin's dashboard, from there select the **Settings -> General Settings tab**
* Add the Credentials, and save your changes
* After saving the changes, test if everything is ok with the **Test API Credentials** button

### Cybersource [API Credentials](https://app.asana.com/0/1205472772784381/1208131302522106/f)
* Go to  `wp-admin/admin.php?page=wc-settings&tab=checkout&section=cybersource_credit_card`
* Add the Credentials, and save your changes

### Algolia
Open `wp-admin/admin.php?page=algolia-account-settings`
and set the proper Algolia API key details

You can get these from [Trek Travel Algolia Details](https://app.asana.com/0/1205472772784381/1206349252116376)

### NetSuite Sync
* Go to `wp-admin/admin.php?page=trek-travel-ns-wc`
* Select items from `Manual Sync for WC<>NS`
* First sync the **Misc: Custom Items/Lists**
* After that run the Steps syncs from 1 to 3
* Go for a coffee and monitor the sync process, as this will take some time

### Password Protected
Password protect your web site. Users will be asked to enter a password to view the site.
* Go to `wp-admin/options-general.php?page=password-protected`
* Toggle the **Password Protected Status** button
* For **Protected Permissions** choose:
  -  [x]  Allow Administrators
  -  [x]  Allow Logged In Users
* New Password: `dxttstaging!`
* Toggle the **Allow Remember me** button
* Click the **Save Changes**

## Server Cron Enabling
Once the whole sync process is ready, **Enable** sync cron

## Agolia sync with the new content
* Once the sync is ready, go to Algolia and hit re-sync
* Go to `wp-admin/admin.php?page=algolia` and hit Re-Sync for All posts
* Do **NOT** hit push settings

## Staging 2, 3, etc
* Once all of the steps from above are ready, you can export the staging database and import it to other stagings.
* Go to the staging directory, export staging.trektravel.com
* Reset the stagingX database
* Import the staging database
* run search-replace with `wp search-replace "https://staging.trektravel.com" "https://staging2.trektravel.com"`
* Connect the proper Algolia settings from [Trek Travel Algolia Details](https://app.asana.com/0/1205472772784381/1206349252116376)
* [Re-Sync Algolia](#agolia-sync-with-the-new-content)
* repeat the same for Staging 3, 4, etc
* Also, check the Pagely CDN settings mentioned a few steps above

## Localhost
* You can prepare a new fresh localhost setup from the newly created staging database
* Download the staging DB
* Import it locally
* run search-replace with `wp search-replace "https://staging.trektravel.com" "https://local.trektravel.com"`
* Delete WordPress users
* Make sure we have one admin user
* Run the NS Sync once again, just in case
* Connect the proper Algolia settings from [Trek Travel Algolia Details](https://app.asana.com/0/1205472772784381/1206349252116376)
* [Re-Sync Algolia](#agolia-sync-with-the-new-content)
* Export the new local database and import it to [Trek Travel Project Setup, Git Details, Database dump and Media export](https://app.asana.com/0/1205472772784381/1205472808111993/f) Drive folder

Enjoy the new synced environments!

# DX ACF Synchronize
This plugin force Advanced Custom Fields to load all groups of fields from JSON files which are located in a certain folder.

## FAQ
Questions that will come up before or while using the plugin:

### How DX ACF Sync plugin actually works?
The plugin is using the ACF feature added in version 5 - [Local JSON](https://www.advancedcustomfields.com/resources/local-json/). This feature is saving all field groups and settings as separate .json files. The whole idea behind this is to dramatically speeds up the ACF and allows you to use version control.

### Why we need this plugin?
Probably, if you have read the official ACF documentation guide about the Local JSON feature, you are going to ask - Why we actually need this plugin when we can just create "_acf-json_" folder in the theme and everything will work without any additional plugins?

One of the main reason is when a certain website is using two themes - one for desktop and one for mobile devices, for example. In a result of this, you will need to add create the "_acf-json_" folder in both themes which will make the process a little longer and uncomfortable because you are going to have the same JSON files in two places. Also, what about if you forget to upate one of the groups in both themes? You will break the website.

The idea of this plugin is to create custom folder in `wp-content/` that can be used by many themes and provide you the environment where you can easily extend with custom functionalities, messages and features.

### How to install and start using the plugin?
0. Make sure to remove all existing ACF Sync plugins, ACF generators etc. in the project.

1. Install the plugin

2. Export all field groups using the ACF exporter from production - `/wp-admin/edit.php?post_type=acf-field-group&page=acf-tools`

3. Remove all currently existing groups from your local installtion

4. Create folder in `acf-exports` in `wp-content` and make sure the rights are at least 775

5. Activate the plugin

6. Import the exported field from production

7. Now the folder `wp-content/acf-exports` should have .json file for each group.

8. Commit your changes

Now in order to deploy and start using the plugin on production, read the next section - [How to deploy the plugin on production](#how-to-deploy-the-plugin-on-production)

### How to deploy the plugin on production?
Steps which you should follow strictly when deploying this plugin:

**!!! VERY IMPORTANT:** Pick a suitable time for deploying the plugin! It depends on the project and when there are visitors. The reason to do this is because **there will be a minute or two when ACF groups will be missing**. This can break some parts of the website - ACF fields, forms etc. won't show up.

0. Make a backup of all field groups. Just like #2 in [How to install and start using the plugin](#how-to-install-and-start-using-the-plugin). In case something went wrong so you can easily import them back!
1. Install the plugin on production.
2. Deploy the commited folder - `wp-content/acf-exports` with all .json files. 
3. Remove all currently existing groups on production from Custom Fields. Make sure to clean them from trash too. (_This is the step for which the important note is valid_)
4. Activate the plugin.
5. Test the pages that use ACF.

### How to sync the ACF groups?
The synchronizing process is easy but it requires a few steps to be completed so everyone can receive latest versions and edit all of the groups.

1. Make sure you do not have any groups in the database. Which means that you should see the message `No Field Groups found` in **Dashboard -> Custom Fields** (`/wp-admin/edit.php?post_type=acf-field-group`).
2. If you have any groups, please remove them even from the trash.
3. Pull latest changes from the repository - `git pull`.
4. Now you have the latest ACF groups and they are fully synced with the repository.

What if I had already pulled the latest changes from the repository but I forgot to clear all my previously edited groups? The solution for this problem is easy:

1. Go to **Custom Fields** page and remove your locally synced groups.
2. Run `git status` and you will that you have removed some of the ACF groups which is a problem that we are trying to figure out how to fix but it is in the [ToDo List](#todo).
3. Checkout the whole folder - `git checkout wp-content/acf-exports` in order to revert the changes.
4. Just to make sure that you have latest groups - `git pull` again.

### How to edit an ACF group?
There are two options for editing an ACF group. One is to edit the JSON files directly and the other one is to use everyones favorite interface in ACF "Custom Fields" dashboard page.

Steps to follow when editing ACF group:
1. Make sure you have fully synced groups. In order to do this, read the [How to sync the ACF groups properly](#how-to-sync-the-acf-groups-properly) section carefully!

2. Go to **Dashboard -> Custom Fields** (`/wp-admin/edit.php?post_type=acf-field-group`).

3. Open the tab **Sync Available(N)** (`/wp-admin/edit.php?post_type=acf-field-group&post_status=sync`).

4. Find the group which you want to edit and click on **Sync** button.

5. You will be redirect to the first tab on this page - **All** and you will see the desired group available for edit.

6. Apply your desired changes and update the group.

7. After that in order to check your changes if they are applied, go to your terminal or whatever Git GUI you are using and execute `git status`. If everything is okay with your change you should see the updated .json file of the group which you have selected.

8. Commit the updated .json file to the repository.

### What if two people are changing the same group?
This is no longer a problem if both are following the steps from the [Sync](#how-to-sync-the-acf-groups) and [Edit](#how-to-edit-an-acf-group) section, and commit their changes to the repository.

## ToDo
List with ideas and features for future version that will make the plugin even better and easier for use:

- Create folder acf-exports/ in wp-content/
    - Check for existance firstly
    - The folder should have at least 755 rights
- Check if ACF is activated before activate this plugin and show notice if not
- Check ACF version, it should be above 5.0
- Add notification on wp-admin/edit.php?post_type=acf-field-group when this plugin is activated
- Hide "Custom Fields" page from dashboard on production in order to avoid direct changes without commit and deploy after that. The page should be available only for dev environments.
- Make sure not to remove the .json file when someone removes a group from their localhost so the only way for removing group is to remove the file.
# Trek Travel site

## Notes
This is the Git repository for the website https://new.trektravel.com/. The repository contains wp-content/* files and a few root files and folders.

The project itself is a single WordPress installation with WooCommerce, ACFs for the dynamic data, Elementor for the landing pages, Netsuite for the ERP integration and sync NetSuite <> WooCommerce, Trek Travel Algolia integration and a bunch of plugins.

Below you'll find important notes about the project, the git branching, and the like.

#### Documentation
TBD, at some point.

#### Localhost Project URL
Make sure to update your hosts/server config to match the custom URL we use for the project - `local.trektravel.com`. For information on how to do it [read our article](https://devrixverse.com/knowledgebase/setup-a-custom-localhost-url/).

#### Dashboard/Admin access
To access the login page go to [http://local.trektravel.com//wp-login.php/](http://localrebuild.enviroklenz.com//wp-login.php).
For localhost Dashboard access, you can use `admin/admin` for `username/password`. Don't judge, this is for your localhost :)
If you have WP-CLI, you can use [wp user create](https://wp-cli.org/commands/user/create/) or simply add a new user from your phpMyAdmin and/or your local MySQL and use another set of credentials.

#### Recommended plugins and plugins notes
* BE Media From Production
Most likely you'll have [BE Media from Production](https://github.com/billerickson/BE-Media-from-Production) plugin installed on your localhost. The plugin allows you to use images from the Production site without the need to download the latest media.
We might still have some missing images. You might need to update the case per case as some of the images are added as options for different plugins and the theme.

#### More details

* [Git: DevriX Git Branching Model/Git Flow](https://devrixverse.com/knowledgebase/git-devrix-git-branching-model-git-flow/)
* [WordPress localhost debugging](https://devrixverse.com/knowledgebase/wordpress-localhost-debugging/).
* [BE Media from Production](https://devrixverse.com/knowledgebase/be-media-from-production/)
* [A typical Git structure](https://devrixverse.com/knowledgebase/a-typical-git-structure)

---

## Setup Details
The Git repository contains a few root files and the `wp-content` directory content, so we are going to setup the project following the steps below.

The steps below are valid for a simple project. If your project requires additiona steps, such as having a custom Apache/hosts setup, make sure you'll add them.

### 0) Prepare the server
You'll need to update your `/etc/hosts` file with:
`127.0.0.1	local.trektravel.com # This for the Rebuild site`

and have your Apache conf file with something like:

```
<VirtualHost *:80>
        DocumentRoot /var/www/html/trektravel/
        ServerName local.trektravel.com

        <Directory /var/www/html/trektravel/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>
</VirtualHost>
```

### 1) Clone the repository
Navigate to your local web server folder - `www/html`, `htdocs`, etc, based on your OS. Make sure the directory is writable. Again, this is based on your localhost setup and personal preferences.

Clone the Git repository with the following command: `git clone git@github.com:DevriX/Trek-Travel.git trektravel`.

### 2) Download WordPress
Navigate to the repository folder that Git just created. 

Run `wp core download --skip-content --force`. You can add `--version=XXXX` if the project requires a specific version of WordPress Core.

The final goal is to have all Git repository files in your root directory, which includes `.git`, `.gitignore`, `wp-content`, and everything else, based on the project needs.

Make sure the directory is writable. Again, this is based on your localhost setup and personal preferences.

### 3) Create a wp-config.php file
In the repository, you'll find `wp-config-local-sample.php`. Duplicate the file and name it `wp-config.php`. Enter your localhost the database user and password and the database name inside the file. You'll see the `define` variables.     

### 4) Create and import the database
**Standard method:**

Create an empty database on your localhost database server and set `utf8mb4_general_ci` for the DB collation. Download the database from [Trek Travel Project Setup, Git Details, Database dump and Media export](https://app.asana.com/0/1205472772784381/1205472808111993/f) and import the downloaded database into the newly created database. This depends on your localhost setup.

**WP-CLI method:**

Run `wp db create` to create a new database. Then run `wp db import <sql-path>` and that should be it!

### 4) Access the site
Once the database is imported you'll have [http://local.trektravel.com/](http://local.trektravel.com/) ready to use.

### 5) Reset permalinks

Go do to the `Dashboard > Settings > Permalinks` and click on the Save button. In that way, you'll generate a new `.htaccess` file, so you'll have working pretty permalinks.

Make sure the directory is writable, so WordPress can generate the new file.

### 6) Regenerate the Elementor Styles and sync the Media
There is a chance the Elementor styles to be missing. To fix that navigate to the site dashboard and from the Admin menu select Elementor -> [Tools](http://local.trektravel.com/wp-admin/admin.php?page=elementor-tools). **Regenerate CSS & Data** and **Sync Library**.

### 7) Import the Uploads folder
If such is not provided, you can use **BE Media From Production** plugin.

The original setting for the media is to NOT order files in year/month folder, yikes.

As stated above, We strongly suggest you to enable the `WP_DEBUG` on your localhost. You can check **Localhost Debugging** section for more details.

If you have any questions regarding the setup, feel free to ask in project's Slack channel!

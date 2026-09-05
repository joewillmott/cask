---
id: installing-cask
title: Installing cask
parent: getting-started
order: 1
excerpt: Upload cask to your web host and get to first page load in five minutes.
tags:
  - installation
  - setup
---

# Installing cask

## What you need

- A web host running PHP 7.4 or later — most shared hosts qualify, including Hostinger, SiteGround, DreamHost, and Namecheap
- Access to your host's file manager (usually via cPanel) or an FTP client
- The cask zip file from [cask.ink](https://cask.ink) or [GitHub](https://github.com/joewillmott/cask)

If your host offers a "cPanel" control panel, you almost certainly have everything you need.

## Step 1 — Download the zip

Download the latest cask release as a zip file from [cask.ink](https://cask.ink) or the [GitHub releases page](https://github.com/joewillmott/cask/releases).

## Step 2 — Upload and extract

1. Log into your host's file manager (in cPanel, it's called **File Manager**)
2. Navigate to the folder where you want your site to live:
   - For your root domain: `public_html/`
   - For a subfolder like `yourdomain.com/docs/`: create a `docs` folder inside `public_html/` first
3. Upload the zip file into that folder
4. Right-click the zip and choose **Extract**

After extracting you should see these files:

```
index.php
app.js
style.css
content-list.php
mcp.php
content/
  welcome.md
  features.md
  getting-started.md
  (and the rest of this documentation)
```

## Step 3 — Visit your site

Open your site URL in a browser. You should see cask load with this documentation as its default content.

If you see a blank page or a PHP error, check that your host is running PHP 7.4 or later. Most file manager UIs show the PHP version in their settings. See [[faqs]] for other common problems.

## Step 4 — Replace the example content

Once cask is working, delete the example `.md` files from the `/content/` folder and add your own. There's nothing else to configure — cask reads whatever is in that folder automatically.

See [[content-management]] for how to write and structure your files.

## Step 5 — Set your site name and logo

Open `index.php` in a text editor (or use your file manager's built-in editor). Near the top you'll see:

```php
$site_name = 'cask.ink';
$site_logo = '';            // URL to a logo image, or leave empty
$nav_links = [
    ['label' => 'GitHub', 'url' => 'https://github.com/joewillmott/cask'],
];
```

Change `$site_name` to your site's name. Paste an image URL into `$site_logo` if you have a logo. Update `$nav_links` with whatever links you want in the top navbar, or set it to an empty array `[]` to show none.

Save the file and reload your site. Full details on these settings are in [[navbar]].

## Step 6 — Style your site

Visit `yoursite.com/?admin` to open the design panel. From here you can change colours, fonts, layout, and display preferences with live preview. When you're happy, click **Export CSS** and upload the downloaded `style.css` to replace the original.

Full details in [[brand-styling]].

---
id: navbar
title: Managing your navbar
parent: getting-started.md
order: 4
excerpt: How to add, remove and edit the links in the top navigation bar.
tags:
  - setup
  - navigation
  - configuration
---

# Managing your navbar

The top navigation bar displays your site title on the left and a set of links on the right. These links are configured directly in `index.php` — no admin panel required, no database, just a straightforward array at the top of the file.

## Editing navbar links

Open `index.php` in any text editor and find the `$nav_links` block near the top:

```php
$nav_links = [
    ['label' => 'GitHub', 'url' => 'https://github.com/yourname/repo'],
];
```

Each link is an array with two keys: `label` (the text displayed) and `url` (where it goes). To add links, add more entries. To remove a link, delete its line. To reorder links, move the lines.

## Examples

**Single link:**
```php
$nav_links = [
    ['label' => 'GitHub', 'url' => 'https://github.com/yourname/repo'],
];
```

**Multiple links:**
```php
$nav_links = [
    ['label' => 'Home',      'url' => 'https://yoursite.com'],
    ['label' => 'GitHub',    'url' => 'https://github.com/yourname/repo'],
    ['label' => 'Changelog', 'url' => 'https://yoursite.com/changelog'],
];
```

**No links (hide the navbar link area entirely):**
```php
$nav_links = [];
```

## Changing the site title

The site title shown in the top-left is set on the line directly above `$nav_links`:

```php
$site_name = 'cask.ink';
```

Change the value to whatever you want your site to be called. It also sets the browser tab title.

## Adding a logo

To show a logo image instead of (or alongside) the site title text, set the `$site_logo` variable to the URL of your image:

```php
$site_logo = 'https://yoursite.com/logo.png';
```

Leave it as an empty string `''` to show only the text title.

## After editing

Save `index.php` and upload it to your server, replacing the existing file. The change is live immediately on the next page load. No build step, no cache to clear on PHP hosting.

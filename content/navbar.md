---
id: navbar
title: Site identity and navbar
parent: getting-started
order: 4
excerpt: Set your site title, logo, and top navigation links by editing index.php.
tags:
  - setup
  - navigation
  - configuration
---

# Site identity and navbar

The site title, logo, and navbar links are set by editing three variables near the top of `index.php`. These are the only things that require a file edit rather than the admin panel — because they're PHP values used to generate the page itself, not CSS properties.

## Opening index.php

Open `index.php` in your file manager's built-in text editor, or download it, edit it locally, and re-upload. The variables you need are right at the top of the file:

```php
$site_name = 'cask.ink';
$site_logo = '';
$nav_links = [
    ['label' => 'GitHub', 'url' => 'https://github.com/joewillmott/cask'],
];
```

## Setting the site title

Change the value of `$site_name` to whatever you want to appear in the navbar and in the browser tab:

```php
$site_name = 'My Documentation';
```

## Adding a logo

Set `$site_logo` to the full URL of your logo image. The image appears to the left of the site title in the navbar:

```php
$site_logo = 'https://yoursite.com/logo.png';
```

Leave it as an empty string `''` to show no logo — just the text title.

For best results, use an image that is already sized for the navbar (around 32px tall). SVG files work well and stay sharp at any size.

## Setting navbar links

`$nav_links` is an array of links that appear in the top-right corner of the navbar. Each entry needs a `label` and a `url`:

```php
$nav_links = [
    ['label' => 'GitHub',  'url' => 'https://github.com/yourusername/yourrepo'],
    ['label' => 'Website', 'url' => 'https://yoursite.com'],
];
```

To show no navbar links at all, set it to an empty array:

```php
$nav_links = [];
```

## Saving and uploading

After editing, save the file and upload it back to your server in the same location, overwriting the original. Reload your site and the changes will be live.

## Field mapping (advanced)

Also in `index.php`, below the site identity settings, is a `$field_map` array:

```php
$field_map = [
    'identifier' => ['id', 'slug', 'key'],
    'title'      => ['title'],
    'excerpt'    => ['excerpt', 'description'],
    'tags'       => ['tags'],
    'thumbnail'  => ['hero_image', 'image'],
    'parent'     => ['parent'],
    'order'      => ['order'],
];
```

This tells cask which frontmatter field names to look for in your markdown files. The values are priority-ordered lists — if a file has `slug` but not `id`, cask uses `slug` as the identifier.

You only need to touch this if your existing markdown files use different field names than cask's defaults. For example, if your files use `description` instead of `excerpt`, it's already covered by the default map. If they use something else entirely — say `summary` — add it to the list:

```php
'excerpt' => ['excerpt', 'description', 'summary'],
```

This can also be adjusted temporarily from the admin panel's Data Mapping tab without editing any files, but those changes only last for your session. To make them permanent, edit `index.php`.

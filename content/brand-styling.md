---
id: brand-styling
title: Brand styling
parent: getting-started.md
order: 2
excerpt: How to change colours, fonts and layout to match your brand, using the admin panel or by editing style.css directly.
tags:
  - styling
  - design
  - css
---

# Brand styling

cask.ink gives you two ways to style your site. The admin panel lets you design visually in the browser and export a CSS file. Direct file editing lets you change values in `style.css` without opening the browser at all. Both methods produce the same result — a `style.css` file on your server that controls how your site looks.

## Using the admin panel

Click **cask v0.0.1** in the bottom-left corner of the sidebar to open the admin panel.

The Design tab contains controls for:

- **Site Identity** — site title and logo URL
- **Colours** — ten named colour variables covering backgrounds, text, accents, borders, code blocks, hover states and tags
- **Fonts and Stylesheets** — Google Fonts import URLs and font-stack fields for headings, body text and UI elements
- **Text Styles and Sizes** — base font size, line height, and heading size/weight for H1, H2 and H3
- **Layout and Sizing** — sidebar width, content max-width and border radius values
- **Tags** — background, text, border colour and corner radius for tag badges
- **Display Preferences** — toggles for the page title, breadcrumb trail and tag badges

Changes preview instantly on the page behind the modal. When you are happy with the result, click **Export CSS**. This downloads a `style.css` file containing your choices. Upload it to your server, replacing the existing `style.css`, and the changes are permanent.

The admin panel does not save anything between sessions. It is a design tool, not a settings store. Your `style.css` file is the only source of truth.

## Editing style.css directly

Open `style.css` in any text editor. The `:root` block at the top of the file contains all the variables the admin panel controls:

```css
:root {
    --color-bg:          #fafafa;
    --color-text:        #000000;
    --color-accent:      #5C7A6B;
    --color-sidebar-bg:  #ffffff;
    --color-navbar-bg:   #ffffff;
    /* ... */
}
```

Change any value, save the file, and upload it to your server. The site reflects the change on the next page load.

If your host is serving a cached version of `style.css`, you may need to purge the cache from your hosting control panel or Cloudflare dashboard before the change is visible.

## Using custom fonts

To use Google Fonts, go to [fonts.google.com](https://fonts.google.com), select your fonts, and copy the stylesheet URL from the import code Google provides. It looks like:

```
https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap
```

In the admin panel, paste this URL into the **Google Fonts / CSS Import URLs** field (one URL per line). Then set the font name in the relevant font-stack field:

```
'Inter', system-ui, sans-serif
```

When you export and upload the CSS, the import and the font-family declaration are both included in the file.

To add fonts directly in `style.css`, add an `@import` line at the very top of the file, before the `:root` block:

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
```

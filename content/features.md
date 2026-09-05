---
id: features
title: Features
order: 2
hero_image: https://www.paramountshop.com/cdn/shop/files/PMT_CollectionBanners_TopGun-mobile.jpg
excerpt: A full list of what cask does out of the box — and what it deliberately doesn't do.
tags:
  -  
---

# Features

## It reads your files directly

cask reads `.md` and `.txt` files from the `/content/` folder on every page load. There's no import step, no sync, no cache to bust. Add a file, refresh, and it appears. Delete a file, refresh, and it's gone.

## It builds structure from frontmatter

The sidebar hierarchy, page ordering, and titles all come from frontmatter fields inside your files — not from folder names or file paths. A `parent:` field makes a page a child of another. An `order:` field sets its position. You can reorganise your entire site by editing a few lines of text.

## It renders markdown beautifully

Standard markdown — headings, bold, italic, lists, tables, code blocks, blockquotes, images, links — all rendered cleanly. Code blocks get automatic copy-to-clipboard buttons.

## It links pages together

Use `[[page-id]]` wiki-style links to connect pages. They resolve automatically and show a visual indicator if the target doesn't exist. You can also use a pipe for custom link text: `[[page-id|read more here]]`.

## It searches everything

The search box in the sidebar searches page titles, body content, and tags across all your documents simultaneously. No index to build, no server-side search engine required.

## It supports hero images

Add a `thumbnail:` field to any page's frontmatter with an image URL, and it displays as a full-width hero image above the content. Corner radius is adjustable in the admin panel.

## It has a live admin panel

Visit your site with `?admin` in the URL to open the design panel. Adjust colours, fonts, layout, and display preferences — all changes apply instantly as you type. When you're happy, export your settings as a new `style.css` and upload it to make them permanent.

## It connects to AI tools

cask includes a built-in MCP (Model Context Protocol) server at `/mcp.php`. Point Claude, Cursor, or any MCP-compatible AI tool at that URL and it can read, search, and reason over all your documentation. No configuration needed — it works as soon as cask is installed.

## It is genuinely self-contained

The entire product is five files: `index.php`, `app.js`, `style.css`, `content-list.php`, and `mcp.php`. No framework. No package manager. No build pipeline. No external service. Everything runs on standard shared web hosting.

---

## What cask deliberately doesn't do

**No login or access control.** Everything in your `/content/` folder is publicly visible. If you need private documentation, cask isn't the right tool yet.

**No image hosting.** Link to images hosted on your own server or a service like Cloudinary. cask doesn't manage media files.

**No static export.** cask requires PHP to serve content. It won't run on GitHub Pages, Netlify, or S3 without a PHP-capable adapter. (A static fallback via `content/index.json` exists for limited use cases.)

**No comments or interactivity.** cask is a reading experience, not a collaboration platform.

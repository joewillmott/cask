---
id: getting-started
title: Getting Started
order: 2
excerpt: Everything you need to get a cask.ink site running, from download to first page load.
tags:
  - setup
  - installation
---

# Getting Started

cask.ink is designed to be installed in under five minutes on any standard web hosting. This section covers what you need, where to get cask.ink, and which guides to follow in order.

## What you need

- **Web hosting with PHP 7.4 or above.** This covers virtually every shared hosting plan sold today — cPanel, Hostinger, SiteGround, DreamHost, and equivalents all qualify. If you're unsure whether your host runs PHP, log into your control panel and look for a PHP version selector, or ask support.
- **FTP access or a web-based file manager.** Most shared hosts provide both. You'll use one of these to upload cask.ink and your content files.
- **A folder of markdown files.** Or just the example file that ships with cask.ink — you can add your own content afterwards.

If you want to host on a static platform (S3, GitHub Pages, Netlify, Cloudflare Pages), cask.ink will still work, but automatic content discovery requires PHP. See the Installing cask guide for details on the static host fallback.

## Download

Download the latest version of cask.ink as a zip file:

**[Download cask.ink →](https://cask.ink/download)**

The zip contains everything: `index.php`, `app.js`, `style.css`, `content-list.php`, `mcp.php`, and a `/content/` folder with these documentation files inside it.

## Where to go next

Follow these guides in order for a first install:

1. **[[installing-cask|Installing cask]]** — upload the zip, extract it, verify it works
2. **[[brand-styling|Brand styling]]** — adjust colours, fonts and layout to match your brand
3. **[[content-management|Content management]]** — add your own markdown files and structure them correctly

Once your site is running, read **[[cask-ai|Cask AI]]** to understand how to expose your content to AI tools via the MCP endpoint.

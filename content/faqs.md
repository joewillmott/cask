---
id: faqs
title: FAQs
parent: welcome.md
order: 2
excerpt: Honest answers to the questions people actually ask.
tags:
  - faq
  - help
---

# FAQs

## Do I need a server to run cask.ink?

You need somewhere to host files that is reachable over HTTP. Shared hosting (cPanel, Hostinger, SiteGround and equivalents) works perfectly and gives you the full feature set including automatic content discovery. Static hosts (S3, GitHub Pages, Netlify) also work, but require you to maintain a `content/index.json` manifest manually and do not support the MCP endpoint.

## Do I need to know how to code?

No. Installing cask.ink is unzipping a file and uploading the contents to your hosting via FTP or a file manager. Styling your site is editing a CSS file or using the built-in admin panel. Adding content is dropping markdown files into a folder.

## What markdown features does cask.ink support?

Anything marked.js supports, which covers the full CommonMark specification: headings, bold, italic, blockquotes, ordered and unordered lists, code blocks with syntax hints, tables, horizontal rules, images and standard links. cask.ink adds `[[wiki-link]]` syntax on top of that for internal navigation.

## Can I use cask.ink for a blog?

Yes. There is nothing documentation-specific about the platform. It reads markdown files and presents them. If you want to call them blog posts, they are blog posts. Hierarchy, tags and ordering work exactly the same way.

## Can I password-protect my site?

Not natively in this version. cask.ink is designed for public-facing content. If you need access control, the simplest approach is to put your hosting's built-in HTTP authentication (`.htpasswd` on Apache) in front of the directory.

## What happens if I delete a file?

It disappears from the sidebar on the next page load. Any internal links pointing to it will render as greyed-out unresolved wiki links rather than broken anchors.

## Will cask.ink work on Windows hosting?

Yes. The PHP is standard and has no Unix-specific dependencies. The only requirement is PHP 7.4 or above.

## How is this different from Obsidian Publish?

Obsidian Publish is tightly coupled to the Obsidian app ecosystem. Your content is hosted on Obsidian's infrastructure, priced per site per month, and the publishing experience is designed around Obsidian's own vault structure. cask.ink is self-hosted, works with any markdown files regardless of what tool created them, costs nothing beyond your existing hosting, and ships with an MCP endpoint Obsidian Publish does not have.

## How is this different from GitBook?

GitBook starts at $65 per site per month and adds per-seat charges on top. It requires content to live inside GitBook's own platform. cask.ink is self-hosted, has no per-seat pricing, and your content stays in plain files you own entirely.

## Can I contribute to cask.ink?

cask.ink is open source. If you have found a bug or want to propose a feature, raise an issue or open a pull request.

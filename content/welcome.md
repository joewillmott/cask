---
id: welcome
title: Welcome to cask.ink
order: 1
excerpt: cask.ink is a documentation platform built on a single, radical idea — your files should outlive any tool you use to view them.
tags:
  - introduction
---

# Welcome to cask.ink

Most documentation platforms want your content. They want you to write inside their editor, store your files in their database, and depend on their infrastructure to keep it accessible. The moment you stop paying, or the moment they shut down, your work is trapped inside a system you never really owned.

cask.ink is built on a different idea entirely.

You write wherever you write. Plain `.md` files, in a folder, on your hard drive or your hosting or your Dropbox. You've probably got a folder like this already. cask.ink simply reads it, structures it, and presents it — to humans in a browser, and to AI tools via a built-in MCP endpoint. When you stop using cask.ink, your files are still exactly where you left them, in a format every text editor on earth can open.

This philosophy has a name: **file over app**, coined by Steph Ango. The file is the asset. The app is just a lens.

## What cask.ink actually is

A zip file you unzip onto your web hosting. That is the entire install process.

Inside that zip is an `index.php` (or `index.html` on static hosts), a `style.css`, a handful of PHP files that do the work, and a `/content/` folder where your markdown files live. Point a browser at the directory, and you have a documentation site. No npm. No build step. No database. No cloud dependency.

You write a markdown file, drop it in `/content/`, and it appears in the sidebar the next time anyone loads the page. That is the entire content management workflow.

## Who it's for

- Developers who want to document a project without configuring a documentation framework
- Founders who want a product knowledge base without paying per-seat SaaS pricing
- Writers who want to publish structured long-form content from plain text files
- Anyone who has a folder of markdown files and wants the world to be able to read them

## The name

A cask holds raw material — wine, whiskey, ink — unprocessed, in its original form, ready to be poured. Your markdown files are the raw material. cask.ink holds them and pours them out, directly, without processing them into something you no longer own.

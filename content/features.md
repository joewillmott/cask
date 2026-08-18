---
id: features
title: Features
parent: welcome.md
order: 1
excerpt: Everything cask.ink does, and why each decision was made the way it was.
tags:
  - features
  - overview
---

# Features

## Zero-config content management

Drop a `.md` file into `/content/`. It appears in the sidebar. Delete it. It disappears. No CMS, no upload interface, no sync daemon, no cache to clear. The page list is generated fresh on every load from whatever is actually in the folder.

## Hierarchy from frontmatter, not folders

Structure is declared inside your files, not inferred from where they happen to sit on disk. A `parent:` field in the YAML frontmatter of any file places it beneath another page in the sidebar. This means your filing system and your published structure are two completely independent things — you can reorganise the published site by editing a single field, without moving any files.

```yaml
---
id: installing-cask
title: Installing cask
parent: getting-started.md
order: 1
---
```

## Flexible frontmatter mapping

Different tools write frontmatter differently. Jekyll uses `slug`. Notion exports use `id`. Hand-written files might use anything. cask.ink lets you tell it which frontmatter keys to look for, in priority order, via the admin panel's Data Mapping tab. A site migrating from an old convention to a new one can list both — cask.ink checks each file individually and uses the first key it finds.

## Built-in admin panel

A design panel accessible from the sidebar lets you adjust colours, typography, layout and tag styling in the browser, preview changes live, and export a `style.css` file reflecting your choices. Upload that file to your server and the changes are permanent. No database write, no API call — just a CSS file.

## MCP endpoint

Every cask.ink installation ships with `mcp.php`, a Model Context Protocol server endpoint that exposes your content to AI tools. Any MCP-compatible client — Claude, Cursor, or any agent with MCP support — can query your documentation directly, search by keyword, and retrieve page content in raw markdown. The endpoint requires no configuration and no API key for public sites.

## PHP-first with static host fallback

On standard shared hosting, cask.ink scans `/content/` automatically on every page load via PHP. On static hosts (S3, GitHub Pages, Netlify), it falls back silently to a `content/index.json` manifest you maintain manually. The same codebase, the same zip file, works on both — no configuration required to switch between them.

## Wiki-style internal links

Link between pages using `[[page-id]]` or `[[page-id|Display text]]` syntax anywhere in your markdown. cask.ink resolves these against your content at render time and converts them to working internal navigation links. Unresolved links are rendered as greyed-out spans rather than broken anchors.

## Client-side search

The sidebar search box runs a substring match across titles, excerpts and tags without any server-side component or external search service. Fast enough for any real-world documentation set.

## Copy buttons on code blocks

Multi-line code blocks get a copy button automatically. It appears on hover, writes to the clipboard, and confirms with a brief "copied" state. No configuration.

## Genuinely lightweight

The entire cask.ink codebase — `index.php`, `app.js`, `style.css`, `content-list.php`, `mcp.php` — fits in a 25KB zip file. No npm dependencies. No build tools. No runtime beyond PHP 7.4, which ships with virtually every shared hosting plan sold today.

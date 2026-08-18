---
id: installing-cask
title: Installing cask
parent: getting-started.md
order: 1
excerpt: How to download, upload and verify a cask.ink installation on shared or static hosting.
tags:
  - installation
  - setup
---

# Installing cask

## On shared hosting (PHP)

This is the recommended path. Full feature set, automatic content discovery, MCP endpoint included.

### Step 1 — Download

Download the cask.ink zip from [cask.ink/download](https://cask.ink/download).

### Step 2 — Upload

Using FTP (FileZilla, Cyberduck, or similar) or your host's web-based file manager, upload the zip file to the directory where you want cask.ink to live. Some examples:

- `public_html/` — site lives at `yourdomain.com`
- `public_html/docs/` — site lives at `yourdomain.com/docs`
- `public_html/wiki/` — site lives at `yourdomain.com/wiki`

### Step 3 — Extract

Extract (unzip) the file in place. Most web file managers have a right-click "Extract" option. After extraction you should see:

```
index.php
app.js
style.css
content-list.php
mcp.php
content/
content/index.json
content/welcome.md
content/getting-started.md
(etc.)
```

### Step 4 — Verify

Navigate to the directory in your browser. You should see the cask.ink documentation site — this site — loading from your own hosting. If you see a directory listing instead of the site, your host may require the file to be named `index.html`. Contact support or check your host's documentation on default index files.

### Step 5 — Add your content

Delete the example content files from `/content/` (or keep them as reference) and add your own `.md` files. They will appear in the sidebar automatically on the next page load.

---

## On static hosting (S3, GitHub Pages, Netlify, Cloudflare Pages)

Static hosts do not execute PHP. cask.ink detects this automatically and falls back to reading `content/index.json` instead of scanning the directory dynamically.

The trade-off: you must update `content/index.json` manually each time you add, remove or rename a content file. The MCP endpoint (`mcp.php`) will also not function, as it requires PHP.

### Setup

1. Upload all files from the zip exactly as described above
2. Open `content/index.json` — it contains a comment block explaining the format
3. Add an entry for each `.md` file in your `/content/` folder
4. Commit and deploy as normal for your static host

Each entry in `content/index.json` looks like this:

```json
{
  "file":       "my-page.md",
  "identifier": "my-page",
  "title":      "My Page",
  "excerpt":    "A short description shown in search results.",
  "tags":       ["example"],
  "parent":     null,
  "order":      1
}
```

---

## Troubleshooting

**The page is blank or shows an error.**
Check that `index.php` is in the root of the directory you navigated to, and that your host supports PHP 7.4 or above.

**The sidebar shows "No content found."**
On PHP hosts, verify that `content-list.php` is in the same directory as `index.php` and is readable by the web server. On static hosts, verify that `content/index.json` exists and contains valid JSON.

**Styles are not loading.**
Verify that `style.css` is in the same directory as `index.php` and was not excluded from the upload.

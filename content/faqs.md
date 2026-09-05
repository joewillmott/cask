---
id: faqs
title: FAQs
order: 5
excerpt: Common questions about installing and using cask.
tags:
  - help
  - faq
---

# FAQs

## Installation

### Does cask work on shared hosting?

Yes — that's exactly what it's built for. Any host running PHP 7.4 or later will work. cPanel-based hosts (Hostinger, SiteGround, DreamHost, Namecheap, and most others) all qualify.

### Does cask work on GitHub Pages, Netlify, or Vercel?

Not fully. These are static hosts and don't run PHP, which cask needs to read and serve content files. A partial fallback exists — if you create a `content/index.json` file with your page metadata pre-generated, cask can read that instead. But you'll lose the ability to serve the raw markdown files dynamically, so page content won't load.

For most users: just use a shared PHP host. They're inexpensive and cask runs perfectly on them.

### I uploaded the files but see a blank page. What's wrong?

A few things to check:

1. Make sure `index.php` is in the folder you're accessing, not inside a subdirectory created by the zip extraction
2. Check that your host is running PHP 7.4 or later — look in your host's control panel under PHP settings
3. Make sure the `/content/` folder exists and contains at least one `.md` file
4. Check that file permissions allow PHP to read the `/content/` folder — `755` for the folder and `644` for the files is standard

### I see a PHP error when I load the page

The most common cause is a PHP version below 7.4. cask uses some syntax that older PHP versions don't support. Log into your cPanel, find the PHP version selector, and switch to 7.4 or 8.x.

### Can I install cask in a subfolder?

Yes. Upload to any subfolder inside `public_html/` and access it at `yoursite.com/subfolder-name/`. Everything works the same.

---

## Content

### Do I need to use frontmatter in every file?

No. A file with no frontmatter at all will still appear in cask — its title and identifier are derived from the filename. Frontmatter is only required when you want to control ordering, hierarchy, tags, or other metadata.

### Can I use subfolders inside `/content/`?

cask currently reads only files directly inside `/content/` — it doesn't recurse into subfolders. Use the `parent:` frontmatter field to create hierarchy instead of folders. You can still organise your files into subfolders on disk if you like, but cask won't find them there.

### What happens if two files have the same `id`?

The second one processed will overwrite the first in the page index. Avoid duplicate IDs — they'll cause one page to become unreachable.

### Can I use `.txt` files instead of `.md`?

Yes. cask treats `.txt` and `.md` files identically — both are parsed as markdown.

---

## Styling

### My changes in the admin panel disappeared after I closed it

This is expected. The admin panel only applies a temporary preview while it's open. To save your changes permanently, click **Export CSS** before closing, then upload the downloaded `style.css` file to overwrite the original on your server.

### I uploaded a new style.css but nothing changed

If your site is behind Cloudflare or another CDN, the old CSS file may be cached. Try purging the cache from your Cloudflare dashboard, or do a hard refresh in your browser (Ctrl+Shift+R on Windows, Cmd+Shift+R on Mac).

### Can I edit style.css directly?

Yes. Open it in any text editor. The design tokens are in a `:root { }` block near the top of the file with clearly labelled variable names. Edit the values, save, and re-upload. The admin panel reads these values automatically the next time it opens.

---

## The admin panel

### How do I open the admin panel?

Add `?admin` to the end of your site URL and press Enter:

```
https://yoursite.com/?admin
```

The panel opens as a modal. The `?admin` part is removed from the URL automatically so your visitors never see it.

### Can my visitors access the admin panel?

Yes — there's no password protecting it. The admin panel only controls visual styling and display preferences though; it can't modify, delete, or add any content files. Your actual content is only changeable by someone with access to your server files.

---

## AI and MCP

### Do I need to configure anything to use the MCP server?

No. The MCP server at `/mcp.php` works as soon as cask is installed. Just give an MCP-compatible AI tool the URL and it will discover the available tools automatically.

### Is my content secure if I enable MCP?

The MCP server exposes exactly the same content that's publicly visible on your site. It adds no additional access — if a page is public on your site, it's accessible via MCP. There's no way to selectively restrict MCP access to specific pages in the current version.

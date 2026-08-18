---
id: content-management
title: Content management
parent: getting-started.md
order: 3
excerpt: How to add, structure and organise your markdown files in cask.ink.
tags:
  - content
  - markdown
  - frontmatter
---

# Content management

## Adding pages

Drop any `.md` or `.txt` file into the `/content/` folder on your server. It will appear in the sidebar the next time someone loads the page. That is the entire process on PHP hosting.

On static hosts, you also need to add an entry for the file in `content/index.json`. See [[installing-cask|Installing cask]] for the format.

## Frontmatter

cask.ink reads metadata from the YAML frontmatter block at the top of each file — the section between the `---` fences. None of it is required. Files with no frontmatter at all will still appear, using the filename as the title.

```yaml
---
id: my-page
title: My Page
parent: parent-page.md
excerpt: A short description shown in search results.
tags:
  - example
  - guide
order: 3
---
```

### Fields

| Field | Purpose | Default if absent |
|---|---|---|
| `id` or `slug` | URL identifier for this page | Filename without extension |
| `title` | Display title in the sidebar and page header | Filename, converted to Title Case |
| `parent` | Filename or `id` of the parent page | None — page sits at top level |
| `excerpt` | Short description shown in search results | First 160 characters of content |
| `tags` | Array of tags displayed as badges | None |
| `order` | Sort position among siblings (lower = higher) | Alphabetical by title |

### Custom field names

If your files use different frontmatter keys — for example `category` instead of `parent`, or `slug` instead of `id` — you can configure cask.ink to look for those instead. Open the admin panel, go to the **Data Mapping** tab, and enter your key names as comma-separated values in priority order.

## Structuring pages

Hierarchy is set via the `parent` field. The value can be either the filename (`parent-page.md`) or the `id` of the parent page (`parent-page`). Both work.

A page with no `parent` field sits at the top level of the sidebar. A page whose `parent` does not match any known file also falls back to the top level, so a typo in a parent reference will not break the site — it will just place the page at root until corrected.

Circular parent references (A is the parent of B, B is the parent of A) are detected and both pages are placed at root.

## Ordering pages

Use the `order` field to control the sequence of pages within the same level of the hierarchy. Lower numbers appear first. Pages without an `order` value are sorted alphabetically by title after any ordered pages.

```yaml
order: 1   # appears first
order: 2   # appears second
order: 10  # appears after order: 2, before anything without an order
```

## Wiki links

Link to other pages in your cask.ink site using double-bracket syntax:

```
[[page-id]]                    — links to that page, uses the page title as link text
[[page-id|Custom link text]]   — links to that page with custom text
```

The target can be a page `id` or a filename with or without the `.md` extension. If the target does not resolve to a known page, the link renders as plain greyed-out text rather than a broken anchor.

## Images

Standard markdown image syntax works:

```markdown
![Alt text](https://example.com/image.jpg)
```

Images hosted on your server can be referenced with a relative path:

```markdown
![Alt text](/images/my-image.jpg)
```

A `hero_image` or `image` frontmatter field (or whichever key you have configured in Data Mapping) sets a hero image that appears at the top of the content pane above the page body.

## Removing pages

Delete the file from `/content/`. On PHP hosts, it disappears from the sidebar on the next page load. On static hosts, also remove the entry from `content/index.json`.

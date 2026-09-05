---
id: content-management
title: Writing content
parent: getting-started
order: 2
excerpt: How to write, structure, and organise your documentation in cask.
tags:
  - content
  - markdown
  - structure
---

# Writing content

Every page in cask is a plain `.md` or `.txt` file in the `/content/` folder. Create a file, add a frontmatter block at the top, write your content below it in markdown, and cask picks it up immediately — no restart, no build, no import.

## Frontmatter

Frontmatter is a block of structured fields at the very top of each file, wrapped in triple dashes. It tells cask how to title the page, where it sits in the hierarchy, how to sort it, and what tags to show.

```
---
id: my-page
title: My Page
parent: getting-started
order: 3
excerpt: A short description shown in search results.
tags:
  - example
  - guides
thumbnail: https://example.com/my-hero-image.jpg
---

Your markdown content starts here.
```

### Frontmatter reference

| Field | Purpose | Required? |
|---|---|---|
| `id` | Unique identifier — used in URLs, wiki links, and parent references | Recommended |
| `title` | Page title shown in the sidebar and as the page heading | Recommended |
| `parent` | `id` of the parent page — creates hierarchy in the sidebar | Optional |
| `order` | Integer sort position within the same level, lowest first | Optional |
| `excerpt` | Short description shown in search results | Optional |
| `tags` | Tags shown as badges at the bottom of the page | Optional |
| `thumbnail` | URL of a hero image displayed above the page content | Optional |

If `id` is omitted, cask derives it from the filename (`my-page.md` → `my-page`). If `title` is omitted, it's derived from the filename too. Everything else is optional.

## Hierarchy

Set `parent` to the `id` of another page to make it a child of that page:

```
---
id: installing-cask
parent: getting-started
---
```

You can reference the parent by either its `id` or its filename — `getting-started` and `getting-started.md` both work. Hierarchy can go as deep as you need. The sidebar reflects the structure automatically, with toggle arrows on any page that has children.

## Ordering

Pages at the same level sort by `order`, lowest first. Pages without an `order` sort alphabetically after those that have one.

```
---
order: 1
---
```

## Tags

Tags display as small badges at the bottom of the page. They also appear in search results and are searchable. Three formats are all valid:

```yaml
# YAML list
tags:
  - setup
  - installation

# Inline list
tags: [setup, installation]

# Comma-separated string
tags: setup, installation
```

## Hero images

Add a `thumbnail` field with an image URL to display a full-width hero image at the top of the page:

```
---
thumbnail: https://example.com/my-image.jpg
---
```

The image stretches to the full content width and maintains its natural proportions. The corner radius is configurable in the admin panel under Layout & Sizing.

## Wiki links

Link between pages using double-bracket syntax:

```
See [[installing-cask]] for the full walkthrough.
```

The value inside the brackets is the target page's `id`. To use custom link text, add a pipe:

```
See [[installing-cask|the installation guide]] for the full walkthrough.
```

If the target page doesn't exist, the link renders as plain text with a visual indicator.

## Markdown reference

cask renders standard markdown. Everything you'd expect works:

| Syntax | Result |
|---|---|
| `# Heading` | H1 heading |
| `## Heading` | H2 heading |
| `**bold**` | **bold** |
| `*italic*` | *italic* |
| `` `code` `` | inline code |
| `[text](url)` | link |
| `![alt](url)` | image |
| `> text` | blockquote |
| `---` | horizontal rule |

Fenced code blocks with triple backticks get a copy-to-clipboard button automatically.

## File naming

File names have no effect on the site — cask uses frontmatter for everything. Name your files however makes sense for your own filing. Lowercase with hyphens is conventional (`my-page.md`) but not required.

## Supported extensions

cask reads `.md` and `.txt` files. Both are treated identically — the content is always parsed as markdown regardless of extension.

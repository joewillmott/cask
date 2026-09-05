---
id: brand-styling
title: Brand styling
parent: getting-started
order: 3
excerpt: How to change colours, fonts, and layout to match your brand — using the admin panel or by editing CSS directly.
tags:
  - styling
  - design
  - admin
---

# Brand styling

cask includes a live design panel that lets you customise the look of your site without touching any code. Changes apply as you type, so you can see exactly what you're getting before saving anything.

## Opening the admin panel

Add `?admin` to the end of your site URL and press Enter:

```
https://yoursite.com/?admin
```

The admin panel opens as a modal overlay. The `?admin` part is silently removed from the URL once the panel is open — your visitors will never see it.

## The Design tab

The Design tab is divided into sections covering every visual aspect of your site.

### Colours

Controls all the key colours in the interface via CSS custom properties. Each colour field shows both a colour picker swatch and a hex text input — you can use either.

| Setting | What it controls |
|---|---|
| Page Background | The main content area background |
| Body Text | All body copy |
| Accent | Links, active sidebar items, interactive highlights |
| Sidebar Background | The left navigation panel |
| Navbar Background | The top navigation bar |
| Border | Dividers, input borders, table lines |
| Muted Text | Secondary text, labels, placeholders |
| Code Background | Background of inline and fenced code |
| Code Text | Text colour inside code blocks |
| Hover Background | Background colour when hovering sidebar items |

### Typography

| Setting | What it controls |
|---|---|
| Body Font | Font stack for all body text |
| Heading Font | Font stack for all headings |
| Mono Font | Font stack for code and UI labels |
| Base Font Size | The root font size everything else scales from |
| Line Height | Line spacing for body text |
| H1 / H2 / H3 Size & Weight | Individual control over heading sizes and weights |
| Font Import URL | A Google Fonts or similar `@import` URL to load custom fonts |

To use a Google Font, go to [fonts.google.com](https://fonts.google.com), select a font, copy the `@import` URL from the "Use on the web" panel, and paste it into the Font Import URL field. Then update the Body Font or Heading Font field to use that font name.

### Layout & Sizing

| Setting | What it controls |
|---|---|
| Sidebar Width | How wide the left navigation panel is |
| Content Max Width | The maximum width of the main content area |
| Small Radius | Corner radius for small elements (inputs, code, tags) |
| Medium Radius | Corner radius for medium elements (cards, modals) |
| Hero Image Radius | Corner radius applied to hero images on pages |

### Tags

Controls the appearance of the tag badges shown at the bottom of pages:

| Setting | What it controls |
|---|---|
| Tag Background | Badge background colour |
| Tag Text | Badge text colour |
| Tag Border | Badge border colour |
| Tag Corner Radius | How rounded the badge corners are |

### Display Preferences

Three checkboxes that control what elements appear on pages:

| Checkbox | Effect when checked |
|---|---|
| Hide page title | Hides the H1 heading at the top of each page |
| Show tag badges | Displays tag badges at the bottom of each page |
| Show breadcrumb trail | Displays the breadcrumb navigation above the content |

## Making your changes permanent

**Changes in the admin panel only last for your current session.** They're applied as a temporary style overlay — nothing is written to any file while you're working.

To save your changes permanently:

1. Get everything looking the way you want in the admin panel
2. Click **Export CSS**
3. A file called `style.css` downloads to your computer
4. Upload it to your site's root folder, overwriting the existing `style.css`

That's it. The exported file is a complete drop-in replacement for the original — it contains the full stylesheet with your values baked in, plus a small block at the end recording your display preferences.

## Site title and logo

The site title and logo are **not** set through the admin panel — they're PHP values in `index.php`. See [[navbar]] for how to set them.

## Editing CSS directly

If you're comfortable with CSS, you can open `style.css` in any text editor and change values directly. All the design tokens live in a `:root { }` block near the top of the file. The variable names match what you see in the admin panel:

```css
:root {
    --color-bg:          #ffffff;
    --color-text:        #1a1a1a;
    --color-accent:      #2563eb;
    --font-body:         'Inter', system-ui, sans-serif;
    --sidebar-width:     252px;
    /* etc. */
}
```

Edit these values, save the file, and upload it. The admin panel will read your custom values the next time it's opened.

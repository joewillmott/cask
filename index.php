<?php

// ============================================================
// cask.ink — index.php
// ============================================================

// ============================================================
// SITE CONFIG — edit these
// ============================================================

$site_name    = 'cask.ink';
$site_logo    = '';                  // URL to logo image, or leave empty

$nav_links = [
    ['label' => 'GitHub', 'url' => 'https://github.com/joewillmott/cask'],
];

// ============================================================
// FRONTMATTER FIELD MAPPING — edit if your keys differ
// For each data type, list the frontmatter keys cask.ink
// should look for, in priority order.
// First key found in a given file wins for that file.
// ============================================================

$field_map = [
    'identifier' => ['id', 'slug', 'key'],
    'title'      => ['title'],
    'excerpt'    => ['excerpt', 'description'],
    'tags'       => ['tags'],
    'thumbnail'  => ['hero_image', 'image'],
    'parent'     => ['parent'],
    'order'      => ['order'],
];

// ============================================================
// Do not edit below this line unless you know what you're doing
// ============================================================

$escaped_site_name = htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8');

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escaped_site_name ?></title>
    <link rel="stylesheet" href="style.css">

    <!-- marked.js for markdown rendering. Swap for local copy if you prefer. -->
    <script src="https://cdn.jsdelivr.net/npm/marked@12/marked.min.js"></script>
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="cask-navbar">
    <a class="cask-navbar__brand" href="#">
        <?php if ($site_logo): ?>
            <img src="<?= htmlspecialchars($site_logo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $escaped_site_name ?>">
        <?php endif; ?>
        <?= $escaped_site_name ?>
    </a>

    <?php if (!empty($nav_links)): ?>
    <div class="cask-navbar__links">
        <?php foreach ($nav_links as $link): ?>
        <a href="<?= htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button class="cask-sidebar-toggle" id="cask-sidebar-toggle" aria-label="Toggle navigation">
        &#9776;
    </button>
</nav>

<!-- ============================================================
     LAYOUT
     ============================================================ -->
<div class="cask-layout">

    <!-- SIDEBAR -->
    <aside class="cask-sidebar" id="cask-sidebar" role="navigation" aria-label="Documentation">
        <div class="cask-search">
            <input
                type="search"
                id="cask-search-input"
                class="cask-search__input"
                placeholder="Search…"
                autocomplete="off"
                aria-label="Search documentation"
            >
        </div>
        <ul class="cask-nav" id="cask-nav" role="list">
            <li class="cask-nav__item">
                <span class="cask-empty">Loading…</span>
            </li>
        </ul>
        <div class="cask-sidebar__footer">
            <button class="cask-admin-trigger" id="cask-admin-trigger" title="Admin settings" aria-label="Open admin settings">
                cask v0.0.1
            </button>
        </div>
    </aside>

    <!-- CONTENT PANE -->
    <main class="cask-content" id="cask-content-area" role="main">
        <div class="cask-content__inner">
            <nav class="cask-breadcrumb" id="cask-breadcrumb" aria-label="Breadcrumb"></nav>
            <img class="cask-thumbnail" id="cask-thumbnail" src="" alt="" style="display:none;">
            <div class="cask-tags" id="cask-tags" style="display:none;"></div>
            <div class="cask-markdown" id="cask-markdown">
                <span class="cask-loading">Loading content…</span>
            </div>
        </div>
    </main>

</div>

<!-- ============================================================
     ADMIN MODAL
     ============================================================ -->
<div class="cask-modal-overlay" id="cask-modal-overlay" aria-hidden="true">
    <div class="cask-modal" role="dialog" aria-modal="true" aria-labelledby="cask-modal-title">

        <div class="cask-modal__header">
            <h2 class="cask-modal__title" id="cask-modal-title">Admin Settings</h2>
            <div class="cask-modal__tabs">
                <button class="cask-modal__tab active" data-tab="design">Design</button>
                <button class="cask-modal__tab" data-tab="data-mapping">Data Mapping</button>
            </div>
            <button class="cask-modal__close" id="cask-modal-close" aria-label="Close">&times;</button>
        </div>

        <!-- DESIGN TAB -->
        <div class="cask-modal__body" id="cask-tab-design">

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Site Identity</h3>
                <div class="cask-admin-grid cask-admin-grid--2">
                    <div class="cask-admin-field">
                        <label>Site Title</label>
                        <input type="text" id="admin-site-title">
                    </div>
                    <div class="cask-admin-field">
                        <label>Logo URL <span class="cask-admin-optional">(optional)</span></label>
                        <input type="text" id="admin-logo-url" placeholder="https://…">
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Colours <span class="cask-admin-hint">:root { --color-* }</span></h3>
                <div class="cask-admin-grid cask-admin-grid--2">
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Page Background <span class="cask-admin-var">(--color-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Body Text <span class="cask-admin-var">(--color-text)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-text" class="cask-color-swatch">
                            <input type="text"  id="admin-color-text-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Accent / Highlights <span class="cask-admin-var">(--color-accent)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-accent" class="cask-color-swatch">
                            <input type="text"  id="admin-color-accent-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Sidebar Background <span class="cask-admin-var">(--color-sidebar-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-sidebar-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-sidebar-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Borders &amp; Dividers <span class="cask-admin-var">(--color-border)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-border" class="cask-color-swatch">
                            <input type="text"  id="admin-color-border-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Muted Text <span class="cask-admin-var">(--color-muted)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-muted" class="cask-color-swatch">
                            <input type="text"  id="admin-color-muted-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Code Block Background <span class="cask-admin-var">(--color-code-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-code-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-code-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Code Text <span class="cask-admin-var">(--color-code-text)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-code-text" class="cask-color-swatch">
                            <input type="text"  id="admin-color-code-text-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Navbar Background <span class="cask-admin-var">(--color-navbar-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-navbar-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-navbar-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Item Hover Background <span class="cask-admin-var">(--color-hover-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-hover-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-hover-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Fonts &amp; Stylesheets</h3>
                <div class="cask-admin-field">
                    <label>Google Fonts / CSS Import URLs <span class="cask-admin-hint">One per line</span></label>
                    <textarea id="admin-font-imports" rows="3" placeholder="https://fonts.googleapis.com/css2?family=…"></textarea>
                </div>
                <div class="cask-admin-grid cask-admin-grid--3">
                    <div class="cask-admin-field">
                        <label>Headings Font</label>
                        <input type="text" id="admin-font-heading" placeholder="'Lora', Georgia, serif">
                    </div>
                    <div class="cask-admin-field">
                        <label>Body Font</label>
                        <input type="text" id="admin-font-body" placeholder="'Inter', system-ui, sans-serif">
                    </div>
                    <div class="cask-admin-field">
                        <label>Monospace / UI Font</label>
                        <input type="text" id="admin-font-mono" placeholder="'DM Mono', monospace">
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Text Styles &amp; Sizes</h3>
                <div class="cask-admin-grid cask-admin-grid--4">
                    <div class="cask-admin-field">
                        <label>Base Font Size</label>
                        <input type="text" id="admin-font-size-base" placeholder="17px">
                    </div>
                    <div class="cask-admin-field">
                        <label>Body Line Height</label>
                        <input type="text" id="admin-line-height" placeholder="1.75">
                    </div>
                    <div class="cask-admin-field">
                        <label>H1 Size</label>
                        <input type="text" id="admin-h1-size" placeholder="2rem">
                    </div>
                    <div class="cask-admin-field">
                        <label>H1 Weight</label>
                        <input type="text" id="admin-h1-weight" placeholder="600">
                    </div>
                    <div class="cask-admin-field">
                        <label>H2 Size</label>
                        <input type="text" id="admin-h2-size" placeholder="1.45rem">
                    </div>
                    <div class="cask-admin-field">
                        <label>H2 Weight</label>
                        <input type="text" id="admin-h2-weight" placeholder="600">
                    </div>
                    <div class="cask-admin-field">
                        <label>H3 Size</label>
                        <input type="text" id="admin-h3-size" placeholder="1.15rem">
                    </div>
                    <div class="cask-admin-field">
                        <label>H3 Weight</label>
                        <input type="text" id="admin-h3-weight" placeholder="600">
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Layout &amp; Sizing</h3>
                <div class="cask-admin-grid cask-admin-grid--4">
                    <div class="cask-admin-field">
                        <label>Sidebar Width</label>
                        <input type="text" id="admin-sidebar-width" placeholder="272px">
                    </div>
                    <div class="cask-admin-field">
                        <label>Content Max Width</label>
                        <input type="text" id="admin-content-max-width" placeholder="72ch">
                    </div>
                    <div class="cask-admin-field">
                        <label>Small Radius</label>
                        <input type="text" id="admin-radius-sm" placeholder="3px">
                    </div>
                    <div class="cask-admin-field">
                        <label>Medium Radius</label>
                        <input type="text" id="admin-radius-md" placeholder="6px">
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Tags</h3>
                <div class="cask-admin-grid cask-admin-grid--2">
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Tag Background <span class="cask-admin-var">(--color-tag-bg)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-tag-bg" class="cask-color-swatch">
                            <input type="text"  id="admin-color-tag-bg-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Tag Text <span class="cask-admin-var">(--color-tag-text)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-tag-text" class="cask-color-swatch">
                            <input type="text"  id="admin-color-tag-text-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field cask-admin-field--color">
                        <label>Tag Border <span class="cask-admin-var">(--color-tag-border)</span></label>
                        <div class="cask-color-row">
                            <input type="color" id="admin-color-tag-border" class="cask-color-swatch">
                            <input type="text"  id="admin-color-tag-border-text" class="cask-color-text" maxlength="9">
                        </div>
                    </div>
                    <div class="cask-admin-field">
                        <label>Tag Corner Radius <span class="cask-admin-var">(--radius-tag)</span></label>
                        <input type="text" id="admin-radius-tag" placeholder="3px">
                    </div>
                </div>
            </section>

            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Display Preferences</h3>
                <div class="cask-admin-grid cask-admin-grid--2">
                    <label class="cask-admin-checkbox">
                        <input type="checkbox" id="admin-hide-title">
                        Hide page title from page header
                    </label>
                    <label class="cask-admin-checkbox">
                        <input type="checkbox" id="admin-hide-frontmatter">
                        Hide YAML frontmatter in document body
                    </label>
                    <label class="cask-admin-checkbox">
                        <input type="checkbox" id="admin-show-tags">
                        Show tag badges on pages
                    </label>
                    <label class="cask-admin-checkbox">
                        <input type="checkbox" id="admin-show-breadcrumb">
                        Show breadcrumb trail
                    </label>
                </div>
            </section>

            <div class="cask-admin-actions">
                <p class="cask-admin-note">Changes preview instantly. Export your CSS to make them permanent.</p>
                <div class="cask-admin-actions__buttons">
                    <button class="cask-btn cask-btn--secondary" id="admin-reset">Reset to defaults</button>
                    <button class="cask-btn cask-btn--secondary" id="admin-export-css">Export CSS</button>
                    <button class="cask-btn cask-btn--primary" id="admin-save">Apply &amp; Preview</button>
                </div>
            </div>

        </div><!-- /design tab -->

        <!-- DATA MAPPING TAB -->
        <div class="cask-modal__body cask-modal__body--hidden" id="cask-tab-data-mapping">
            <section class="cask-admin-section">
                <h3 class="cask-admin-section__title">Frontmatter Field Mapping</h3>
                <p class="cask-admin-desc">For each data type, enter the frontmatter key names cask.ink should look for, comma-separated, in priority order. The first key found in a given file wins for that file.</p>
                <div class="cask-admin-grid cask-admin-grid--2">
                    <div class="cask-admin-field">
                        <label>Identifier <span class="cask-admin-hint">URL slug</span></label>
                        <input type="text" id="admin-map-identifier" placeholder="id, slug, key">
                    </div>
                    <div class="cask-admin-field">
                        <label>Title</label>
                        <input type="text" id="admin-map-title" placeholder="title">
                    </div>
                    <div class="cask-admin-field">
                        <label>Excerpt <span class="cask-admin-hint">Search preview</span></label>
                        <input type="text" id="admin-map-excerpt" placeholder="excerpt, description">
                    </div>
                    <div class="cask-admin-field">
                        <label>Tags</label>
                        <input type="text" id="admin-map-tags" placeholder="tags">
                    </div>
                    <div class="cask-admin-field">
                        <label>Thumbnail <span class="cask-admin-hint">Hero image URL</span></label>
                        <input type="text" id="admin-map-thumbnail" placeholder="hero_image, image">
                    </div>
                    <div class="cask-admin-field">
                        <label>Parent <span class="cask-admin-hint">Hierarchy</span></label>
                        <input type="text" id="admin-map-parent" placeholder="parent">
                    </div>
                    <div class="cask-admin-field">
                        <label>Order <span class="cask-admin-hint">Sort position</span></label>
                        <input type="text" id="admin-map-order" placeholder="order">
                    </div>
                    <div class="cask-admin-field">
                        <label>Sidebar Expand Depth <span class="cask-admin-hint">0 = collapsed, 1 = top level open</span></label>
                        <input type="number" id="admin-expand-depth" min="0" max="10" placeholder="1">
                    </div>
                </div>
            </section>
            <div class="cask-admin-actions">
                <p class="cask-admin-note">Changing field mapping reloads and re-parses all content from your /content/ folder.</p>
                <div class="cask-admin-actions__buttons">
                    <button class="cask-btn cask-btn--primary" id="admin-map-save">Apply Mapping &amp; Reload</button>
                </div>
            </div>
        </div><!-- /data mapping tab -->

    </div><!-- /cask-modal -->
</div><!-- /cask-modal-overlay -->


<script>
window.CASK_FIELD_MAP = <?= json_encode($field_map) ?>;
window.CASK_CONFIG = {
    expandDepth: 1,   // 0 = all collapsed, 1 = top level open, 2 = top + children, etc.
    siteName: <?= json_encode($site_name) ?>
};
</script>
<script src="app.js"></script>

</body>
</html>

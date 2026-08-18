<?php

// === cask.ink — content-list.php ===
// Scans /content/ and returns a JSON array of all nodes with parsed frontmatter.
// Called by app.js on page load. Falls back to content/index.json on static hosts.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// === frontmatter field mapping ===
// For each data type, list the frontmatter keys cask.ink should look for,
// in priority order. First key found in a given file wins for that file.
// Edit these to match your files if your keys differ from the defaults.
$field_map = [
    'identifier' => ['id', 'slug', 'key'],
    'title'      => ['title'],
    'excerpt'    => ['excerpt', 'description'],
    'tags'       => ['tags'],
    'thumbnail'  => ['hero_image', 'image'],
    'parent'     => ['parent'],
    'order'      => ['order'],
];

// -----------------------------------------------------------------

$content_dir = __DIR__ . '/content';
$nodes = [];

function parse_frontmatter(string $raw): array {
    $frontmatter = [];
    $content_start = 0;

    if (strpos(ltrim($raw), '---') === 0) {
        $raw = ltrim($raw);
        $end = strpos($raw, '---', 3);
        if ($end !== false) {
            $yaml_block = substr($raw, 3, $end - 3);
            $content_start = $end + 3;

            // Parse YAML line by line (covers string, number, array block formats)
            $lines = explode("\n", trim($yaml_block));
            $current_key = null;
            $in_array = false;

            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s*$/', $line, $m)) {
                    // Key with no inline value — start of a block array
                    $current_key = $m[1];
                    $frontmatter[$current_key] = [];
                    $in_array = true;
                } elseif ($in_array && preg_match('/^\s+-\s+(.+)$/', $line, $m)) {
                    // Array item
                    $frontmatter[$current_key][] = trim($m[1], '"\'');
                } elseif (preg_match('/^(\w+):\s*\[(.+)\]/', $line, $m)) {
                    // Inline array: tags: [foo, bar]
                    $current_key = $m[1];
                    $in_array = false;
                    $frontmatter[$current_key] = array_map(
                        fn($v) => trim($v, ' "\''),
                        explode(',', $m[2])
                    );
                } elseif (preg_match('/^(\w+):\s*(.+)$/', $line, $m)) {
                    // Simple key: value
                    $current_key = $m[1];
                    $in_array = false;
                    $frontmatter[$current_key] = trim($m[2], '"\'');
                } else {
                    $in_array = false;
                }
            }
        }
    }

    $body = trim(substr($raw, $content_start));
    return ['frontmatter' => $frontmatter, 'body' => $body];
}

function resolve_field(array $frontmatter, array $keys): mixed {
    foreach ($keys as $key) {
        if (isset($frontmatter[$key]) && $frontmatter[$key] !== '') {
            return $frontmatter[$key];
        }
    }
    return null;
}

function filename_to_title(string $filename): string {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['-', '_'], ' ', $name);
    return ucwords($name);
}

function filename_to_identifier(string $filename): string {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
}

if (!is_dir($content_dir)) {
    echo json_encode(['error' => 'content/ directory not found']);
    exit;
}

$files = glob($content_dir . '/*.md');
if ($files === false) $files = [];

// Also pick up .txt files
$txt_files = glob($content_dir . '/*.txt');
if ($txt_files) $files = array_merge($files, $txt_files);

foreach ($files as $filepath) {
    $filename = basename($filepath);
    $raw = file_get_contents($filepath);
    $parsed = parse_frontmatter($raw);
    $fm = $parsed['frontmatter'];

    // Resolve each field using the priority-ordered key map
    $identifier = resolve_field($fm, $field_map['identifier'])
        ?? filename_to_identifier($filename);

    $title = resolve_field($fm, $field_map['title'])
        ?? filename_to_title($filename);

    $excerpt   = resolve_field($fm, $field_map['excerpt']);
    $tags      = resolve_field($fm, $field_map['tags']) ?? [];
    $thumbnail = resolve_field($fm, $field_map['thumbnail']);
    $parent    = resolve_field($fm, $field_map['parent']);
    $order     = resolve_field($fm, $field_map['order']);

    // Normalise tags to array
    if (is_string($tags)) {
        $tags = array_map('trim', explode(',', $tags));
    }

    // Auto-generate excerpt from body if not set
    if (!$excerpt && !empty($parsed['body'])) {
        $plain = strip_tags($parsed['body']);
        $plain = preg_replace('/\s+/', ' ', $plain);
        $excerpt = mb_substr(trim($plain), 0, 160);
        if (mb_strlen($plain) > 160) $excerpt .= '…';
    }

    $nodes[] = [
        'file'       => $filename,
        'identifier' => $identifier,
        'title'      => $title,
        'excerpt'    => $excerpt,
        'thumbnail'  => $thumbnail,
        'tags'       => array_values(array_filter((array) $tags)),
        'parent'     => $parent,
        'order'      => $order !== null ? (int) $order : null,
    ];
}

// Sort: by order (nulls last), then alphabetically by title
usort($nodes, function ($a, $b) {
    $ao = $a['order'] ?? PHP_INT_MAX;
    $bo = $b['order'] ?? PHP_INT_MAX;
    if ($ao !== $bo) return $ao <=> $bo;
    return strcmp($a['title'], $b['title']);
});

echo json_encode($nodes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

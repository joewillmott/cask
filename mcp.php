<?php

// === cask.ink — mcp.php ===
// MCP (Model Context Protocol) server endpoint.
// Exposes your /content/ folder as a structured, AI-queryable knowledge base.
// Three tools: list_pages, get_page, search_pages.
//
// No auth is applied. Content here is the same content visible on the public site.
// Private-doc access control is a future feature.
//
// NOTE: This endpoint requires PHP to execute. It will not function on static hosts
// (S3, GitHub Pages, Netlify). A static-host MCP fallback is a planned fast-follow.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$content_dir = __DIR__ . '/content';

// Reuse the same parsing logic as content-list.php
function mcp_parse_frontmatter(string $raw): array {
    $frontmatter = [];
    $content_start = 0;

    if (strpos(ltrim($raw), '---') === 0) {
        $raw_trimmed = ltrim($raw);
        $end = strpos($raw_trimmed, '---', 3);
        if ($end !== false) {
            $yaml_block = substr($raw_trimmed, 3, $end - 3);
            $content_start = $end + 3;
            $lines = explode("\n", trim($yaml_block));
            $current_key = null;
            $in_array = false;

            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s*$/', $line, $m)) {
                    $current_key = $m[1];
                    $frontmatter[$current_key] = [];
                    $in_array = true;
                } elseif ($in_array && preg_match('/^\s+-\s+(.+)$/', $line, $m)) {
                    $frontmatter[$current_key][] = trim($m[1], '"\'');
                } elseif (preg_match('/^(\w+):\s*\[(.+)\]/', $line, $m)) {
                    $current_key = $m[1];
                    $in_array = false;
                    $frontmatter[$current_key] = array_map(
                        fn($v) => trim($v, ' "\''),
                        explode(',', $m[2])
                    );
                } elseif (preg_match('/^(\w+):\s*(.+)$/', $line, $m)) {
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

function mcp_get_all_nodes(string $content_dir): array {
    $nodes = [];
    $files = array_merge(
        glob($content_dir . '/*.md') ?: [],
        glob($content_dir . '/*.txt') ?: []
    );

    foreach ($files as $filepath) {
        $filename = basename($filepath);
        $raw = file_get_contents($filepath);
        $parsed = mcp_parse_frontmatter($raw);
        $fm = $parsed['frontmatter'];

        $identifier = $fm['id'] ?? $fm['slug'] ?? $fm['key']
            ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', pathinfo($filename, PATHINFO_FILENAME)));

        $nodes[] = [
            'file'       => $filename,
            'identifier' => $identifier,
            'title'      => $fm['title'] ?? ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME))),
            'excerpt'    => $fm['excerpt'] ?? $fm['description'] ?? null,
            'tags'       => isset($fm['tags']) ? (array) $fm['tags'] : [],
            'parent'     => $fm['parent'] ?? null,
            'order'      => isset($fm['order']) ? (int) $fm['order'] : null,
            'raw'        => $raw,
            'body'       => $parsed['body'],
        ];
    }

    return $nodes;
}

function mcp_response(string $tool, $content): void {
    echo json_encode([
        'tool'    => $tool,
        'content' => $content,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function mcp_error(string $message): void {
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

// Parse the incoming request
$input = json_decode(file_get_contents('php://input'), true);
$tool  = $input['tool'] ?? $_GET['tool'] ?? null;
$args  = $input['arguments'] ?? $input['args'] ?? [];

if (!$tool) {
    // Return tool manifest when called with no tool
    echo json_encode([
        'name'    => 'cask.ink',
        'version' => '1.0.0',
        'tools'   => [
            [
                'name'        => 'list_pages',
                'description' => 'Returns the full list of pages with hierarchy, titles, tags and excerpts.',
                'parameters'  => [],
            ],
            [
                'name'        => 'get_page',
                'description' => 'Returns the raw markdown content of a single page by its identifier or filename.',
                'parameters'  => [
                    'id' => [
                        'type'        => 'string',
                        'description' => 'The page identifier or filename (e.g. "pokemon-mewtwo" or "pokemon-mewtwo.md")',
                        'required'    => true,
                    ],
                ],
            ],
            [
                'name'        => 'search_pages',
                'description' => 'Searches page titles and content for a query string. Returns matching pages.',
                'parameters'  => [
                    'query' => [
                        'type'        => 'string',
                        'description' => 'The search term to look for.',
                        'required'    => true,
                    ],
                ],
            ],
        ],
    ], JSON_PRETTY_PRINT);
    exit;
}

$nodes = mcp_get_all_nodes($content_dir);

// -------------------------------------------------------
// Tool: list_pages
// -------------------------------------------------------
if ($tool === 'list_pages') {
    $pages = array_map(fn($n) => [
        'file'       => $n['file'],
        'identifier' => $n['identifier'],
        'title'      => $n['title'],
        'excerpt'    => $n['excerpt'],
        'tags'       => $n['tags'],
        'parent'     => $n['parent'],
        'order'      => $n['order'],
    ], $nodes);

    mcp_response('list_pages', $pages);
}

// -------------------------------------------------------
// Tool: get_page
// -------------------------------------------------------
if ($tool === 'get_page') {
    $id = $args['id'] ?? null;
    if (!$id) mcp_error('get_page requires an "id" argument.');

    foreach ($nodes as $node) {
        if (
            $node['identifier'] === $id ||
            $node['file'] === $id ||
            $node['file'] === $id . '.md' ||
            $node['file'] === $id . '.txt'
        ) {
            mcp_response('get_page', [
                'file'       => $node['file'],
                'identifier' => $node['identifier'],
                'title'      => $node['title'],
                'raw'        => $node['raw'],
            ]);
        }
    }

    mcp_error("No page found with id or filename: {$id}");
}

// -------------------------------------------------------
// Tool: search_pages
// -------------------------------------------------------
if ($tool === 'search_pages') {
    $query = strtolower(trim($args['query'] ?? ''));
    if ($query === '') mcp_error('search_pages requires a "query" argument.');

    $results = [];
    foreach ($nodes as $node) {
        $haystack = strtolower($node['title'] . ' ' . $node['body'] . ' ' . implode(' ', $node['tags']));
        if (strpos($haystack, $query) !== false) {
            $results[] = [
                'file'       => $node['file'],
                'identifier' => $node['identifier'],
                'title'      => $node['title'],
                'excerpt'    => $node['excerpt'],
                'tags'       => $node['tags'],
                'parent'     => $node['parent'],
            ];
        }
    }

    mcp_response('search_pages', $results);
}

mcp_error("Unknown tool: {$tool}");

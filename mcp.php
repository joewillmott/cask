<?php

// === cask — mcp.php ===
// Proper MCP server implementing the Streamable HTTP transport (spec 2025-03-26).
// JSON-RPC 2.0 over HTTP POST. Single endpoint, no sessions, no SSE needed
// for these synchronous tools.
//
// Supported methods:
//   initialize               — capability handshake
//   notifications/initialized — client confirmation (no-op, returns 202)
//   ping                     — heartbeat
//   tools/list               — list available tools
//   tools/call               — call a tool
//
// Tools:
//   list_pages    — returns all pages with metadata and hierarchy
//   get_page      — returns full markdown content of one page by id or filename
//   search_pages  — full-text search across titles, tags, and body content
//
// No auth is applied. Content here is identical to what is publicly visible
// on the site. Access control is a future feature.

// ---------------------------------------------------------------------------
// Headers
// ---------------------------------------------------------------------------

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, MCP-Protocol-Version, Mcp-Method, Mcp-Name');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(rpc_error(null, -32600, 'Method not allowed. Use POST.'));
    exit;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define('MCP_PROTOCOL_VERSION', '2025-03-26');
define('SERVER_NAME',          'cask');
define('SERVER_VERSION',       '1.0.0');
define('CONTENT_DIR',          __DIR__ . '/content');

// ---------------------------------------------------------------------------
// JSON-RPC envelope helpers
// ---------------------------------------------------------------------------

function rpc_result($id, $result): array {
    return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
}

function rpc_error($id, int $code, string $message, $data = null): array {
    $err = ['code' => $code, 'message' => $message];
    if ($data !== null) $err['data'] = $data;
    return ['jsonrpc' => '2.0', 'id' => $id, 'error' => $err];
}

function text_content(string $text): array {
    return ['content' => [['type' => 'text', 'text' => $text]]];
}

// ---------------------------------------------------------------------------
// Frontmatter parser
// ---------------------------------------------------------------------------

function parse_frontmatter(string $raw): array {
    $frontmatter   = [];
    $content_start = 0;

    if (strpos(ltrim($raw), '---') === 0) {
        $trimmed = ltrim($raw);
        $end     = strpos($trimmed, '---', 3);
        if ($end !== false) {
            $yaml_block    = substr($trimmed, 3, $end - 3);
            $content_start = $end + 3;
            $lines         = explode("\n", trim($yaml_block));
            $current_key   = null;
            $in_array      = false;

            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s*$/', $line, $m)) {
                    $current_key                 = $m[1];
                    $frontmatter[$current_key]   = [];
                    $in_array                    = true;
                } elseif ($in_array && preg_match('/^\s+-\s+(.+)$/', $line, $m)) {
                    $frontmatter[$current_key][] = trim($m[1], '"\'');
                } elseif (preg_match('/^(\w+):\s*\[(.+)\]/', $line, $m)) {
                    $current_key               = $m[1];
                    $in_array                  = false;
                    $frontmatter[$current_key] = array_map(
                        fn($v) => trim($v, ' "\''),
                        explode(',', $m[2])
                    );
                } elseif (preg_match('/^(\w+):\s*(.+)$/', $line, $m)) {
                    $current_key               = $m[1];
                    $in_array                  = false;
                    $frontmatter[$current_key] = trim($m[2], '"\'');
                } else {
                    $in_array = false;
                }
            }
        }
    }

    return [
        'frontmatter' => $frontmatter,
        'body'        => trim(substr($raw, $content_start)),
    ];
}

// ---------------------------------------------------------------------------
// Content loader
// ---------------------------------------------------------------------------

function load_all_nodes(): array {
    $nodes = [];
    $files = array_merge(
        glob(CONTENT_DIR . '/*.md')  ?: [],
        glob(CONTENT_DIR . '/*.txt') ?: []
    );

    foreach ($files as $filepath) {
        $filename = basename($filepath);
        $raw      = file_get_contents($filepath);
        $parsed   = parse_frontmatter($raw);
        $fm       = $parsed['frontmatter'];

        $identifier = $fm['id'] ?? $fm['slug'] ?? $fm['key']
            ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', pathinfo($filename, PATHINFO_FILENAME)));

        $title = $fm['title']
            ?? ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

        $tags = isset($fm['tags']) ? (array) $fm['tags'] : [];

        // Auto-generate excerpt from body if not set in frontmatter
        $excerpt = $fm['excerpt'] ?? $fm['description'] ?? null;
        if (!$excerpt && !empty($parsed['body'])) {
            $plain   = preg_replace('/\s+/', ' ', strip_tags($parsed['body']));
            $excerpt = mb_substr(trim($plain), 0, 200);
            if (mb_strlen($plain) > 200) $excerpt .= '…';
        }

        $nodes[] = [
            'file'       => $filename,
            'identifier' => $identifier,
            'title'      => $title,
            'excerpt'    => $excerpt,
            'tags'       => $tags,
            'parent'     => $fm['parent'] ?? null,
            'order'      => isset($fm['order']) ? (int) $fm['order'] : null,
            'body'       => $parsed['body'],
            'raw'        => $raw,
        ];
    }

    // Sort: by order (nulls last), then alphabetically
    usort($nodes, function ($a, $b) {
        $ao = $a['order'] ?? PHP_INT_MAX;
        $bo = $b['order'] ?? PHP_INT_MAX;
        if ($ao !== $bo) return $ao <=> $bo;
        return strcmp($a['title'], $b['title']);
    });

    return $nodes;
}

// ---------------------------------------------------------------------------
// Tool definitions (returned by tools/list)
// ---------------------------------------------------------------------------

function tool_definitions(): array {
    return [
        [
            'name'        => 'list_pages',
            'description' => 'Returns all pages in the documentation with their titles, identifiers, tags, excerpts, parent relationships, and sort order. Call this first to understand the structure of the knowledge base before fetching specific pages.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => (object) [],
                'required'   => [],
            ],
        ],
        [
            'name'        => 'get_page',
            'description' => 'Returns the full markdown content of a single page, identified by its id or filename. Use list_pages first to find the correct identifier.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'id' => [
                        'type'        => 'string',
                        'description' => 'The page identifier (e.g. "getting-started") or filename (e.g. "getting-started.md").',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
        [
            'name'        => 'search_pages',
            'description' => 'Searches all page titles, body content, and tags for a given query string. Returns matching pages with their metadata. Useful for finding relevant documentation without reading every page.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'query' => [
                        'type'        => 'string',
                        'description' => 'The search term to look for across all pages.',
                    ],
                ],
                'required' => ['query'],
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Tool execution
// ---------------------------------------------------------------------------

function call_tool(string $name, array $args): array {
    $nodes = load_all_nodes();

    // ---- list_pages --------------------------------------------------------
    if ($name === 'list_pages') {
        $pages = array_map(fn($n) => [
            'identifier' => $n['identifier'],
            'file'       => $n['file'],
            'title'      => $n['title'],
            'excerpt'    => $n['excerpt'],
            'tags'       => $n['tags'],
            'parent'     => $n['parent'],
            'order'      => $n['order'],
        ], $nodes);

        return text_content(json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ---- get_page ----------------------------------------------------------
    if ($name === 'get_page') {
        $id = trim($args['id'] ?? '');
        if ($id === '') {
            throw new InvalidArgumentException('get_page requires an "id" argument.');
        }

        $id_stripped = preg_replace('/\.(md|txt)$/i', '', $id);

        foreach ($nodes as $node) {
            if (
                $node['identifier'] === $id          ||
                $node['identifier'] === $id_stripped ||
                $node['file']       === $id          ||
                $node['file']       === $id . '.md'  ||
                $node['file']       === $id . '.txt'
            ) {
                $result = [
                    'identifier' => $node['identifier'],
                    'file'       => $node['file'],
                    'title'      => $node['title'],
                    'content'    => $node['raw'],
                ];
                return text_content(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        throw new InvalidArgumentException("No page found with id or filename: {$id}");
    }

    // ---- search_pages ------------------------------------------------------
    if ($name === 'search_pages') {
        $query = strtolower(trim($args['query'] ?? ''));
        if ($query === '') {
            throw new InvalidArgumentException('search_pages requires a "query" argument.');
        }

        $results = [];
        foreach ($nodes as $node) {
            $haystack = strtolower(
                $node['title'] . ' ' .
                $node['body']  . ' ' .
                implode(' ', $node['tags'])
            );
            if (strpos($haystack, $query) !== false) {
                $results[] = [
                    'identifier' => $node['identifier'],
                    'file'       => $node['file'],
                    'title'      => $node['title'],
                    'excerpt'    => $node['excerpt'],
                    'tags'       => $node['tags'],
                    'parent'     => $node['parent'],
                ];
            }
        }

        return text_content(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    throw new InvalidArgumentException("Unknown tool: {$name}");
}

// ---------------------------------------------------------------------------
// Request dispatch
// ---------------------------------------------------------------------------

$raw   = file_get_contents('php://input');
$body  = json_decode($raw, true);

if (!is_array($body) || ($body['jsonrpc'] ?? '') !== '2.0') {
    http_response_code(400);
    echo json_encode(rpc_error(null, -32600, 'Invalid JSON-RPC 2.0 request.'));
    exit;
}

$id     = $body['id']     ?? null;
$method = $body['method'] ?? '';
$params = $body['params'] ?? [];
if (!is_array($params)) $params = [];

// ---- initialize ------------------------------------------------------------
if ($method === 'initialize') {
    echo json_encode(rpc_result($id, [
        'protocolVersion' => MCP_PROTOCOL_VERSION,
        'capabilities'    => [
            'tools' => ['listChanged' => false],
        ],
        'serverInfo' => [
            'name'    => SERVER_NAME,
            'version' => SERVER_VERSION,
        ],
    ]));
    exit;
}

// ---- notifications/initialized (client confirmation, no response body) -----
if ($method === 'notifications/initialized') {
    http_response_code(202);
    exit;
}

// ---- ping ------------------------------------------------------------------
if ($method === 'ping') {
    echo json_encode(rpc_result($id, (object) []));
    exit;
}

// ---- tools/list ------------------------------------------------------------
if ($method === 'tools/list') {
    echo json_encode(rpc_result($id, ['tools' => tool_definitions()]));
    exit;
}

// ---- tools/call ------------------------------------------------------------
if ($method === 'tools/call') {
    $tool_name = $params['name']      ?? '';
    $tool_args = $params['arguments'] ?? [];
    if (!is_array($tool_args)) $tool_args = [];

    if ($tool_name === '') {
        echo json_encode(rpc_error($id, -32602, 'Missing required parameter: name'));
        exit;
    }

    try {
        $result = call_tool($tool_name, $tool_args);
        echo json_encode(rpc_result($id, $result));
    } catch (InvalidArgumentException $e) {
        echo json_encode(rpc_error($id, -32602, $e->getMessage()));
    } catch (Throwable $e) {
        echo json_encode(rpc_error($id, -32603, 'Internal error: ' . $e->getMessage()));
    }
    exit;
}

// ---- unknown method --------------------------------------------------------
echo json_encode(rpc_error($id, -32601, "Method not found: {$method}"));
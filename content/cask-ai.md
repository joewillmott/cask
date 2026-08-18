---
id: cask-ai
title: Cask AI
order: 3
excerpt: cask.ink ships with a built-in MCP endpoint that makes your content queryable by AI tools, agents and coding assistants — no configuration required.
tags:
  - ai
  - mcp
  - integrations
---

# Cask AI

Every cask.ink installation ships with `mcp.php` — a Model Context Protocol (MCP) server endpoint that exposes your content to AI tools. Any MCP-compatible client can connect to it and query your documentation directly: listing pages, fetching raw markdown, and searching by keyword.

This is not a bolt-on feature. It is part of the default install, active from the moment you put cask.ink on a server, requiring no configuration and no API key for public sites.

## What MCP is

The Model Context Protocol is an open standard, developed by Anthropic, that lets AI tools connect to external data sources in a structured way. Instead of copy-pasting documentation into a chat window, an MCP-compatible tool like Claude, Cursor or any MCP-aware agent can query your cask.ink site directly while working — reading pages, searching for relevant content, and navigating your hierarchy — the same way it would use a tool or a web search.

Your documentation becomes part of the AI's working context, automatically, without any manual retrieval step.

## The endpoint

The MCP endpoint lives at:

```
https://yourdomain.com/path-to-cask/mcp.php
```

It accepts POST requests with a JSON body specifying the tool to call and its arguments. It also accepts GET requests with a `tool` query parameter, and returns the tool manifest when called with no arguments.

## Available tools

### `list_pages`

Returns the full list of pages with their hierarchy, titles, excerpts, tags and parent references.

```json
{
  "tool": "list_pages"
}
```

### `get_page`

Returns the raw markdown content of a single page, identified by its `id` or filename.

```json
{
  "tool": "get_page",
  "arguments": {
    "id": "content-management"
  }
}
```

The `id` field accepts the page's declared identifier, its filename with or without the `.md` extension.

### `search_pages`

Searches page titles, body content and tags for a query string. Returns all matching pages with their metadata.

```json
{
  "tool": "search_pages",
  "arguments": {
    "query": "frontmatter"
  }
}
```

## Connecting to Claude

To connect your cask.ink MCP endpoint to Claude desktop:

1. Open your Claude desktop configuration file. On macOS this is at `~/Library/Application Support/Claude/claude_desktop_config.json`.
2. Add your cask.ink endpoint as an MCP server:

```json
{
  "mcpServers": {
    "my-docs": {
      "command": "npx",
      "args": [
        "-y",
        "mcp-remote",
        "https://yourdomain.com/path-to-cask/mcp.php"
      ]
    }
  }
}
```

3. Restart Claude desktop. Your documentation will be available as a connected knowledge source.

## Connecting to Cursor

In Cursor, go to **Settings → MCP** and add a new server with your endpoint URL. Cursor will discover the available tools automatically and make them available to the AI assistant during coding sessions.

## A note on static hosts

`mcp.php` requires PHP to execute. It will not function on static hosts (S3, GitHub Pages, Netlify). If you are on a static host and want MCP support, the only current option is to move to PHP hosting. A static-host MCP fallback using a pre-generated `mcp.json` is planned for a future release.

## Security

By default the MCP endpoint is publicly accessible, because the content it returns is the same content visible on the public site. It is a machine-readable door onto what is already public, not a new exposure surface.

Access control for private documentation sites is a planned feature. In the interim, if you need to restrict MCP access, your host's `.htpasswd` basic authentication can be placed in front of `mcp.php` specifically.

---
id: cask-ai
title: AI tools
order: 4
excerpt: Connect your cask documentation to Claude, Cursor, and other AI tools via a built-in MCP server — no configuration required.
tags:
  - ai
  - mcp
  - integrations
---

# AI tools

cask includes a built-in MCP (Model Context Protocol) server. Point any MCP-compatible AI tool at your cask site and it can read, search, and reason over all your documentation automatically.

## What MCP is

MCP is an open standard that lets AI tools connect to external data sources in a structured way. Instead of pasting documentation into a chat window, you give an AI tool a URL and it can query your content directly — fetching pages, searching for information, and navigating the hierarchy on its own.

## Your MCP endpoint

Your cask MCP server is available at:

```
https://yoursite.com/mcp.php
```

Replace `yoursite.com` with your actual domain. No setup, no API key, no configuration — it works as soon as cask is installed.

## Connecting to Claude

In Claude's settings, go to **Integrations** and add a new MCP server. Paste your `mcp.php` URL. Claude will discover the available tools automatically and can then answer questions about your documentation, find specific pages, and navigate the content hierarchy.

## Connecting to Cursor

In Cursor, open Settings and find the MCP section. Add your `mcp.php` URL as a new server. Cursor will index the available tools and can use your documentation as context when writing code, answering questions, or explaining concepts.

## What the MCP server exposes

The MCP server provides three tools:

### list_pages

Returns the complete list of pages with their titles, identifiers, tags, excerpts, hierarchy, and sort order. An AI tool uses this to understand the structure of your documentation before diving into specific pages.

### get_page

Fetches the full raw markdown content of a single page, identified by its `id` or filename. An AI tool uses this to read a specific page in detail.

### search_pages

Searches page titles, body content, and tags for a given query string. Returns all matching pages with their metadata. An AI tool uses this to find relevant content without having to read every page.

## Calling the MCP server directly

You can also call `mcp.php` directly from a browser or HTTP client to inspect what it returns.

With no parameters, it returns the tool manifest:

```
GET https://yoursite.com/mcp.php
```

To call a specific tool, pass a `tool` parameter:

```
GET https://yoursite.com/mcp.php?tool=list_pages
GET https://yoursite.com/mcp.php?tool=get_page&id=welcome
GET https://yoursite.com/mcp.php?tool=search_pages&query=installation
```

Or POST a JSON body:

```json
{
  "tool": "get_page",
  "arguments": { "id": "welcome" }
}
```

## A note on access control

The MCP server exposes exactly the same content that's publicly visible on your site. It applies no authentication. If your documentation is meant to be public, this is fine. If you need private documentation, cask is not yet the right tool.

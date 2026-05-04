# Meraki Roots WordPress Dev Workspace

Local Docker-based WordPress/WooCommerce development workspace for `merakiroots/merakirootscbd-wp`.

## Quick Start

```powershell
npm install
npm test
npm run mcp:http
```

The local MCP endpoint is:

```text
http://127.0.0.1:8787/mcp
```

For stdio-capable local agents:

```powershell
npm run mcp:stdio
```

## MCP Bridge

This repo includes a custom MCP server that exposes the current workspace to agents without handing them the whole machine.

| Tool | Purpose |
| --- | --- |
| `workspace_status` | Git status, Docker Compose state, Node/npm versions, and MCP security mode |
| `list_workspace_files` | Safe source file listing with dependency/upload/secret exclusions |
| `read_workspace_file` | Safe text file read with secret path blocking and output redaction |
| `search_workspace` | Ripgrep search over source paths |
| `get_wp_debug_log` | Tail local WordPress debug logs with redaction |
| `docker_compose` | Guarded Docker Compose diagnostics and optional mutations |
| `wp_cli` | WP-CLI through the `wpcli` Compose service |
| `run_quality_check` | Predefined PHP/Node quality commands for the plugin/workspace |

Default mode is read-and-diagnose. Write-capable actions are blocked unless explicitly enabled:

```powershell
$env:MCP_ALLOW_MUTATIONS = "true"
npm run mcp:http
```

File writes are intentionally not exposed by default. Keep `.env`, keys, uploads, dependency directories, and editor config out of the MCP surface.

## ChatGPT / Project Workspace Path

1. Start the local HTTP server:

   ```powershell
   npm run mcp:http
   ```

2. Verify the health endpoint:

   ```powershell
   Invoke-RestMethod http://127.0.0.1:8787/
   ```

3. Expose it with a trusted HTTPS tunnel when a browser-hosted ChatGPT workspace needs to reach this machine. The public MCP URL should end in `/mcp`, for example:

   ```text
   https://your-trusted-tunnel.example/mcp
   ```

4. In ChatGPT Apps / custom MCP setup, add that HTTPS endpoint as the MCP server URL.

For anything beyond a private dev tunnel, add OAuth before sharing the URL outside trusted editors. OpenAI's MCP guidance recommends OAuth and dynamic client registration for custom remote MCP servers, and ChatGPT Apps use OAuth when linking authenticated remote servers:

- https://developers.openai.com/api/docs/mcp
- https://developers.openai.com/apps-sdk/deploy/connect-chatgpt
- https://developers.openai.com/apps-sdk/build/auth

## Local Agent Config

Example snippets live in `mcp/clients/`:

- `codex.config.toml.example`
- `vscode-mcp.json`

The local VS Code-style `.vscode/mcp.json` in this workspace points at `npm run mcp:stdio`.

## Commands

| Command | Description |
| --- | --- |
| `npm test` | Run MCP safety tests |
| `npm run test:mcp` | Run MCP-specific tests |
| `npm run mcp:stdio` | Start the MCP server over stdio |
| `npm run mcp:http` | Start the MCP server over Streamable HTTP at `/mcp` |
| `docker compose up -d --build` | Start the WordPress stack |
| `docker compose ps` | Show local container state |

## Notes

Docker Desktop must be running for `wp_cli`, Docker Compose, PHP lint/static analysis, and PHPUnit tools. When Docker is unavailable, the MCP server returns the real container error while still allowing source inspection and log reads.

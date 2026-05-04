# MCP Workspace Bridge

## What This Gives Agents

The MCP server turns this repo into a bounded WordPress development environment for agents. It can inspect source, search code, read debug logs, report git/Docker status, run read-only WP-CLI commands, and run predefined quality checks.

It does not expose `.env`, keys, uploads, `.git`, `.vscode`, `node_modules`, or `vendor` directories through file tools.

## Start Local HTTP

```powershell
npm run mcp:http
```

Default endpoint:

```text
http://127.0.0.1:8787/mcp
```

Health check:

```powershell
Invoke-RestMethod http://127.0.0.1:8787/
```

Custom port:

```powershell
$env:MCP_PORT = "8790"
npm run mcp:http
```

## Start Local Stdio

```powershell
npm run mcp:stdio
```

Use stdio for agents running on this same machine. Use HTTP only when the client needs a URL.

## Connect ChatGPT

ChatGPT web cannot reach `127.0.0.1` on this machine. To connect a ChatGPT project workspace, expose the local server through a trusted HTTPS tunnel and use the tunneled `/mcp` URL.

Example final URL shape:

```text
https://your-trusted-tunnel.example/mcp
```

Keep the server in default read-and-diagnose mode for the first connection. Enable mutations only when you want agents to run write-capable WP-CLI or Docker actions:

```powershell
$env:MCP_ALLOW_MUTATIONS = "true"
npm run mcp:http
```

For any durable public endpoint, add OAuth. OpenAI's docs recommend OAuth and dynamic client registration for custom remote MCP servers protecting private data:

- https://developers.openai.com/api/docs/mcp
- https://developers.openai.com/apps-sdk/build/auth

## Smoke Test HTTP Manually

Streamable HTTP clients must send an `Accept` header that allows JSON and server-sent events:

```powershell
$body = @{
  jsonrpc = "2.0"
  id = 1
  method = "initialize"
  params = @{
    protocolVersion = "2025-06-18"
    capabilities = @{}
    clientInfo = @{ name = "manual-smoke"; version = "0.0.1" }
  }
} | ConvertTo-Json -Depth 10

Invoke-RestMethod `
  -Uri "http://127.0.0.1:8787/mcp" `
  -Method Post `
  -ContentType "application/json" `
  -Headers @{ Accept = "application/json, text/event-stream" } `
  -Body $body
```

## Tool Safety Model

Read tools:

- `workspace_status`
- `list_workspace_files`
- `read_workspace_file`
- `search_workspace`
- `get_wp_debug_log`

Guarded command tools:

- `docker_compose`
- `wp_cli`
- `run_quality_check`

Mutation switch:

```powershell
$env:MCP_ALLOW_MUTATIONS = "true"
```

The default should stay read-only for ChatGPT project agents until the agent and tunnel are trusted.

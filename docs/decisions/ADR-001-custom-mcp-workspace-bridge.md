# ADR-001: Custom MCP Bridge for Local WordPress Workspace

## Status
Accepted

## Date
2026-05-03

## Context
ChatGPT project agents and other MCP-aware agents need repeatable access to this local WordPress/WooCommerce workspace. The useful context lives in a Docker Compose environment, local plugin/theme files, git state, and WordPress debug logs. Directly exposing the filesystem or arbitrary shell commands would make it too easy to leak `.env` values, SSH keys, database credentials, or upload contents.

OpenAI's current MCP guidance supports Streamable HTTP servers for remote agents and stdio servers for local agent processes. The same docs recommend OAuth for custom remote MCP servers when they protect private data.

## Decision
Build a repo-local Node MCP server with both transports:

- `npm run mcp:stdio` for local agents that can launch a process in this workspace.
- `npm run mcp:http` for browser-hosted agents that need an HTTPS-tunneled `/mcp` endpoint.

Expose a conservative tool surface:

- Workspace status and git/Docker diagnostics.
- Source file listing, safe file reads, and ripgrep search.
- WordPress debug-log reads.
- WP-CLI through the `wpcli` Compose service.
- Predefined quality checks for PHP and Node workflows.

Default behavior is read-and-diagnose. Write-capable Docker and WP-CLI operations require `MCP_ALLOW_MUTATIONS=true`. Secret-like files, dependency directories, uploads, `.git`, and `.vscode` are blocked from read tools.

## Alternatives Considered

### Arbitrary Shell MCP Tool
Pros: Maximally flexible for agents.
Cons: Too risky for a WordPress workspace with `.env`, database credentials, SSH keys, and host-level Docker access.
Rejected because a narrower tool contract provides the needed workflows with far less blast radius.

### GitHub-Only Context
Pros: Easy for ChatGPT agents to read committed code.
Cons: Does not expose local Docker state, local `.env` shape, debug logs, generated files, or uncommitted diagnostics.
Rejected as incomplete for PHP/WordPress debugging.

### Deploy MCP Server to Vercel
Pros: Stable HTTPS endpoint.
Cons: A Vercel deployment cannot directly inspect or control this local Docker Desktop workspace.
Deferred until there is a separate remote WordPress/dev environment to target.

## Consequences
- Agents can inspect and diagnose the real local workspace through a repeatable MCP interface.
- Remote ChatGPT access still needs an HTTPS tunnel or hosted environment.
- Production sharing requires OAuth or another approved auth boundary before exposing private workspace data.
- The MCP bridge has its own tests and documentation, so future changes can be made safely.

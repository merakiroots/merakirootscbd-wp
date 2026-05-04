import { StreamableHTTPServerTransport } from '@modelcontextprotocol/sdk/server/streamableHttp.js';
import { createMcpExpressApp } from '@modelcontextprotocol/sdk/server/express.js';

import { createWorkspaceMcpServer } from './server.js';
import { createWorkspaceConfig } from './lib/workspace.js';

const config = createWorkspaceConfig();
const host = process.env.MCP_HOST ?? '127.0.0.1';
const port = Number.parseInt(process.env.MCP_PORT ?? '', 10) || 8787;

const app = createMcpExpressApp();

app.get('/', (_req, res) => {
  res.json({
    ok: true,
    name: config.name,
    transport: 'streamable-http',
    endpoint: '/mcp',
    workspaceRoot: config.workspaceRoot,
  });
});

app.post('/mcp', async (req, res) => {
  const server = createWorkspaceMcpServer({ workspaceRoot: config.workspaceRoot, env: config.env });
  const transport = new StreamableHTTPServerTransport({ sessionIdGenerator: undefined });

  try {
    await server.connect(transport);
    res.on('close', () => {
      transport.close();
      server.close();
    });
    await transport.handleRequest(req, res, req.body);
  } catch (error) {
    console.error('MCP request failed:', error);
    if (!res.headersSent) {
      res.status(500).json({
        jsonrpc: '2.0',
        error: { code: -32603, message: 'Internal server error' },
        id: null,
      });
    }
  }
});

app.get('/mcp', (_req, res) => {
  res.status(405).json({
    jsonrpc: '2.0',
    error: { code: -32000, message: 'Method not allowed. Use POST /mcp for this stateless Streamable HTTP server.' },
    id: null,
  });
});

app.delete('/mcp', (_req, res) => {
  res.status(405).json({
    jsonrpc: '2.0',
    error: { code: -32000, message: 'Method not allowed. This server is stateless.' },
    id: null,
  });
});

app.listen(port, host, error => {
  if (error) {
    console.error('Failed to start MCP HTTP server:', error);
    process.exit(1);
  }

  console.error(`Meraki Roots WordPress MCP server listening at http://${host}:${port}/mcp`);
});

import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';

import { createWorkspaceMcpServer } from './server.js';

const server = createWorkspaceMcpServer();
const transport = new StdioServerTransport();

await server.connect(transport);

import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import * as z from 'zod/v4';

import {
  createWorkspaceConfig,
  errorResult,
  getDebugLog,
  jsonResult,
  listWorkspaceFiles,
  readWorkspaceFile,
  runDockerCompose,
  runQualityCheck,
  runWpCli,
  searchWorkspace,
  workspaceStatus,
} from './lib/workspace.js';

async function toolResult(fn) {
  try {
    return jsonResult(await fn());
  } catch (error) {
    return errorResult(error.message);
  }
}

export function createWorkspaceMcpServer(overrides = {}) {
  const config = createWorkspaceConfig(overrides);
  const server = new McpServer(
    {
      name: config.name,
      title: 'Meraki Roots WordPress Dev Workspace',
      version: config.version,
    },
    {
      capabilities: {
        logging: {},
      },
    },
  );

  server.registerTool(
    'workspace_status',
    {
      title: 'Workspace Status',
      description: 'Summarize the local WordPress workspace, git status, Docker Compose state, and MCP safety mode.',
      annotations: { readOnlyHint: true, openWorldHint: false },
      inputSchema: {},
    },
    async () => toolResult(() => workspaceStatus(config)),
  );

  server.registerTool(
    'list_workspace_files',
    {
      title: 'List Workspace Files',
      description: 'List source files in this workspace, excluding secrets, dependency folders, uploads, and ignored bulk paths.',
      annotations: { readOnlyHint: true, openWorldHint: false },
      inputSchema: {
        directory: z.string().default('.').describe('Relative workspace directory to list.'),
        maxResults: z.number().int().min(1).max(1000).default(250),
      },
    },
    async input => toolResult(() => listWorkspaceFiles(config, input)),
  );

  server.registerTool(
    'read_workspace_file',
    {
      title: 'Read Workspace File',
      description: 'Read a text file from the workspace with secret-path blocking and output redaction.',
      annotations: { readOnlyHint: true, openWorldHint: false },
      inputSchema: {
        filePath: z.string().min(1).describe('Relative path to a readable workspace file.'),
        maxBytes: z.number().int().min(1).max(128000).default(64000),
      },
    },
    async input => toolResult(() => readWorkspaceFile(config, input)),
  );

  server.registerTool(
    'search_workspace',
    {
      title: 'Search Workspace',
      description: 'Search workspace source text with ripgrep, using safe argument passing and secret/bulk path exclusions.',
      annotations: { readOnlyHint: true, openWorldHint: false },
      inputSchema: {
        query: z.string().min(1).max(200).describe('Ripgrep search pattern.'),
        directory: z.string().default('.').describe('Relative workspace directory to search.'),
        maxResults: z.number().int().min(1).max(500).default(100),
      },
    },
    async input => toolResult(() => searchWorkspace(config, input)),
  );

  server.registerTool(
    'get_wp_debug_log',
    {
      title: 'Get WordPress Debug Log',
      description: 'Read the tail of the local WordPress debug log, with redaction.',
      annotations: { readOnlyHint: true, openWorldHint: false },
      inputSchema: {
        source: z.enum(['logs', 'wp-content']).default('logs'),
        lines: z.number().int().min(1).max(1000).default(120),
      },
    },
    async input => toolResult(() => getDebugLog(config, input)),
  );

  server.registerTool(
    'docker_compose',
    {
      title: 'Docker Compose',
      description: 'Run guarded Docker Compose diagnostics. Mutating actions require MCP_ALLOW_MUTATIONS=true.',
      inputSchema: {
        action: z.enum(['ps', 'config', 'logs', 'up', 'restart', 'down']),
        service: z.enum(['db', 'composer', 'wordpress', 'wpcli', 'phpmyadmin', 'mailhog']).optional(),
        tail: z.number().int().min(1).max(500).default(80),
      },
    },
    async input => toolResult(() => runDockerCompose(config, input)),
  );

  server.registerTool(
    'wp_cli',
    {
      title: 'WP-CLI',
      description: 'Run WP-CLI inside the wpcli Docker Compose service. Read-only commands are allowed by default; writes require MCP_ALLOW_MUTATIONS=true.',
      inputSchema: {
        args: z.array(z.string().min(1).max(400)).min(1).max(40).describe('WP-CLI args, for example ["plugin", "list"].'),
      },
    },
    async input => toolResult(() => runWpCli(config, input)),
  );

  server.registerTool(
    'run_quality_check',
    {
      title: 'Run Quality Check',
      description: 'Run predefined test, lint, static-analysis, or build commands for the WordPress plugin/workspace.',
      inputSchema: {
        target: z.enum([
          'plugin-phpunit',
          'plugin-phpcs',
          'plugin-phpstan',
          'plugin-npm-test',
          'plugin-npm-build',
          'root-npm-test',
        ]),
      },
    },
    async input => toolResult(() => runQualityCheck(config, input)),
  );

  return server;
}

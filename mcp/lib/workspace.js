import fs from 'node:fs/promises';
import path from 'node:path';

import {
  DEFAULT_EXCLUDES,
  assertAllowedReadPath,
  assertInsideWorkspace,
  buildDockerComposeCommand,
  buildMutatingDockerComposeCommand,
  describeSecurityMode,
  isMutationAllowed,
  isReadOnlyWpCliArgs,
  redactSensitiveText,
  sanitizeCommandArgs,
} from './safety.js';
import { runCommand, truncateText } from './command.js';

const DEFAULT_TIMEOUT_MS = 90000;
const MAX_FILE_BYTES = 128000;

export function createWorkspaceConfig(overrides = {}) {
  const env = overrides.env ?? process.env;
  const workspaceRoot = path.resolve(overrides.workspaceRoot ?? env.WP_DEV_WORKSPACE_ROOT ?? process.cwd());
  const pluginPath = overrides.pluginPath ?? 'wp-content/plugins/my-plugin';

  return {
    workspaceRoot,
    pluginPath,
    name: env.MCP_SERVER_NAME ?? 'merakiroots-wp-dev',
    version: '0.1.0',
    env,
    timeoutMs: Number.parseInt(env.MCP_TOOL_TIMEOUT_MS ?? '', 10) || DEFAULT_TIMEOUT_MS,
    maxOutputBytes: Number.parseInt(env.MCP_MAX_OUTPUT_BYTES ?? '', 10) || 24000,
  };
}

export function jsonResult(output) {
  return {
    content: [{ type: 'text', text: JSON.stringify(output, null, 2) }],
    structuredContent: output,
  };
}

export function errorResult(message, details = {}) {
  const output = { ok: false, error: message, ...details };
  return {
    content: [{ type: 'text', text: JSON.stringify(output, null, 2) }],
    structuredContent: output,
    isError: true,
  };
}

export async function workspaceStatus(config) {
  const fastDockerConfig = { ...config, timeoutMs: Math.min(config.timeoutMs, 10000) };
  const [gitStatus, dockerPs, dockerServices, nodeVersion, npmVersion] = await Promise.all([
    runCommand('git', ['status', '--short'], config),
    runCommand('docker', ['compose', 'ps'], fastDockerConfig),
    runCommand('docker', ['compose', 'config', '--services'], fastDockerConfig),
    runCommand('node', ['--version'], config),
    runCommand('npm', ['--version'], config),
  ]);

  return {
    ok: true,
    workspaceRoot: config.workspaceRoot,
    pluginPath: config.pluginPath,
    security: describeSecurityMode(config.env),
    commands: {
      gitStatus,
      dockerPs,
      dockerServices,
      nodeVersion,
      npmVersion,
    },
  };
}

function rgBaseArgs() {
  return DEFAULT_EXCLUDES.flatMap(pattern => ['--glob', `!${pattern}`]);
}

export async function listWorkspaceFiles(config, { directory = '.', maxResults = 250 } = {}) {
  assertAllowedReadPath(config.workspaceRoot, directory);
  const safeMax = Math.min(Math.max(maxResults, 1), 1000);
  const args = ['--files', ...rgBaseArgs(), directory];
  const result = await runCommand('rg', args, config);
  const files = result.stdout
    .split(/\r?\n/)
    .filter(Boolean)
    .slice(0, safeMax);

  return {
    ok: result.exitCode === 0 || result.exitCode === 1,
    directory,
    count: files.length,
    truncated: result.stdout.split(/\r?\n/).filter(Boolean).length > safeMax,
    files,
    command: result,
  };
}

export async function readWorkspaceFile(config, { filePath, maxBytes = MAX_FILE_BYTES } = {}) {
  const resolved = assertAllowedReadPath(config.workspaceRoot, filePath);
  const stat = await fs.stat(resolved);
  if (!stat.isFile()) {
    throw new Error(`Path is not a file: ${filePath}`);
  }

  const safeMaxBytes = Math.min(Math.max(maxBytes, 1), MAX_FILE_BYTES);
  const buffer = await fs.readFile(resolved);
  const text = truncateText(redactSensitiveText(buffer.toString('utf8')), safeMaxBytes);

  return {
    ok: true,
    filePath,
    bytes: stat.size,
    truncated: stat.size > Buffer.byteLength(text, 'utf8'),
    text,
  };
}

export async function searchWorkspace(config, { query, directory = '.', maxResults = 100 } = {}) {
  if (typeof query !== 'string' || query.trim() === '') {
    throw new Error('Search query must be a non-empty string.');
  }
  if (query.length > 200 || query.includes('\0')) {
    throw new Error('Search query is too long or contains an invalid byte.');
  }
  assertAllowedReadPath(config.workspaceRoot, directory);

  const safeMax = Math.min(Math.max(maxResults, 1), 500);
  const args = [
    '--line-number',
    '--no-heading',
    '--color',
    'never',
    '--max-count',
    String(safeMax),
    ...rgBaseArgs(),
    query,
    directory,
  ];
  const result = await runCommand('rg', args, config);
  const matches = result.stdout
    .split(/\r?\n/)
    .filter(Boolean)
    .slice(0, safeMax);

  return {
    ok: result.exitCode === 0 || result.exitCode === 1,
    query,
    directory,
    count: matches.length,
    matches,
    command: result,
  };
}

export async function getDebugLog(config, { source = 'logs', lines = 120 } = {}) {
  const logPath = source === 'wp-content' ? 'wp-content/debug-logs/debug.log' : 'logs/debug.log';
  const resolved = assertAllowedReadPath(config.workspaceRoot, logPath);
  const text = await fs.readFile(resolved, 'utf8').catch(error => {
    if (error.code === 'ENOENT') {
      return '';
    }
    throw error;
  });
  const safeLines = Math.min(Math.max(lines, 1), 1000);
  const tail = text.split(/\r?\n/).slice(-safeLines).join('\n');
  return {
    ok: true,
    logPath,
    lines: safeLines,
    text: truncateText(redactSensitiveText(tail), config.maxOutputBytes),
  };
}

export async function runDockerCompose(config, { action, service, tail = 80 } = {}) {
  const readOnlyActions = new Set(['ps', 'config', 'logs']);
  const commandBuilder = readOnlyActions.has(action) ? buildDockerComposeCommand : buildMutatingDockerComposeCommand;

  if (!readOnlyActions.has(action) && !isMutationAllowed(config.env)) {
    return {
      ok: false,
      error: `docker compose ${action} is write-capable. Set MCP_ALLOW_MUTATIONS=true to enable it.`,
      action,
    };
  }

  const [command, args] = commandBuilder(action, { service, tail });
  const result = await runCommand(command, args, config);
  return {
    ok: result.exitCode === 0,
    action,
    result,
  };
}

export async function runWpCli(config, { args = [] } = {}) {
  const cleanArgs = sanitizeCommandArgs(args, { maxArgs: 40, maxArgLength: 400 });
  const readOnly = isReadOnlyWpCliArgs(cleanArgs);

  if (!readOnly && !isMutationAllowed(config.env)) {
    return {
      ok: false,
      error: 'This WP-CLI command is write-capable or not in the read-only allowlist. Set MCP_ALLOW_MUTATIONS=true to enable it.',
      args: cleanArgs,
      readOnly,
    };
  }

  const wpArgs = cleanArgs.includes('--allow-root') ? cleanArgs : [...cleanArgs, '--allow-root'];
  const result = await runCommand('docker', ['compose', 'run', '--rm', 'wpcli', ...wpArgs], config);
  return {
    ok: result.exitCode === 0,
    args: cleanArgs,
    readOnly,
    result,
  };
}

export async function runQualityCheck(config, { target } = {}) {
  const pluginHostPath = path.relative(config.workspaceRoot, assertInsideWorkspace(config.workspaceRoot, config.pluginPath));
  const pluginContainerPath = '/var/www/html/wp-content/plugins/my-plugin';
  const checks = {
    'plugin-phpunit': ['docker', ['compose', 'run', '--rm', '--entrypoint', 'bash', 'wordpress', '-lc', `cd ${pluginContainerPath} && vendor/bin/phpunit`]],
    'plugin-phpcs': ['docker', ['compose', 'run', '--rm', '--entrypoint', 'bash', 'wordpress', '-lc', `cd ${pluginContainerPath} && vendor/bin/phpcs .`]],
    'plugin-phpstan': ['docker', ['compose', 'run', '--rm', '--entrypoint', 'bash', 'wordpress', '-lc', `cd ${pluginContainerPath} && vendor/bin/phpstan analyse src --level=5`]],
    'plugin-npm-test': ['npm', ['--prefix', pluginHostPath, 'test']],
    'plugin-npm-build': ['npm', ['--prefix', pluginHostPath, 'run', 'build']],
    'root-npm-test': ['npm', ['test']],
  };

  if (!Object.hasOwn(checks, target)) {
    throw new Error(`Unsupported quality check target: ${target}`);
  }

  if (target === 'plugin-npm-build' && !isMutationAllowed(config.env)) {
    return {
      ok: false,
      error: 'plugin-npm-build writes build artifacts. Set MCP_ALLOW_MUTATIONS=true to enable it.',
      target,
    };
  }

  const [command, args] = checks[target];
  const result = await runCommand(command, args, config);
  return {
    ok: result.exitCode === 0,
    target,
    result,
  };
}

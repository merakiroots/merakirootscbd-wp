import { spawn } from 'node:child_process';

import { redactSensitiveText } from './safety.js';

const WINDOWS_CMD_SHIMS = new Set(['npm', 'npx']);

function normalizeSpawn(command, args) {
  if (process.platform === 'win32' && WINDOWS_CMD_SHIMS.has(command)) {
    return {
      command: process.env.ComSpec || 'cmd.exe',
      args: ['/d', '/s', '/c', command, ...args],
    };
  }
  return { command, args };
}

export function formatCommand(command, args = []) {
  return [command, ...args].map(part => (/\s/.test(part) ? JSON.stringify(part) : part)).join(' ');
}

export function truncateText(value, maxBytes = 20000) {
  if (!value) {
    return '';
  }

  const bytes = Buffer.byteLength(value, 'utf8');
  if (bytes <= maxBytes) {
    return value;
  }

  let truncated = value;
  while (Buffer.byteLength(truncated, 'utf8') > maxBytes) {
    truncated = truncated.slice(0, Math.floor(truncated.length * 0.9));
  }
  return `${truncated}\n...[truncated ${bytes - Buffer.byteLength(truncated, 'utf8')} bytes]`;
}

export function runCommand(command, args = [], options = {}) {
  const {
    cwd = options.workspaceRoot ?? process.cwd(),
    timeoutMs = 60000,
    maxOutputBytes = 24000,
    env = process.env,
  } = options;

  return new Promise(resolve => {
    let child;
    try {
      const normalized = normalizeSpawn(command, args);
      child = spawn(normalized.command, normalized.args, {
        cwd,
        env,
        shell: false,
        windowsHide: true,
      });
    } catch (error) {
      resolve({
        command: formatCommand(command, args),
        exitCode: null,
        timedOut: false,
        stdout: '',
        stderr: redactSensitiveText(error.message),
      });
      return;
    }

    let stdout = '';
    let stderr = '';
    let timedOut = false;

    const timer = setTimeout(() => {
      timedOut = true;
      child.kill('SIGTERM');
    }, timeoutMs);

    child.stdout?.on('data', chunk => {
      stdout += chunk.toString('utf8');
      if (Buffer.byteLength(stdout, 'utf8') > maxOutputBytes * 2) {
        stdout = truncateText(stdout, maxOutputBytes);
      }
    });

    child.stderr?.on('data', chunk => {
      stderr += chunk.toString('utf8');
      if (Buffer.byteLength(stderr, 'utf8') > maxOutputBytes * 2) {
        stderr = truncateText(stderr, maxOutputBytes);
      }
    });

    child.on('error', error => {
      clearTimeout(timer);
      resolve({
        command: formatCommand(command, args),
        exitCode: null,
        timedOut,
        stdout: '',
        stderr: redactSensitiveText(error.message),
      });
    });

    child.on('close', exitCode => {
      clearTimeout(timer);
      resolve({
        command: formatCommand(command, args),
        exitCode,
        timedOut,
        stdout: truncateText(redactSensitiveText(stdout), maxOutputBytes),
        stderr: truncateText(redactSensitiveText(stderr), maxOutputBytes),
      });
    });
  });
}

import path from 'node:path';

export const DEFAULT_EXCLUDES = [
  '.git/**',
  '.vscode/**',
  'node_modules/**',
  'vendor/**',
  'wp-content/uploads/**',
  'wp-content/**/vendor/**',
  'wp-content/**/node_modules/**',
];

const SECRET_BASENAMES = [
  '.env',
  '.env.local',
  '.env.development',
  '.env.production',
  '.npmrc',
  '.pypirc',
  'id_rsa',
  'id_dsa',
  'id_ed25519',
];

const SECRET_EXTENSIONS = new Set(['.key', '.pem', '.p12', '.pfx', '.jks', '.keystore']);
const ALWAYS_BLOCKED_WP_CLI_COMMANDS = new Set(['eval', 'eval-file', 'shell']);
const READ_ONLY_WP_CLI_PAIRS = new Set([
  'cache type',
  'cli version',
  'core check-update',
  'core version',
  'db check',
  'db size',
  'db tables',
  'option get',
  'option list',
  'plugin get',
  'plugin list',
  'post get',
  'post list',
  'site list',
  'theme get',
  'theme list',
  'transient get',
  'transient list',
  'user get',
  'user list',
  'wc product get',
  'wc product list',
  'wc system_status',
  'wc system_status_tool',
]);

const SAFE_DOCKER_SERVICES = new Set(['db', 'composer', 'wordpress', 'wpcli', 'phpmyadmin', 'mailhog']);

export function assertInsideWorkspace(workspaceRoot, relativePath = '.') {
  if (typeof relativePath !== 'string' || relativePath.trim() === '') {
    throw new Error('Path must be a non-empty relative workspace path.');
  }

  if (relativePath.includes('\0')) {
    throw new Error('Path contains an invalid null byte.');
  }

  if (path.isAbsolute(relativePath) || /^[a-zA-Z]:[\\/]/.test(relativePath)) {
    throw new Error('Path must be relative to the workspace.');
  }

  const root = path.resolve(workspaceRoot);
  const resolved = path.resolve(root, relativePath);
  const rel = path.relative(root, resolved);

  if (rel === '' || (!rel.startsWith('..') && !path.isAbsolute(rel))) {
    return resolved;
  }

  throw new Error(`Path resolves outside workspace: ${relativePath}`);
}

export function isDeniedReadPath(workspaceRoot, targetPath) {
  const root = path.resolve(workspaceRoot);
  const rel = path.relative(root, targetPath).replace(/\\/g, '/');
  const parts = rel.split('/').filter(Boolean);
  const basename = path.basename(targetPath).toLowerCase();
  const ext = path.extname(targetPath).toLowerCase();

  if (SECRET_BASENAMES.includes(basename) || basename.startsWith('.env.')) {
    return true;
  }

  if (SECRET_EXTENSIONS.has(ext)) {
    return true;
  }

  if (parts.some(part => part === '.git' || part === '.vscode' || part === 'node_modules' || part === 'vendor')) {
    return true;
  }

  if (rel === 'wp-content/uploads' || rel.startsWith('wp-content/uploads/')) {
    return true;
  }

  return false;
}

export function assertAllowedReadPath(workspaceRoot, relativePath) {
  const resolved = assertInsideWorkspace(workspaceRoot, relativePath);
  if (isDeniedReadPath(workspaceRoot, resolved)) {
    throw new Error(`Path is not readable through this MCP server: ${relativePath}`);
  }
  return resolved;
}

export function sanitizeCommandArgs(args, { maxArgs = 32, maxArgLength = 300 } = {}) {
  if (!Array.isArray(args)) {
    throw new Error('Command args must be an array of strings.');
  }
  if (args.length > maxArgs) {
    throw new Error(`Too many command arguments. Max: ${maxArgs}`);
  }
  return args.map(arg => {
    if (typeof arg !== 'string' || arg.length === 0) {
      throw new Error('Command arguments must be non-empty strings.');
    }
    if (arg.length > maxArgLength) {
      throw new Error(`Command argument is too long. Max length: ${maxArgLength}`);
    }
    if (arg.includes('\0')) {
      throw new Error('Command argument contains an invalid null byte.');
    }
    return arg;
  });
}

export function isReadOnlyWpCliArgs(args) {
  const cleanArgs = sanitizeCommandArgs(args);
  const positional = cleanArgs.filter(arg => !arg.startsWith('-')).map(arg => arg.toLowerCase());

  if (positional.length === 0) {
    return false;
  }

  const [primary, secondary, tertiary] = positional;
  if (ALWAYS_BLOCKED_WP_CLI_COMMANDS.has(primary)) {
    return false;
  }

  if (cleanArgs.includes('--help') || cleanArgs.includes('-h')) {
    return true;
  }

  if (primary === 'wc' && tertiary) {
    return READ_ONLY_WP_CLI_PAIRS.has(`${primary} ${secondary} ${tertiary}`);
  }

  return READ_ONLY_WP_CLI_PAIRS.has(`${primary} ${secondary ?? ''}`.trim());
}

export function isMutationAllowed(env = process.env) {
  return ['1', 'true', 'yes', 'on'].includes(String(env.MCP_ALLOW_MUTATIONS ?? '').toLowerCase());
}

export function isFileWriteAllowed(env = process.env) {
  return ['1', 'true', 'yes', 'on'].includes(String(env.MCP_ALLOW_FILE_WRITES ?? '').toLowerCase());
}

export function buildDockerComposeCommand(action, options = {}) {
  switch (action) {
    case 'ps':
      return ['docker', ['compose', 'ps']];
    case 'config':
      return ['docker', ['compose', 'config']];
    case 'logs': {
      const args = ['compose', 'logs'];
      const tail = Number.isInteger(options.tail) ? Math.min(Math.max(options.tail, 1), 500) : 80;
      args.push(`--tail=${tail}`);
      if (options.service) {
        if (!SAFE_DOCKER_SERVICES.has(options.service)) {
          throw new Error(`Unsupported docker compose service: ${options.service}`);
        }
        args.push(options.service);
      }
      return ['docker', args];
    }
    default:
      throw new Error(`Unsupported docker compose action: ${action}`);
  }
}

export function buildMutatingDockerComposeCommand(action, options = {}) {
  switch (action) {
    case 'up':
      return ['docker', ['compose', 'up', '-d', '--build']];
    case 'restart': {
      const args = ['compose', 'restart'];
      if (options.service) {
        if (!SAFE_DOCKER_SERVICES.has(options.service)) {
          throw new Error(`Unsupported docker compose service: ${options.service}`);
        }
        args.push(options.service);
      }
      return ['docker', args];
    }
    case 'down':
      return ['docker', ['compose', 'down']];
    default:
      return buildDockerComposeCommand(action, options);
  }
}

export function redactSensitiveText(value) {
  if (typeof value !== 'string' || value.length === 0) {
    return value;
  }

  return value
    .split(/\r?\n/)
    .map(line => {
      if (/^\s*authorization\s*:/i.test(line)) {
        return line.replace(/^(\s*authorization\s*:\s*).+$/i, '$1<redacted>');
      }

      if (
        /^\s*[\w.-]*(password|secret|token|api[_-]?key|consumer[_-]?key|consumer[_-]?secret|cookie)[\w.-]*\s*[:=]/i.test(
          line,
        )
      ) {
        return line.replace(/^(\s*[\w.-]+\s*[:=]\s*).+$/i, '$1<redacted>');
      }

      return line.replace(/\bBearer\s+[A-Za-z0-9._~+/=-]+/gi, 'Bearer <redacted>');
    })
    .join('\n');
}

export function describeSecurityMode(env = process.env) {
  return {
    mutationsEnabled: isMutationAllowed(env),
    fileWritesEnabled: isFileWriteAllowed(env),
    defaultExcludes: DEFAULT_EXCLUDES,
  };
}

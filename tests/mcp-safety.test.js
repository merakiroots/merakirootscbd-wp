import assert from 'node:assert/strict';
import { describe, it } from 'node:test';

import {
  assertAllowedReadPath,
  assertInsideWorkspace,
  buildDockerComposeCommand,
  isMutationAllowed,
  isReadOnlyWpCliArgs,
  redactSensitiveText,
} from '../mcp/lib/safety.js';

const root = process.cwd();

describe('workspace path safety', () => {
  it('allows normal relative source paths inside the workspace', () => {
    const resolved = assertInsideWorkspace(root, 'wp-content/plugins/my-plugin/my-plugin.php');

    assert.match(resolved, /wp-content[\\/]+plugins[\\/]+my-plugin[\\/]+my-plugin\.php$/);
  });

  it('rejects traversal outside the workspace', () => {
    assert.throws(() => assertInsideWorkspace(root, '../outside.txt'), /outside workspace/i);
  });

  it('rejects secret-like files even when they are inside the workspace', () => {
    assert.throws(() => assertAllowedReadPath(root, '.env'), /not readable/i);
  });
});

describe('command safety', () => {
  it('allows read-only WP-CLI commands by default', () => {
    assert.equal(isReadOnlyWpCliArgs(['plugin', 'list']), true);
    assert.equal(isReadOnlyWpCliArgs(['option', 'get', 'siteurl']), true);
  });

  it('classifies write-capable WP-CLI commands as mutations', () => {
    assert.equal(isReadOnlyWpCliArgs(['plugin', 'activate', 'woocommerce']), false);
    assert.equal(isReadOnlyWpCliArgs(['db', 'import', 'dump.sql']), false);
    assert.equal(isReadOnlyWpCliArgs(['eval', 'echo get_option("home");']), false);
  });

  it('requires an explicit environment switch for mutations', () => {
    assert.equal(isMutationAllowed({ MCP_ALLOW_MUTATIONS: 'true' }), true);
    assert.equal(isMutationAllowed({ MCP_ALLOW_MUTATIONS: '1' }), true);
    assert.equal(isMutationAllowed({ MCP_ALLOW_MUTATIONS: 'false' }), false);
    assert.equal(isMutationAllowed({}), false);
  });

  it('builds only known docker compose commands', () => {
    assert.deepEqual(buildDockerComposeCommand('ps'), ['docker', ['compose', 'ps']]);
    assert.deepEqual(buildDockerComposeCommand('logs', { service: 'wordpress', tail: 20 }), [
      'docker',
      ['compose', 'logs', '--tail=20', 'wordpress'],
    ]);
    assert.throws(() => buildDockerComposeCommand('exec'), /Unsupported docker compose action/);
  });
});

describe('output hygiene', () => {
  it('redacts env-style secrets from command output', () => {
    const redacted = redactSensitiveText('DB_PASSWORD=hunter2\nAuthorization: Bearer abc123\nnormal=value');

    assert.match(redacted, /DB_PASSWORD=<redacted>/);
    assert.match(redacted, /Authorization: <redacted>/);
    assert.match(redacted, /normal=value/);
  });
});

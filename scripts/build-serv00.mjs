import { spawnSync } from 'node:child_process';

const siteUrl = (process.env.SITE_URL || '').trim();
if (!/^https?:\/\//i.test(siteUrl)) {
  console.error('请先设置 SITE_URL，例如 https://blog.example.com');
  process.exit(1);
}

const env = {
  ...process.env,
  SITE_BASE: '/',
  SITE_URL: siteUrl.replace(/\/$/, ''),
  SITE_DOMAIN: ''
};
const isWindows = process.platform === 'win32';
const result = spawnSync(isWindows ? 'npm.cmd' : 'npm', ['run', 'build'], {
  env,
  stdio: 'inherit',
  shell: isWindows
});

process.exit(result.status ?? 1);

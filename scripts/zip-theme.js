// Packs theme/toplist-khanh-hoa into dist/toplist-khanh-hoa.zip for Appearance → Themes → Upload.
// Uses bsdtar (bundled with Windows 10+ and macOS), which writes zip archives with -a.
import { execFileSync } from 'node:child_process';
import { mkdirSync, rmSync, existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const out = path.join(root, 'dist', 'toplist-khanh-hoa.zip');

if (!existsSync(path.join(root, 'theme', 'toplist-khanh-hoa', 'assets', 'css', 'main.css'))) {
  throw new Error('Missing compiled CSS — run "npm run build:css" first.');
}

mkdirSync(path.dirname(out), { recursive: true });
rmSync(out, { force: true });
// On Windows, Git Bash puts GNU tar (no zip support) first on PATH; use the system bsdtar instead.
const tar = process.platform === 'win32' ? path.join(process.env.SystemRoot || 'C:\\Windows', 'System32', 'tar.exe') : 'tar';
execFileSync(tar, ['-a', '-c', '-f', out, '-C', path.join(root, 'theme'), 'toplist-khanh-hoa'], { stdio: 'inherit' });
console.log(`Theme zip → ${path.relative(root, out)}`);

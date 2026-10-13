import { mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const docs = path.join(root, 'docs');
const output = path.join(root, 'src', 'content', 'posts');
const base = (process.env.SITE_BASE || '/my-blog').replace(/\/$/, '');
const slash = value => value.split(path.sep).join('/');

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) files.push(...await walk(absolute));
    else files.push(absolute);
  }
  return files;
}

function makeSlug(relative) {
  const parts = relative.replace(/\\/g, '/').replace(/\.md$/i, '').split('/');
  if (parts.at(-1)?.toLowerCase() === 'index') parts.pop();
  return parts.map(part => part.toLowerCase()
    .replace(/\+/g, '-plus-')
    .replace(/\s+/g, '-')
    .replace(/[^\p{L}\p{N}_-]+/gu, '')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')).filter(Boolean).join('/');
}

function titleOf(source, relative) {
  const heading = source.match(/^#\s+(.+)$/m)?.[1];
  return (heading || path.basename(relative, '.md')).replace(/\*\*/g, '').replace(/\*/g, '').trim();
}

function summaryOf(source, title) {
  const tripleTick = String.fromCharCode(96).repeat(3);
  const codeBlock = new RegExp(tripleTick + '[\\s\\S]*?' + tripleTick, 'g');
  const text = source.replace(/^---[\s\S]*?---\s*/m, '')
    .replace(codeBlock, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/!?\[([^\]]*)\]\([^)]*\)/g, '$1')
    .replace(/^#{1,6}\s+/gm, '')
    .replace(/[>*_~|]/g, ' ')
    .replace(/\s+/g, ' ').trim();
  return (text.replace(title, '').trim() || '整理自原有学习与项目资料。').slice(0, 180);
}

function categoryOf(relative) {
  if (relative.startsWith('编程语言/C语言/')) return 'C 语言';
  if (relative.startsWith('编程语言/Python/')) return 'Python';
  if (relative.startsWith('编程语言/Linux基础/')) return 'Linux';
  if (relative.startsWith('职业成长/')) return '职业成长';
  if (relative.startsWith('项目实战/')) return '项目实战';
  if (relative.startsWith('内容索引/')) return '内容索引';
  return '学习笔记';
}

function tagsOf(relative, category) {
  const tags = [category];
  if (/esp32/i.test(relative)) tags.push('ESP32-S3');
  if (/python/i.test(relative)) tags.push('Python');
  if (/linux/i.test(relative)) tags.push('Linux');
  if (/操作系统/.test(relative)) tags.push('操作系统');
  return [...new Set(tags)];
}

const files = (await walk(docs)).filter(file => file.toLowerCase().endsWith('.md'));
const entries = [];
for (const file of files) {
  const relative = slash(path.relative(docs, file));
  if (relative.toLowerCase() === 'index.md') continue;
  const raw = await readFile(file, 'utf8');
  entries.push({
    file, relative, raw, slug: makeSlug(relative),
    kind: path.basename(relative).toLowerCase() === 'index.md' ? 'index' : 'article',
    title: titleOf(raw, relative)
  });
}

const slugBySource = new Map(entries.map(entry => [entry.relative, entry.slug]));
function destination(href, sourceRelative) {
  const clean = href.split(/[?#]/, 1)[0];
  if (!clean || /^(?:[a-z]+:|\/\/|#)/i.test(clean)) return href;
  let decoded;
  try { decoded = decodeURIComponent(clean); } catch { decoded = clean; }
  const resolved = path.posix.normalize(path.posix.join(path.posix.dirname(sourceRelative), decoded));
  const normalized = resolved.replace(/^\.\//, '');
  let target = normalized;
  if (!path.posix.extname(normalized)) target = path.posix.join(normalized, 'index.md');
  if (target.toLowerCase().endsWith('.md')) {
    const slug = slugBySource.get(target);
    if (slug !== undefined) {
      const suffix = href.slice(clean.length);
      const url = base + '/archives/' + slug.split('/').map(encodeURIComponent).join('/') + '/';
      return url + suffix;
    }
  }
  const resource = base + '/资料/' + normalized.split('/').map(encodeURIComponent).join('/');
  return resource + href.slice(clean.length);
}

function rewriteLinks(text, sourceRelative) {
  return text
    .replace(/(!?\[[^\]]*\]\()([^)]+)(\))/g, (all, before, target, after) => {
      const trimmed = target.trim();
      const titleMatch = trimmed.match(/^(.*?)(\s+["'][^"']*["'])$/);
      const linkTarget = titleMatch ? titleMatch[1] : trimmed;
      const title = titleMatch ? titleMatch[2] : '';
      const updated = destination(linkTarget, sourceRelative);
      return before + updated + title + after;
    })
    .replace(/\b(href|src)=(["'])(.*?)\2/gi, (all, attribute, quote, target) =>
      attribute + '=' + quote + destination(target, sourceRelative) + quote);
}

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
for (const entry of entries) {
  const category = categoryOf(entry.relative);
  const summary = summaryOf(entry.raw, entry.title);
  const body = rewriteLinks(entry.raw, entry.relative);
  const frontmatter = [
    '---',
    'title: ' + JSON.stringify(entry.title),
    'slug: ' + JSON.stringify(entry.slug),
    'category: ' + JSON.stringify(category),
    'tags: ' + JSON.stringify(tagsOf(entry.relative, category)),
    'summary: ' + JSON.stringify(summary),
    'kind: ' + JSON.stringify(entry.kind),
    'source: ' + JSON.stringify(entry.relative),
    '---',
    ''
  ].join('\n');
  const target = path.join(output, entry.relative);
  await mkdir(path.dirname(target), { recursive: true });
  await writeFile(target, frontmatter + body, 'utf8');
}
console.log('已整理 ' + entries.length + ' 篇 Markdown 内容；未修改 docs 原件。');

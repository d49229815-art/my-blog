import { cp, mkdir, readdir } from 'node:fs/promises';
import path from 'node:path';

const root = process.cwd();
const docs = path.join(root, 'docs');
const targetRoot = path.join(root, 'public', '资料');

async function copyTree(source, relative = '') {
  await mkdir(path.join(targetRoot, relative), { recursive: true });
  for (const entry of await readdir(source, { withFileTypes: true })) {
    const sourcePath = path.join(source, entry.name);
    const rel = path.join(relative, entry.name);
    if (entry.isDirectory()) await copyTree(sourcePath, rel);
    else if (path.extname(entry.name).toLowerCase() !== '.md') {
      await cp(sourcePath, path.join(targetRoot, rel));
    }
  }
}

await copyTree(docs);
console.log('原始代码示例、HTML 页面及附件已复制到静态资料区。');

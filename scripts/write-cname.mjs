import { mkdir, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';

const publicDir = path.join(process.cwd(), 'public');
await mkdir(publicDir, { recursive: true });
const domain = (process.env.SITE_DOMAIN || '').trim();
const cname = path.join(publicDir, 'CNAME');
if (domain) await writeFile(cname, domain + '\n', 'utf8');
else await rm(cname, { force: true });

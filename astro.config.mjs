import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';

export default defineConfig({
  site: process.env.SITE_URL || 'https://d49229815-art.github.io',
  base: process.env.SITE_BASE || '/my-blog',
  trailingSlash: 'always',
  integrations: [sitemap()],
  markdown: {
    remarkPlugins: [remarkMath],
    rehypePlugins: [rehypeKatex],
    shikiConfig: {
      themes: { light: 'min-light', dark: 'github-dark' },
      langs: ['c', 'cpp', 'python', 'bash', 'json', 'yaml', 'javascript', 'typescript', 'html', 'css', 'verilog', 'make', 'diff', 'text'],
      langAlias: { python3: 'python' }
    }
  }
});

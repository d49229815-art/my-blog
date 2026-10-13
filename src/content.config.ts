import { defineCollection, z } from 'astro:content';
import { glob } from 'astro/loaders';

const posts = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/posts' }),
  schema: z.object({
    title: z.string(),
    slug: z.string(),
    category: z.string(),
    tags: z.array(z.string()).default([]),
    summary: z.string().default(''),
    kind: z.enum(['article', 'index']).default('article'),
    source: z.string()
  })
});

export const collections = { posts };

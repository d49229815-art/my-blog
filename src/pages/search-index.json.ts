import type { APIRoute } from 'astro';
import { getCollection } from 'astro:content';
import rawFeels from '../data/feels.json';
import { postPath, sitePath } from '../data/site';

interface Feel { id: string | number; title: string; content: string; date: string; type?: string }
const feels = rawFeels as Feel[];

export const GET: APIRoute = async () => {
  const posts = await getCollection('posts');
  const index = [
    ...posts.map(post => ({
      title: post.data.title,
      category: post.data.category,
      tags: post.data.tags,
      summary: post.data.summary,
      content: (post.body || '').replace(/[#>*_~|]/g, ' ').replace(/\s+/g, ' ').slice(0, 12000),
      url: postPath(post.data.slug)
    })),
    ...feels.map(note => ({
      title: note.title,
      category: '说说',
      tags: ['FIELD NOTE'],
      summary: note.content.slice(0, 180),
      content: note.content,
      url: sitePath('feel/' + encodeURIComponent(String(note.id)) + '/')
    }))
  ];
  return new Response(JSON.stringify(index), {
    headers: { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'public, max-age=300' }
  });
};

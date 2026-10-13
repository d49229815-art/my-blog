import rss from '@astrojs/rss';
import { getCollection } from 'astro:content';
import rawFeels from '../data/feels.json';
import { site, postPath, sitePath } from '../data/site';

interface Feel { id: string | number; title: string; content: string; date: string }
const feels = rawFeels as Feel[];

export async function GET(context) {
  const posts = await getCollection('posts', ({ data }) => data.kind === 'article');
  const items = [
    ...posts.map(post => ({
      title: post.data.title,
      description: post.data.summary,
      link: new URL(postPath(post.data.slug), context.site).toString()
    })),
    ...feels.map(note => ({
      title: note.title,
      description: note.content.slice(0, 180),
      link: new URL(sitePath('feel/' + encodeURIComponent(String(note.id)) + '/'), context.site).toString()
    }))
  ];
  return rss({ title: site.name, description: site.description, site: context.site, items });
}

export const site = {
  name: '阿德小屋',
  subtitle: '嵌入式开发记录',
  description: '记录 C、MCU、Linux 和项目开发中的方案、代码与排错过程。',
  author: '阿德',
  intro: '从板级调试，到系统实践。',
  github: 'https://github.com/d49229815-art',
  email: ''
};

const basePath = import.meta.env.BASE_URL.replace(/\/$/, '');
export const sitePath = (path = '') => basePath + '/' + path.replace(/^\/+/, '');
export const postPath = (slug: string) => sitePath('archives/' + slug.split('/').map(encodeURIComponent).join('/') + '/');
export const resourcePath = (path: string) => sitePath('资料/' + path.split('/').map(encodeURIComponent).join('/'));

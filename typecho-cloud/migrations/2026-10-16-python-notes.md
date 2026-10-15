# Python notes consolidation — 2026-10-16

## Scope

The live site runs Typecho at `/var/www/typecho`. Python article data is stored in the `typecho` database. The article text is tracked in `../content/python-learning-notes.md`; server credentials and database dumps are excluded from Git.

## Live article changes

- Updated CID 19, `Python 学习笔记总览`, with a corrected, self-contained Markdown overview and in-page chapter navigation.
- Updated CID 9, `Python 学习资料`, so its links lead to the consolidated overview and the existing basics supplement.
- Updated CID 4, `编程语言与系统基础`, to remove links to redundant Python chapter pages.
- Updated CID 22, `Python 基础补充`, to remove its link to the retired chapter index while preserving its official-course note.
- Moved CIDs 10–17, the eight individual course chapters already covered by CID 19, to the site's trash.
- Moved CID 18, the redundant `Python 基础与进阶` index, and stale course-progress entries CIDs 20, 21, and 24 to the site's trash.
- Preserved CID 22, `Python 基础补充`, and CID 23, its official-course note.
- Kept the overview under `编程语言` with the existing `Python` tag.

No comments were attached to the affected articles. The site backup was completed before changing the database and again after the changes.

## Source and database backup

Pre-change backup directory on the server:

~~~text
/var/backups/typecho/manual-pre-python-20261015T162627Z
~~~

It contains `site-code.tar.gz`, `database.sql.gz`, and `SHA256SUMS`. The archive includes the private runtime configuration and is restricted to root on the server. Do not copy it to GitHub.

The post-change backup directory is recorded in `latest-cloud-backup.txt`. It contains the updated Typecho database and source snapshot, is root-only, and has a verified SHA-256 manifest.

The Git snapshot under `../site/` was pulled from the live installation with `config.inc.php`, uploaded files, and runtime cache excluded.

## Verification record

- Confirmed the live article IDs, titles, statuses, categories, tags, body lengths, and comment counts over SSH before editing.
- Compared the eight published chapter notes against CID 19: all non-heading chapter content was already present in the overview.
- Reviewed and corrected examples and explanations that were wrong in the live overview, including Boolean operator casing, variable/object wording, mutability and hashability, dictionary access, `*args`, file handling, and the ATM sample.
- Confirmed the sanitized repository snapshot excludes the Typecho config, uploads, and cache directories.

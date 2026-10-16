# Python notes consolidation — 2026-10-16

## Scope

The live site runs Typecho at `/var/www/typecho`. Python article data is stored in the `typecho` database. The article text is tracked in `../content/python-learning-notes.md`; server credentials and database dumps are excluded from Git.

## Live article changes

- Updated CID 19, `Python 学习笔记总览`, with a corrected, self-contained Markdown overview and in-page chapter navigation.
- Updated CID 9, `Python 学习资料`, so its links lead to the consolidated overview and the existing basics supplement.
- Updated CID 4, `编程语言与系统基础`, to remove links to redundant Python chapter pages.
- Updated CID 22, `Python 基础补充`, to link to the newly consolidated copy and built-in-function sections in CID 19.
- Moved CIDs 10–17, the eight individual course chapters already covered by CID 19, to the site's trash.
- Moved CID 18, the redundant `Python 基础与进阶` index, stale course-progress entries CIDs 20, 21, and 24, and the source note CID 23 to the site's trash after merging its unique content into CID 19.
- Preserved CID 22, `Python 基础补充`, as a short index to the merged supplemental sections.
- Kept the overview under `编程语言` with the existing `Python` tag.

No comments were attached to the affected articles. The site backup was completed before changing the database and again after the changes.

## Source and database backup

Pre-change backup directory on the server:

~~~text
/var/backups/typecho/manual-pre-python-20261015T162627Z
~~~

It contains `site-code.tar.gz`, `database.sql.gz`, and `SHA256SUMS`. The archive includes the private runtime configuration and is restricted to root on the server. Do not copy it to GitHub.

Before the follow-up merge of CID 23, the then-current live state was also backed up at `/var/backups/typecho/manual-post-python-20261015T165339Z`. The final post-merge backup directory is recorded in `latest-cloud-backup.txt`; it contains the updated Typecho database and source snapshot, is root-only, and has a verified SHA-256 manifest.

The Git snapshot under `../site/` was pulled from the live installation with `config.inc.php`, uploaded files, and runtime cache excluded.

## Verification record

- Confirmed the live article IDs, titles, statuses, categories, tags, body lengths, and comment counts over SSH before editing.
- Compared the eight published chapter notes against CID 19: all non-heading chapter content was already present in the overview. Compared CID 23 and merged its unique object-copying and built-in-function material into CID 19.
- Reviewed and corrected examples and explanations that were wrong in the live overview and CID 23, including Boolean operator casing, variable/object wording, mutability and hashability, dictionary access, copy behavior, `zip()` syntax and return type, `*args`, file handling, and the ATM sample.
- Confirmed the sanitized repository snapshot excludes the Typecho config, uploads, and cache directories.

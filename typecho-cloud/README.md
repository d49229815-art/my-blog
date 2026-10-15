# Cloud Typecho package

This directory contains the Typecho source snapshot taken from the live Morrow blog and the cleaned Python learning notes prepared for its database.

## Contents

- `site/` is the live Typecho source tree, including the Argon Note theme and installed plugins.
- `content/python-learning-notes.md` is the revised text for the published article `Python 学习笔记总览` (Typecho CID 19).
- `content/` also contains the refreshed Python and programming index pages.
- `migrations/2026-10-16-python-notes.md` records the live content changes and backup locations.

The source snapshot omits `site/config.inc.php`, `site/usr/uploads/`, and `site/var/cache/`. The live database and private configuration are stored only in root-readable cloud backups. Do not commit credentials, database dumps, or uploaded files to this branch.

## Python note organization

The eight course chapters on syntax, conditions, loops, functions, containers, advanced functions, files, and exceptions/modules are consolidated into the overview. The separate course pages that duplicated those sections are moved to the site's trash after the overview is updated. The Python materials index is retained and points to the overview and the existing “Python 基础补充” material.

The “Python 基础补充” page and its official-course note remain available. The overview is assigned to the existing “编程语言” category with the `Python` tag.

## Cloud backup

The server keeps private snapshots under `/var/backups/typecho/`. Manual snapshots made before and after the database change include the complete site code and a compressed database dump; their directories are recorded in the migration note and `migrations/latest-cloud-backup.txt`. The code archives are mode `0600` in root-owned `0700` directories.

The repository snapshot intentionally excludes secrets and user uploads. Use the cloud backup when a full server-side recovery is needed.

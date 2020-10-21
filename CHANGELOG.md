# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
### Changed
### Removed
### Fixed

## [1.0.1] - 2020-10-21

### Added

- [BT-47] - Re-added German language support, based on a contribution by [Conny Henn](mailto:Conny@hennweb.de)

## [1.0.0] - 2020-09-14

### Added

- [BT-38] - When creating a backup file, we now ensure we don't backup any metadata about backups, and we also delete any "already deleted" backups in the `media_attic` that the user cannot otherwise delete
- [BT-44] - Added this CHANGELOG.md file and first official version number 1.0.0 and release date to 2020-09-14
- [BT-46] - Added LICENSE, rewrote README.md and some re-writing of how the backup page looks

### Changed

- [BT-39] - When the backup completes, we now hide the "backup progress" text
- [BT-42] - If a user is running on a Windows-based server, we now alert the user that there may be issues with the plugin, and force an "I Understand" button to be pressed to show the plugin
- [BT-43] - Did some code cleanup via `php-cs-fixer` and `js-beautify`; also removed German, French, and Japanese translations as they're now out of date and need to be redone

## 

[1.0.0]: https://github.com/tatewake/dokuwiki-plugin-backup/releases/tag/1.0.0
[1.0.1]: https://github.com/tatewake/dokuwiki-plugin-backup/releases/tag/1.0.0

[BT-39]: https://github.com/tatewake/dokuwiki-plugin-backup/issues/39
[BT-38]: https://github.com/tatewake/dokuwiki-plugin-backup/issues/38

[BT-42]: http://192.168.1.150/open-source/dokuwiki/backup/-/issues/42
[BT-43]: http://192.168.1.150/open-source/dokuwiki/backup/-/issues/43
[BT-44]: http://192.168.1.150/open-source/dokuwiki/backup/-/issues/44
[BT-46]: http://192.168.1.150/open-source/dokuwiki/backup/-/issues/46
[BT-47]: http://192.168.1.150/open-source/dokuwiki/backup/-/issues/47

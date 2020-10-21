# BackupTool for DokuWiki

## License

* **Author**: [Terence J. Grant](mailto:tjgrant@tatewake.com) (with special thanks to [Andreas Wagner](andreas.wagner@em.uni-frankfurt.de) and [Andreas Gohr](dokuwiki@cosmocode.de))
* **License**: [GNU GPL v2](http://opensource.org/licenses/GPL-2.0)
* **Latest Release**: v1.0.1 on Oct 21st, 2020
* **Changes**: See [CHANGELOG.md](CHANGELOG.md) for full details.
* **Donate**: [Donations](http://tjgrant.com/wiki/donate) and [Sponsorships](https://github.com/sponsors/tatewake) are appreciated!

## About

This tool allows you to easily create backups of your [DokuWiki](http://dokuwiki.org/) data and other configuration / settings from the admin interface.

The tool will create a [tar](https://en.wikipedia.org/wiki/Tar_(computing)) archive, and optionally compressed with either [bzip2](https://en.wikipedia.org/wiki/Bzip2) or [gzip](https://en.wikipedia.org/wiki/Gzip) if either of these compression methods are available.

## Install / Upgrade

Search and install the plugin using the [Extension Manager](https://www.dokuwiki.org/plugin:extension). Refer to [Plugins](https://www.dokuwiki.org/plugins) on how to install plugins manually.

You can then run backups manually from the **Backup Tool** link in the **Additional Plugins** section of your DokuWiki installation's **Admin** page.

Backup archives will be available via the **Media Manager** at the path `:wiki:backup`.

## Setup

All further documentation for this plugin can be found at:

 * [https://www.dokuwiki.org/plugin:backup](https://www.dokuwiki.org/plugin:backup)

## Contributing

The official repository for this plugin is available on GitHub:

* [https://github.com/tatewake/dokuwiki-plugin-backup](https://github.com/tatewake/dokuwiki-plugin-backup)

The plugin thrives from community contributions. If you're able to provide useful code changes or bug fixes, they will likely be accepted to future versions of the plugin.

If you find my work helpful and would like to give back, [consider joining me as a GitHub sponsor](https://github.com/sponsors/tatewake).

Thanks!

--Terence

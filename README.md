SunshineCMS Installers
======================

[![Latest Stable Version][icon-stable-version]][link-packagist]
[![Latest Untable Version][icon-unstable-version]][link-packagist]
[![Total Downloads][icon-downloads]][link-packagist]
[![License][icon-license]][link-license]
[![PHP][icon-php]][link-php]

[![Linux Build Status][icon-travis]][link-travis]
[![Windows Build Status][icon-appveyor]][link-appveyor]
[![Code Coverage][icon-coverage]][link-coverage]
[![Code Quality][icon-quality]][link-quality]

Composer Library Installer for SunshineCMS.

## Description

This package acts as composer plugin in order to download and install SunshineCMS core and extensions and put them into a directory structure which is suitable for SunshineCMS to work correctly.

## Usage

You should include `sunshinecms/installers` in your project and specify `type` of it. See below for supported types and naming.

This would install plugin to `plugins/example/system/` in website root directory:

```json
{
    "name": "example/sunshinecms-system-plugin",
    "type": "sunshinecms-plugin",
    "require": {
        "sunshinecms/installers": "^1.0"
    }
}
```

You should then submit your package to Packagist. In the future, SunshineCMS will probably have it's own extension repository so you wold also need to submit package there.

## Supported Types

| Type | Name | Location | Description |
|--|--|--|--|
| `sunshinecms-core` | core |  | Installer for core project. Only for forward compatibility and should not be currently not be used. |
| `sunshinecms-*-pack` | pack | `vendor/{$vendor}/{$name}/` | Installer for plugin and theme packs. It must contain type of extensions that are included in it. |
| `sunshinecms-plugin` | plugin | `plugins/{$vendor}/{$name}/` | Installer for plugins. |
| `sunshinecms-*-theme`| theme | `themes/{$type}/{$vendor}/{$name}/` | Installer for themes. It can contain type of theme. If it is not specified, theme will be public. |

## Package Naming

* Package should follow standard Composer and Packagist convention.
* Name will be automatically converted to `camel-case`.
* Leading and trailing word `sunshinecms` will be automatically removed if present.
* Type name will be automatically removed if present.

## Examples

| Package Type | Package Name | Location |
|--|--|--|
| `sunshinecms-plugin` | `example/sunshinecms-system-plugin` | `plugins/example/system/` |
| `sunshinecms-theme` | `example/sunshinecms-responsive-theme` | `themes/public/example/responsive/`|
| `sunshinecms-public-theme` | `example/sunshinecms-responsive-theme` | `themes/public/example/responsive/`|
| `sunshinecms-admin-theme` | `example/sunshinecms-responsive-theme` | `themes/admin/example/responsive/`|


## Versioning

This library uses [SemVer][link-semver] for versioning. For the versions available, see the [tags on this repository][link-tags].

## License

This library is licensed under the GPLv3+ license. See the [LICENSE][link-license-file] file for details.

[icon-stable-version]: https://img.shields.io/packagist/v/sunshinecms/installers.svg?style=flat-square&label=Latest+Stable+Version
[icon-unstable-version]: https://img.shields.io/packagist/vpre/sunshinecms/installers.svg?style=flat-square&label=Latest+Unstable+Version
[icon-downloads]: https://img.shields.io/packagist/dt/sunshinecms/installers.svg?style=flat-square&label=Downloads
[icon-license]: https://img.shields.io/packagist/l/sunshinecms/installers.svg?style=flat-square&label=License
[icon-php]: https://img.shields.io/packagist/php-v/sunshinecms/installers.svg?style=flat-square&label=PHP
[icon-travis]: https://img.shields.io/travis/com/SunshineCMS/ComposerInstallers.svg?style=flat-square&label=Linux+Build+Status
[icon-appveyor]: https://img.shields.io/appveyor/ci/SunshineCMS/ComposerInstallers.svg?style=flat-square&label=Windows+Build+Status
[icon-coverage]: https://img.shields.io/scrutinizer/coverage/g/SunshineCMS/ComposerInstallers.svg?style=flat-square&label=Code+Coverage
[icon-quality]: https://img.shields.io/scrutinizer/g/SunshineCMS/ComposerInstallers.svg?style=flat-square&label=Code+Quality

[link-packagist]: https://packagist.org/packages/sunshinecms/installers/
[link-license]: https://choosealicense.com/licenses/gpl-3.0/
[link-php]: https://php.net/
[link-travis]: https://travis-ci.com/SunshineCMS/ComposerInstallers/
[link-appveyor]: https://ci.appveyor.com/project/SunshineCMS/ComposerInstallers/
[link-coverage]: https://scrutinizer-ci.com/g/SunshineCMS/ComposerInstallers/code-structure/
[link-quality]: https://scrutinizer-ci.com/g/SunshineCMS/ComposerInstallers/
[link-semver]: https://semver.org/

[link-tags]: https://github.com/SunshineCMS/ComposerInstallers/tags/
[link-license-file]: https://github.com/SunshineCMS/ComposerInstallers/blob/master/LICENSE

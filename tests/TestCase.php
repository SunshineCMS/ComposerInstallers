<?php

namespace SunshineCMS\Installers\Tests;

use Composer\Package\Version\VersionParser;
use Composer\Package\Package;
use Composer\Package\AliasPackage;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var VersionParser */
    private static $parser;

    protected static function getVersionParser()
    {
        if (!self::$parser) {
            self::$parser = new VersionParser();
        }

        return self::$parser;
    }

    protected function getVersionConstraint($operator, $version)
    {
        return new VersionConstraint(
            $operator,
            self::getVersionParser()->normalize($version)
        );
    }

    protected function getPackage($name, $version)
    {
        $normVersion = self::getVersionParser()->normalize($version);

        return new Package($name, $normVersion, $version);
    }

    protected function getAliasPackage($package, $version)
    {
        $normVersion = self::getVersionParser()->normalize($version);

        return new AliasPackage($package, $normVersion, $version);
    }

    protected function ensureDirectoryExistsAndClear($directory)
    {
        $fs = new Filesystem();
        if (is_dir($directory)) {
            $fs->removeDirectory($directory);
        }
        mkdir($directory, 0777, true);
    }
}

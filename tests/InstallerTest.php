<?php

// phpcs:disable Squiz.Arrays.ArrayDeclaration

namespace SunshineCMS\Installers\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Util\Filesystem;
use SunshineCMS\Installers\Installer;

class InstallerTest extends TestCase
{
    /** @var Compsoer */
    private $composer;

    /** @var Config */
    private $config;

    /** @var string */
    private $vendorDir;

     /** @var string */
    private $binDir;

    /** @var Composer\Downloader\DownloadManager */
    private $dm;

    /** @var Composer\Repository\InstalledRepositoryInterface */
    private $repository;

    /** @var Composer\IO\IOInterface */
    private $io;

    /** @var Filesystem */
    private $fs;

    public function setUp()
    {
        $this->fs = new Filesystem;

        $this->composer = new Composer;
        $this->config = new Config;
        $this->composer->setConfig($this->config);

        $this->vendorDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'baton-test-vendor';
        $this->ensureDirectoryExistsAndClear($this->vendorDir);

        $this->binDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'baton-test-bin';
        $this->ensureDirectoryExistsAndClear($this->binDir);

        $this->config->merge(
            [
                'config' => [
                    'vendor-dir' => $this->vendorDir,
                    'bin-dir' => $this->binDir,
                ],
            ]
        );

        $this->dm = $this->getMockBuilder('Composer\Downloader\DownloadManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->composer->setDownloadManager($this->dm);

        $this->repository = $this->getMockBuilder('Composer\Repository\InstalledRepositoryInterface')->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();

        $consumerPackage = new RootPackage('foo/bar', '1.0.0', '1.0.0');
        $this->composer->setPackage($consumerPackage);
    }

    public function tearDown()
    {
        $this->fs->removeDirectory($this->vendorDir);
        $this->fs->removeDirectory($this->binDir);
    }

    /**
     * @dataProvider dataForTestSupport
     */
    public function testSupports($type, $expected)
    {
        $installer = new Installer($this->io, $this->composer);
        $this->assertSame($expected, $installer->supports($type), sprintf('Failed to show support for %s', $type));
    }

    public function dataForTestSupport()
    {
        return [
            ['sunshinecms-core', true],

            ['sunshinecms-plugin', true],

            ['sunshinecms-theme', true],
            ['sunshinecms-public-theme', true],
            ['sunshinecms-admin-theme', true],
            ['sunshinecms-example-theme', true],

            ['sunshinecms-plugin-pack', true],
            ['sunshinecms-theme-pack', true],
            ['sunshinecms-example-pack', true],
        ];
    }


    /**
     * @dataProvider dataForTestInstallPath
     */
    public function testInstallPath($type, $path, $name, $version = '1.0.0')
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package($name, $version, $version);

        $package->setType($type);
        $result = $installer->getInstallPath($package);
        $this->assertEquals($path, $result);
    }

    public function dataForTestInstallPath()
    {
        return [
            ['sunshinecms-core', '', 'sunshinecms/sunshinecms'],

            ['sunshinecms-plugin', 'plugins/example/system/', 'example/system'],
            ['sunshinecms-plugin', 'plugins/example/system/', 'example/system-plugin'],
            ['sunshinecms-plugin', 'plugins/example/system/', 'example/sunshinecms-system'],
            ['sunshinecms-plugin', 'plugins/example/system/', 'example/sunshinecms-system-plugin'],

            ['sunshinecms-public-theme', 'themes/public/example/responsive/', 'example/responsive'],
            ['sunshinecms-admin-theme', 'themes/admin/example/responsive/', 'example/responsive'],
            ['sunshinecms-example-theme', 'themes/example/example/responsive/', 'example/responsive'],

            ['sunshinecms-theme', 'themes/public/example/responsive/', 'example/responsive'],
            ['sunshinecms-theme', 'themes/public/example/responsive/', 'example/responsive-theme'],
            ['sunshinecms-theme', 'themes/public/example/responsive/', 'example/sunshinecms-responsive'],
            ['sunshinecms-theme', 'themes/public/example/responsive/', 'example/sunshinecms-responsive-theme'],

            ['sunshinecms-plugin-pack', 'vendor/example/system/', 'example/system/'],
            ['sunshinecms-theme-pack', 'vendor/example/responsive/', 'example/responsive/'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUnsupportedBaseType()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('faketype-plugin');
        $result = $installer->getInstallPath($package);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUnsupportedPathType()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-faketype');
        $result = $installer->getInstallPath($package);
    }

    public function testCustomInstallPath()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');
        $this->composer->getPackage()->setExtra(
            [
                'installer-paths' => [
                    'my/custom/path/{$name}/' => [
                        'example/system',
                        'foo/bar',
                    ],
                ],
            ]
        );

        $result = $installer->getInstallPath($package);
        $this->assertEquals('my/custom/path/system/', $result);
    }

    public function testCustomVendorPath()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');
        $this->composer->getPackage()->setExtra(
            [
                'installer-paths' => [
                    'my/custom/path/{$name}/' => [
                        'vendor:example'
                    ],
                ],
            ]
        );

        $result = $installer->getInstallPath($package);
        $this->assertEquals('my/custom/path/system/', $result);
    }

    public function testCustomTypePath()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');
        $this->composer->getPackage()->setExtra(
            [
                'installer-paths' => [
                    'my/custom/path/{$name}/' => [
                        'type:sunshinecms-plugin'
                    ],
                ],
            ]
        );

        $result = $installer->getInstallPath($package);
        $this->assertEquals('my/custom/path/system/', $result);
    }

    public function testCustomUnusedPath()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');
        $this->composer->getPackage()->setExtra(
            [
                'installer-paths' => [
                    'my/custom/path/{$name}/' => [
                        'type:sunshinecms-faketype'
                    ],
                ],
            ]
        );

        $result = $installer->getInstallPath($package);
        $this->assertEquals('plugins/example/system/', $result);
    }

    public function testCustomInstallerName()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('example/system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');
        $package->setExtra(
            [
                'installer-name' => 'system-control',
            ]
        );

        $result = $installer->getInstallPath($package);
        $this->assertEquals('plugins/example/system-control/', $result);
    }

    public function testNoVendorName()
    {
        $installer = new Installer($this->io, $this->composer);
        $package = new Package('system', '1.0.0', '1.0.0');

        $package->setType('sunshinecms-plugin');

        $result = $installer->getInstallPath($package);
        $this->assertEquals('plugins/system/', $result);
    }

    /**
     * @dataProvider dataForTestDisabledInstallers
     */
    public function testDisabledInstallers($disabled, $type, $expected)
    {
        $this->composer->getPackage()->setExtra(
            [
                'installer-disable' => $disabled,
            ]
        );
        $this->testSupports($type, $expected);
    }

    public function dataForTestDisabledInstallers()
    {
        return [
            [false, 'sunshinecms-plugin', true],

            [true, 'sunshinecms-plugin', false],
            ['true', 'sunshinecms-plugin', true],
            ['all', 'sunshinecms-plugin', false],
            ['*', 'sunshinecms-plugin', false],

            ['unexisting', 'sunshinecms-plugin', true],

            ['plugin', 'sunshinecms-plugin', false],
            ['plugin', 'sunshinecms-theme', true],

            [['plugin', 'unexisting'], 'sunshinecms-plugin', false],
            [['plugin', 'unexisting'], 'sunshinecms-theme', true],

            [['unexisting', true], 'sunshinecms-plugin', false],
            [['unexisting', 'all'], 'sunshinecms-plugin', false],
            [['unexisting', '*'], 'sunshinecms-plugin', false],

            [['sunshinecms', 'true'], 'sunshinecms-plugin', true],
        ];
    }
}

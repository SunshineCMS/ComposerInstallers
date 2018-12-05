<?php


namespace SunshineCMS\Installers;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer Library Installer Plugin for SunshineCMS.
 *
 * Handles the installation of SunshineCMS core, plugins,
 * themes and other extensions.
 *
 * @since 1.0.0
 *
 * @author  SunshineCMS Authors & Developers
 * @license GPL-3.0-or-later
 *
 * @package SunshineCMS\Installers
 */
class Plugin implements PluginInterface
{
    /**
     * Activates installer.
     *
     * Activates the installer plugin for SunshineCMS.
     *
     * @param Composer             $composer
     * @param IOInterface          $io
     *
     * @retutrn void
     *
     * @codeCoverageIgnore
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}

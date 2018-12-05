<?php

namespace SunshineCMS\Installers;

use Composer\Composer;
use Composer\Installer\BinaryInstaller;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use InvalidArgumentException;

/**
 * Composer Library Installer Core for SunshineCMS.
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
class Installer extends LibraryInstaller
{
    /**
     * Stores base name.
     *
     * @var string
     */
    protected $name = 'sunshinecms';

    /**
     * Stores installation locations.
     *
     * @var array
     */
    protected $locations = [
        // SunshineCMS Core
        'core' => '',

        // SunshineCMS Packs
        '.*(?=-pack)' => 'vendor/{$vendor}/{$name}/',

        // SunshineCMS Plugins
        'plugin' => 'plugins/{$vendor}/{$name}/',

        // SunshineCMS Themes
        'theme' => 'themes/public/{$vendor}/{$name}/',
        '.*(?=-theme)' => 'themes/{$type}/{$vendor}/{$name}/',
    ];

    /**
     * Constructs installer.
     *
     * Constructs the installer core for SunshineCMS or disables it when
     * specified in main composer extra installer disable list.
     *
     * @param IOInterface          $io
     * @param Composer             $composer
     * @param string               $type
     * @param Filesystem|null      $filesystem
     * @param BinaryInstaller|null $binaryInstaller
     */
    public function __construct(
        IOInterface $io,
        Composer $composer,
        $type = 'library',
        Filesystem $filesystem = null,
        BinaryInstaller $binaryInstaller = null
    ) {
        parent::__construct($io, $composer, $type, $filesystem, $binaryInstaller);
        $this->removeDisabledInstallers();
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        // Get type
        $type = $package->getType();

        // Get vendor and name
        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            $vendor = '';
            $name = $prettyName;
        }

        // Check if supported
        if (!$this->supports($type)) {
            throw new InvalidArgumentException('Package type of this package is not supported');
        }

        // Inflect package vars
        $availableVars = $this->inflectPackageVars(compact('name', 'vendor', 'type'));

        // Get different name if available
        $extra = $package->getExtra();
        if (!empty($extra['installer-name'])) {
            $availableVars['name'] = $extra['installer-name'];
        }

        // Return custom location
        if ($this->composer->getPackage()) {
            $extra = $this->composer->getPackage()->getExtra();
            if (!empty($extra['installer-paths'])) {
                $customPath = $this->mapCustomInstallPaths($extra['installer-paths'], $prettyName, $type, $vendor);
                if ($customPath !== false) {
                    return $this->templatePath($availableVars, $customPath);
                }
            }
        }

        // Return location
        return $this->templatePath($availableVars);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($type)
    {
        // Check base name
        if ($this->name !== substr($type, 0, strlen($this->name))) {
            return false;
        }

        // Check type name
        foreach ($this->locations as $key => $value) {
            preg_match('/' . $key . '/', $type, $matches, PREG_OFFSET_CAPTURE, 0);

            if (!empty($matches)) {
                return true;
            }
        }

        // Return if not supported
        return false;
    }

    /**
     * Removes disabled installers.
     *
     * Looks for disabled installers in composer's extra config
     * and remove them.
     *
     * @return void
     */
    protected function removeDisabledInstallers()
    {
        // Get extra config
        $extra = $this->composer->getPackage()->getExtra();

        // Check if installers need to be disabled
        if (!isset($extra['installer-disable']) || $extra['installer-disable'] === false) {
            return;
        }

        // Get installers to disable
        $disable = $extra['installer-disable'];

        // Ensure $disabled is an array
        if (!is_array($disable)) {
            $disable = [$disable];
        }

        // Check which installers should be disabled
        $all = [
            true,
            'all',
            '*',
        ];
        $intersect = array_intersect($all, $disable);

        if (!empty($intersect)) {
            // Disable all installers
            $this->locations = [];
        } else {
            // Disable specified installers
            foreach ($disable as $key => $installer) {
                if (is_string($installer) && key_exists($installer, $this->locations)) {
                    unset($this->locations[$installer]);
                }
            }
        }
    }

    /**
     * Formats package name.
     *
     * Lowercases name and changes underscores to hyphens. Also
     * cuts off leading or trailing `sunshinecms`, `plugin`,
     * `theme` or `pack` from name depending on type.
     *
     * @param array $vars
     *
     * @return array
     */
    protected function inflectPackageVars($vars)
    {
        // Lowercase and change underscores to hyphens
        $vars['name'] = strtolower(str_replace('_', '-', $vars['name']));

        // Remove base name
        $vars['name'] = preg_replace('/-' . $this->name . '/', '', $vars['name']);
        $vars['name'] = preg_replace('/' . $this->name . '-/', '', $vars['name']);

        // Remove type name
        foreach ($this->locations as $key => $value) {
            if ($vars['type'] === $this->name . '-' . $key) {
                $vars['name'] = preg_replace('/[-]?' . $key . '[-]?/', '', $vars['name']);
                break;
            }
        }

        // Return formatted name
        return $vars;
    }

    /**
     * Searchs for install path.
     *
     * Searchs through a passed paths array for a custom install path.
     *
     * @param array  $paths
     * @param string $name
     * @param string $type
     * @param string $vendor = null
     *
     * @return string
     */
    protected function mapCustomInstallPaths(array $paths, $name, $type, $vendor = null)
    {
        // Search in paths array
        foreach ($paths as $path => $names) {
            if (in_array($name, $names) || in_array('type:' . $type, $names) || in_array('vendor:' . $vendor, $names)) {
                return $path;
            }
        }

        // Return if not found
        return false;
    }

    /**
     * Replaces vars in a path.
     *
     * Replaces vars for type, vendor and name with their calculated
     * values from package details.
     *
     * @param array $vars
     *
     * @return string
     */
    protected function templatePath(array $vars, $path = '')
    {
        // Export vars
        extract($vars);

        if (!empty($path)) {
            // Insert vendor and name
            preg_match_all('@\{\$([A-Za-z0-9_]*)\}@i', $path, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $var) {
                    $path = str_replace('{$' . $var . '}', $$var, $path);
                }
            }

            // Return template path
            return preg_replace('#/+#', '/', $path);
        }

        // Search for path
        foreach ($this->locations as $key => $value) {
            if (preg_match('/(?<=' . $this->name . '-)' . $key . '/', $type)) {
                if (strpos($value, '{') !== false) {
                    // Match type
                    preg_match(
                        '/(?<=' . $this->name . '-)' . $key . '/',
                        $type,
                        $type,
                        PREG_OFFSET_CAPTURE,
                        0
                    );
                    $type = $type[0][0];

                    // Insert vendor and name
                    preg_match_all('@\{\$([A-Za-z0-9_]*)\}@i', $value, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $var) {
                            $value = str_replace('{$' . $var . '}', $$var, $value);
                        }
                    }
                }

                // Return template path
                return preg_replace('#/+#', '/', $value);
            }
        }
    }
}

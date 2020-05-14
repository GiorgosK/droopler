<?php

namespace Drupal\d_content_init\Services;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class ConfigUpdate.
 *
 * @package Drupal\d_commerce\Services
 */
class ConfigUpdate {

  /**
   * Configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Manages modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * ConfigUpdate constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   Module handler.
   */
  public function __construct(ConfigFactory $configFactory, ModuleHandler $moduleHandler) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Searches for all files in the given path that contain in the name the
   * value specified in the variable $configName. Then, in the configurations
   * found, it changes the value of the theme option to the default theme.
   *
   * @param string $moduleName
   *   Module name.
   * @param string $path
   *   Directory path with configs.
   * @param string $regex
   *   Regular expresion pattern to search in configuration file names.
   *
   * @return array
   *   Array of configs.
   */
  public function getConfigs($moduleName, $path, $regex) {
    $configs = [];
    if ($this->moduleHandler->moduleExists($moduleName)) {
      $dir = $this->moduleHandler->getModule($moduleName)->getPath();
      if (file_exists($dir)) {
        $files = scandir($dir . $path);
        foreach ($files as $file) {
          if (preg_match($regex, $file) === 1) {
            $configs[] = substr($file, 0, -4);
          }
        }
      }
    }
    return $configs;
  }

  /**
   * Set default theme for configs.
   *
   * @param array $configs
   *   Array of configuration file names.
   */
  public function setTheme(array $configs) {
    $theme = $this->configFactory->get('system.theme')->get('default');
    foreach ($configs as $config) {
      $configToUpdate = $this->configFactory->getEditable($config);
      if (!$configToUpdate->isNew()) {
        $configToUpdate->set('theme', $theme)
          ->set('dependencies.theme', $theme)
          ->save();
      }
    }
  }

  /**
   * Changes the value of the theme option to the default theme.
   *
   * @param string $moduleName
   *   Module name.
   * @param string $path
   *   Directory path with configs.
   * @param string $regex
   *   Regular expresion pattern to search in configuration file names.
   */
  public function updateThemeInConfigs($moduleName, $path, $regex) {
    $this->setTheme($this->getConfigs($moduleName, $path, $regex));
  }

  /**
   * Delete configs.
   *
   * @param string $moduleName
   *   Module name.
   * @param string $path
   *   Directory path with configs.
   * @param string $regex
   *   Regular expresion pattern to search in configuration file names.
   */
  public function deleteConfigs($moduleName, $path, $regex) {
    $configs = $this->getConfigs($moduleName, $path, $regex);
    foreach ($configs as $config) {
      $this->configFactory->getEditable($config)->delete();
    }
  }

}

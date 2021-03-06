<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien M?nager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib;

/**
 * Autoloads FontLib classes
 *
 * @package phpFontLib
 */
class Autoloader {
  const PREFIX = 'FontLib';

  /**
   * Register the autoloader
   */
  public static function register() {
    spl_autoload_register(array(new self, 'autoload'));
  }

  /**
   * Autoloader
   *
   * @param string
   */
  public static function autoload($class) {
    $prefixLength = strlen(self::PREFIX);
    if (0 === strncmp(self::PREFIX, $class, $prefixLength)) {
      $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
      $file = realpath(__DIR__ . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
      if (file_exists($file)) {
        require_once $file;
      }
    }
  }
}

Autoloader::register();
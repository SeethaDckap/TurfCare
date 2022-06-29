<?php
/**
 * @package phpSvgLib
 * @link    http://github.com/PhenX/phpSvgLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

spl_autoload_register(function($class) {
  if (0 === strpos($class, "Svg")) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = realpath(__DIR__ . DIRECTORY_SEPARATOR . $file . '.php');
    if (file_exists($file)) {
      include_once $file;
    }
  }
});
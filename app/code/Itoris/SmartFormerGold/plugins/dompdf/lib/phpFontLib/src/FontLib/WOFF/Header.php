<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\WOFF;

/**
 * WOFF font file header.
 *
 * @package phpFontLib
 */
class Header extends \Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\TrueType\Header {
  protected $def = array(
    "format"         => self::uint32,
    "flavor"         => self::uint32,
    "length"         => self::uint32,
    "numTables"      => self::uint16,
    self::uint16,
    "totalSfntSize"  => self::uint32,
    "majorVersion"   => self::uint16,
    "minorVersion"   => self::uint16,
    "metaOffset"     => self::uint32,
    "metaLength"     => self::uint32,
    "metaOrigLength" => self::uint32,
    "privOffset"     => self::uint32,
    "privLength"     => self::uint32,
  );
}
<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib;

use Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\TrueType\File;

/**
 * Font header container.
 *
 * @package phpFontLib
 */
abstract class Header extends BinaryStream {
  /**
   * @var File
   */
  protected $font;
  protected $def = array();

  public $data;

  public function __construct(File $font) {
    $this->font = $font;
  }

  public function encode() {
    return $this->font->pack($this->def, $this->data);
  }

  public function parse() {
    $this->data = $this->font->unpack($this->def);
  }
}
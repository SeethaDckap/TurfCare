<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: Font_Table_glyf.php 46 2012-04-02 20:22:38Z fabien.menager $
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\Glyph;
/**
 * Glyph outline component
 *
 * @package phpFontLib
 */
class OutlineComponent {
  public $flags;
  public $glyphIndex;
  public $a, $b, $c, $d, $e, $f;
  public $point_compound;
  public $point_component;
  public $instructions;

  function getMatrix() {
    return array(
      $this->a, $this->b,
      $this->c, $this->d,
      $this->e, $this->f,
    );
  }
}
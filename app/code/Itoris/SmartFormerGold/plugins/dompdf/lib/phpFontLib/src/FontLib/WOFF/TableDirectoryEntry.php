<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\WOFF;

use Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\Table\DirectoryEntry;

/**
 * WOFF font file table directory entry.
 *
 * @package phpFontLib
 */
class TableDirectoryEntry extends DirectoryEntry {
  public $origLength;

  function __construct(File $font) {
    parent::__construct($font);
  }

  function parse() {
    parent::parse();

    $font             = $this->font;
    $this->offset     = $font->readUInt32();
    $this->length     = $font->readUInt32();
    $this->origLength = $font->readUInt32();
    $this->checksum   = $font->readUInt32();
  }
}

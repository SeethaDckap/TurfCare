<?php
/**
 * @package phpFontLib
 * @link    https://github.com/PhenX/phpFontLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\TrueType;

use Itoris\SmartFormerGold\plugins\dompdf\lib\phpFontLib\src\FontLib\Table\DirectoryEntry;

/**
 * TrueType table directory entry.
 *
 * @package phpFontLib
 */
class TableDirectoryEntry extends DirectoryEntry {
  function __construct(File $font) {
    parent::__construct($font);
  }

  function parse() {
    parent::parse();

    $font           = $this->font;
    $this->checksum = $font->readUInt32();
    $this->offset   = $font->readUInt32();
    $this->length   = $font->readUInt32();
    $this->entryLength += 12;
  }
}


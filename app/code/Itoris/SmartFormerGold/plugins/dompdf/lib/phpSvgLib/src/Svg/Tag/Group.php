<?php
/**
 * @package phpSvgLib
 * @link    http://github.com/PhenX/phpSvgLib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Itoris\SmartFormerGold\plugins\dompdf\lib\phpSvgLib\src\Svg\Tag;

use Itoris\SmartFormerGold\plugins\dompdf\lib\phpSvgLib\src\Svg\Style;

class Group extends AbstractTag
{
    protected function before($attribs)
    {
        $surface = $this->document->getSurface();

        $surface->save();

        $style = new Style();
        $style->inherit($this);
        $style->fromAttributes($attribs);

        $this->setStyle($style);

        $surface->setStyle($style);

        $this->applyTransform($attribs);
    }

    protected function after()
    {
        $this->document->getSurface()->restore();
    }
} 
<?php
namespace Sitegeist\Kaleidoscope\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Mvc\Controller\ActionController;

use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette;
use Imagine\Image\Box;
use Imagine\Image\Point;

class DummyImageController extends ActionController
{
    /**
     * @var ImagineInterface
     * @Flow\Inject
     */
    protected $imagineService;

    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @var PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * Get a dummy-image
     *
     * @param int $w
     * @param int $h
     * @param string $bg
     * @param string $fg
     * @param string $t
     */
    public function imageAction (int $w = 600, int $h = 400, string $bg = '#000', string $fg = '#fff', string $t = null)
    {
        // limit input arguments
        if ($w > 9999) {
            $w = 9999;
        }

        if ($h > 9999) {
            $h = 9999;
        }

        // limit input arguments
        if ($w < 10 ) {
            $w = 10;
        }

        if ($h < 10) {
            $h = 10;
        }

        if (is_null($t)) {
            $t = (string)$w . ' x ' . (string)$h;
        }

        $width = $w;
        $height = $h;
        $text = $t;

        // create imagine
        $palette = new Palette\RGB();
        $backgroundColor = $palette->color($bg);
        $foregroundColor = $palette->color($fg);

        // create image
        $imageBox = new Box($width, $height);
        $image = $this->imagineService->create($imageBox);
        $image->usePalette($palette);

        // render border
        $renderBorder = ($w >= 70 && $h >= 70);

        // render shape
        $renderShape = ($w >= 200 && $h >= 100);

        $renderText = ($w >= 50 && $h >= 30);

        $renderPattern = ($w >= 20 && $h >= 20);

        $this->renderBackground($image, $foregroundColor, $backgroundColor, $width, $height);

        if ($renderShape) {
            $this->renderShape($image, $foregroundColor, $backgroundColor, $width, $height);
        }

        if ($renderBorder) {
            $this->renderBorder($image, $foregroundColor, $backgroundColor, $width, $height);
        }

        if ($t && $renderText) {
            $this->renderText($image, $foregroundColor, $width, $height, $text, $renderShape ? false : true);
        }

        if ($renderPattern) {
            $this->renderPattern($image, $renderShape ? $backgroundColor : $foregroundColor, $width, $height, $text);
        }

        // build result
        $this->response->setHeader( 'Cache-Control', 'max-age=883000000');
        $this->response->setHeader( 'Content-type', 'image/png');
        return $image->get('png');
    }

    /**
     * @param ImageInterface $image
     * @param ColorInterface $foregroundColor
     * @param ColorInterface $backgroundColor
     * @param int $width
     * @param int $height
     */
    protected function renderBackground(ImageInterface $image, ColorInterface $foregroundColor,  ColorInterface $backgroundColor, int $width, int $height): void
    {
        $image->draw()->polygon(
            [
                new Point(0,0),
                new Point($width, 0),
                new Point($width, $height),
                new Point(0, $height)
            ],
            $backgroundColor,
            true,
            1
        );

    }
    /**
     * @param ImageInterface $image
     * @param ColorInterface $foregroundColor
     * @param ColorInterface $backgroundColor
     * @param int $width
     * @param int $height
     */
    protected function renderShape(ImageInterface $image, ColorInterface $foregroundColor,  ColorInterface $backgroundColor, int $width, int $height): void
    {
        $imageAspectRatio = $width / $height;
        $baseShapeWidth = 600;
        $baseShapeHeight = 400;
        $baseShapeAspectRatio = $baseShapeWidth / $baseShapeHeight;

        /**
         * @var $baseShape Point[]
         */
        $baseShape = [
            new Point(0, 250), // left ground
            new Point(0, 0), // left top
            new Point(600, 0), // right top
            new Point(600, 250), // right ground
            new Point(580, 250),

            new Point(440, 110), // small mountain
            new Point(360, 190),  // saddle
            new Point(220, 50), // big mountain

            new Point(20, 250),
        ];

        // transform shape to center of the image
        $factor = ($imageAspectRatio > $baseShapeAspectRatio) ? (float)$height / (float)$baseShapeHeight : (float)$width / (float)$baseShapeWidth;
        $xoffset = ($imageAspectRatio > $baseShapeAspectRatio) ? ($width - ($baseShapeWidth * $factor)) / 2.0 : 0.0;
        $yoffset = ($imageAspectRatio < $baseShapeAspectRatio) ? ($height - ($baseShapeHeight * $factor)) / 2.0 : 0.0;

        /**
         * @var $transformedShape Point[]
         */
        $transformedShape = array_map(
            function (Point $point) use ($factor, $xoffset, $yoffset) {
                return new Point($point->getX() * $factor + $xoffset, $point->getY() * $factor + $yoffset);
            },
            $baseShape
        );

        // adjust some points based on aspect ratio
        $transformedShape[0] = new Point(0, $transformedShape[0]->getY());
        $transformedShape[1] = new Point(0, 0);
        $transformedShape[2] = new Point( $width, 0);
        $transformedShape[3] = new Point( $width, $transformedShape[3]->getY());

        // draw shape
        $image->draw()->polygon(
            $transformedShape,
            $foregroundColor,
            true,
            1
        );
    }

    /**
     * @param ImageInterface $image
     * @param ColorInterface $foregroundColor
     * @param ColorInterface $backgroundColor
     * @param int $width
     * @param int $height
     */
    protected function renderBorder(ImageInterface $image, ColorInterface $foregroundColor,  ColorInterface $backgroundColor,int $width, int $height): void
    {
        $borderWidth = 10;

        for ($i = 0; $i <= $borderWidth; $i++) {
            $x1 = $i;
            $x2 = $width - $i;
            $y1 = $i;
            $y2 = $height - $i;
            $image->draw()->polygon(
                [
                    new Point($x1, $y1),
                    new Point($x2, $y1),
                    new Point($x2, $y2),
                    new Point($x1, $y2)
                ],
                ($i > $borderWidth / 2 ? $foregroundColor : $backgroundColor),
                false,
                1
            );
        }
    }

    /**
     * @param ImageInterface $image
     * @param ColorInterface $textColor
     * @param int $width
     * @param int $height
     * @param string $text
     */
    protected function renderText(ImageInterface $image, ColorInterface $textColor, int $width, int $height, string $text, $center = false): void
    {
        $initialFontSize = 10;
        $fontFile = $this->packageManager->getPackage('Neos.Neos')->getPackagePath() . "Resources/Public/Fonts/NotoSans/NotoSans-Regular.ttf";
        $initialFont = $this->imagineService->font($fontFile, $initialFontSize, $textColor);

        // scale text to fit the image
        $initialFontBox = $initialFont->box($text);
        $targetFontWidth = $width * .5;
        $targetFontHeight = $center ? $height * .5 : $height * .20;
        $correctedFontSizeByWidth = $targetFontWidth * $initialFontSize / $initialFontBox->getWidth();
        $correctedFontSizeByHeight = $targetFontHeight * $initialFontSize / $initialFontBox->getHeight();

        // render actual text
        $actualFont = $this->imagineService->font($fontFile, min([$correctedFontSizeByWidth, $correctedFontSizeByHeight]), $textColor);
        $actualFontBox = $actualFont->box($text);
        $imageCenterPosition = new Point($width / 2 , $height / 2);
        $textCenterPosition = new Point\Center($actualFontBox);
        if ($center) {
            $centeredTextPosition = new Point($imageCenterPosition->getX() - $textCenterPosition->getX(), ($height * .5 - $actualFontBox->getHeight() * .5));
        } else {
            $centeredTextPosition = new Point($imageCenterPosition->getX() - $textCenterPosition->getX(), ($height * .78 - $actualFontBox->getHeight() * .5));
        }
        $image->draw()->text($text, $actualFont, $centeredTextPosition);
    }

    protected function renderPattern(ImageInterface $image, ColorInterface $patternColor, int $width, int $height): void
    {
        $borderWidth = 5;
        $patternSize = 50;

        $limitingDimension = $width > $height ? $height : $width;

        if ($limitingDimension < ($patternSize + $borderWidth + $borderWidth)) {
            $patternSize = $limitingDimension - $borderWidth - $borderWidth;
        }

        for ($i = 0; $i < $patternSize; $i++) {
            for ($k = 0; $k < $patternSize; $k++) {

                if ($k > $patternSize - $i || $i > $patternSize - $k) {
                    continue;
                }

                if (
                    $i == $k ||
                    ($i % 2 && $k % 2)

                ) {
                    $image->draw()->dot(new Point($borderWidth + $i, $borderWidth + $k), $patternColor);
                }
            }
        }
    }
}

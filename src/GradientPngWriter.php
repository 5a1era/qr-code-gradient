<?php

namespace Vlr\QrCodeGradient;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Writer\Result\PngResult;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Override;

final class GradientPngWriter extends GradientAbstract implements GradientWriterInterface
{

    public function __construct() {}


    #[Override]
    public function writeGradient(
        ColorInterface $startColor,
        ColorInterface $endColor,
        ?ColorInterface $middleColor,
        ResultInterface $result
    ): ResultInterface {

        $image = imagecreatefromstring($result->getString());
        imagepalettetotruecolor($image);

        $width = imagesx($image);
        $height = imagesy($image);
        $halfHeight = $height / 2;

        for ($y = 0; $y < $height; $y++) {

            if ($middleColor !== null) {
                if ($y < $halfHeight) {
                    $ratio = $y / $halfHeight;
                    $red = $this->interpolate($startColor->getRed(), $middleColor->getRed(), $ratio);
                    $green = $this->interpolate($startColor->getGreen(), $middleColor->getGreen(), $ratio);
                    $blue = $this->interpolate($startColor->getBlue(), $middleColor->getBlue(), $ratio);
                } else {
                    $ratio = ($y - $halfHeight) / $halfHeight;
                    $red = $this->interpolate($middleColor->getRed(), $endColor->getRed(), $ratio);
                    $green = $this->interpolate($middleColor->getGreen(), $endColor->getGreen(), $ratio);
                    $blue = $this->interpolate($middleColor->getBlue(), $endColor->getBlue(), $ratio);
                }
            } else {
                $ratio = $y / $height;
                $red = $this->interpolate($startColor->getRed(), $endColor->getRed(), $ratio);
                $green = $this->interpolate($startColor->getGreen(), $endColor->getGreen(), $ratio);
                $blue = $this->interpolate($startColor->getBlue(), $endColor->getBlue(), $ratio);
            }

            $gradientColor = imagecolorallocate($image, $red, $green, $blue);

            for ($x = 0; $x < $width; $x++) {
                $colorIndex = imagecolorat($image, $y, $x);
                $colorInfo = imagecolorsforindex($image, $colorIndex);

                if (
                    $colorInfo['red'] < 50 &&
                    $colorInfo['green'] < 50 &&
                    $colorInfo['blue'] < 50
                ) {
                    imagesetpixel($image, $y, $x, $gradientColor);
                }
            }
        }

        return new PngResult($result->getMatrix(), $image);
    }
}

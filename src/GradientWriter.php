<?php

namespace Vlr\QrCodeGradient;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Writer\Result\PngResult;
use Endroid\QrCode\Writer\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;

final class GradientWriter implements WriterInterface
{

    public function __construct(
        private WriterInterface $baseWriter,
        private ColorInterface $startColor,
        private ColorInterface $endColor,
        private ?ColorInterface $middleColor = null
    ) {}

    #[Override]
    public function write(
        QrCodeInterface $qrCode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface {

        $result = $this->baseWriter->write($qrCode, $logo, $label, $options);

        if ($this->baseWriter instanceof PngWriter) {

            $image = imagecreatefromstring($result->getString());
            imagepalettetotruecolor($image);

            $width = imagesx($image);
            $height = imagesy($image);
            $halfHeight = $height / 2;

            for ($y = 0; $y < $height; $y++) {

                if ($this->middleColor !== null) {
                    if ($y < $halfHeight) {
                        $ratio = $y / $halfHeight;
                        $red = $this->interpolate($this->startColor->getRed(), $this->middleColor->getRed(), $ratio);
                        $green = $this->interpolate($this->startColor->getGreen(), $this->middleColor->getGreen(), $ratio);
                        $blue = $this->interpolate($this->startColor->getBlue(), $this->middleColor->getBlue(), $ratio);
                    } else {
                        $ratio = ($y - $halfHeight) / $halfHeight;
                        $red = $this->interpolate($this->middleColor->getRed(), $this->endColor->getRed(), $ratio);
                        $green = $this->interpolate($this->middleColor->getGreen(), $this->endColor->getGreen(), $ratio);
                        $blue = $this->interpolate($this->middleColor->getBlue(), $this->endColor->getBlue(), $ratio);
                    }
                } else {
                    $ratio = $y / $height;
                    $red = $this->interpolate($this->startColor->getRed(), $this->endColor->getRed(), $ratio);
                    $green = $this->interpolate($this->startColor->getGreen(), $this->endColor->getGreen(), $ratio);
                    $blue = $this->interpolate($this->startColor->getBlue(), $this->endColor->getBlue(), $ratio);
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

        return $result;
    }

    private function interpolate(int $start, int $end, float $ratio): int
    {
        return (int)($start + ($end - $start) * $ratio);
    }
}

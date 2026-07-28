<?php

namespace Vlr\QrCodeGradient;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\PngResult;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;

final class GradientPngWriter implements WriterInterface
{
    private PngWriter $baseWriter;

    public function __construct(
        private ColorInterface $startColor,
        private ColorInterface $endColor,
        private ?ColorInterface $middleColor = null
    ) {
        $this->baseWriter = new PngWriter();
    }

    #[Override]
    public function write(
        QrCodeInterface $qrCode,
        ?LogoInterface $logo = null,
        ?LabelInterface $label = null,
        array $options = []
    ): ResultInterface {

        $result = $this->baseWriter->write($qrCode, $logo, $label, $options);
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
                    $colorInfo['red'] === 0 &&
                    $colorInfo['green'] === 0 &&
                    $colorInfo['blue'] === 0 &&
                    $colorInfo['alpha'] === 0
                ) {
                    imagesetpixel($image, $y, $x, $gradientColor);
                }
            }
        }

        return new PngResult($result->getMatrix(), $image);
    }

    private function interpolate(int $start, int $end, float $ratio): int
    {
        return (int)($start + ($end - $start) * $ratio);
    }
}

<?php

use Endroid\QrCode\Color\ColorInterface;
use Vlr\QrCodeGradient\Math\ColorInterpolator;
use Vlr\QrCodeGradient\Renderer\GradientRendererInterface;

final class GdGradientRenderer implements GradientRendererInterface
{
    public function __construct(private ColorInterpolator $colorInterpolator) {}

    #[Override]
    public function createGradientImage(
        int $width,
        int $height,
        ColorInterface $startColor,
        ColorInterface $endColor,
        ?ColorInterface $middleColor = null
    ): GdImage {

        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, false);

        imagesavealpha($image, true);

        if ($middleColor !== null) {

            $halfHeight = (int)($height / 2);

            $this->drawBand($image, 0, 0, $width, $halfHeight, $startColor, $middleColor);

            $this->drawBand($image, 0, $halfHeight, $width, $height, $middleColor, $endColor);
        } else {

            $this->drawBand($image, 0, 0, $width, $height, $startColor, $endColor);
        }
        return $image;
    }

    #[Override]
    public function applyMask(GdImage $sourceQrResource, GdImage $gradientResource): GdImage
    {
        $width = imagesx($sourceQrResource);

        $height = imagesy($sourceQrResource);

        $finalImage = imagecreatetruecolor($width, $height);

        imagealphablending($finalImage, false);

        imagesavealpha($finalImage, true);

        $transparent = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);

        imagefilledrectangle($finalImage, 0, 0, $width, $height, $transparent);

        $mask = imagecreatetruecolor($width, $height);

        imagecopy($mask, $sourceQrResource, 0, 0, 0, 0, $width, $height);

        $qr = imagecreatetruecolor($width, $height);

        imagecopy($qr, $sourceQrResource, 0, 0, 0, 0, $width, $height);

        $white = imagecolorallocate($qr, 255, 255, 255);

        imagecolortransparent($qr, $white);

        $resultImage = $gradientResource;

        $mask = imagecreatefromstring(imagepng($sourceQrResource));

        imagefilter($mask, IMG_FILTER_NEGATE);

        imagefilter($mask, IMG_FILTER_CONTRAST, -1000);

        $black = imagecolorallocate($mask, 0, 0, 0);

        imagecolortransparent($mask, $black);

        imagecopymerge($resultImage, $mask, 0, 0, 0, 0, $width, $height, 100);

        return $resultImage;
    }

    public function drawBand(
        GdImage $resource,
        int $x1,
        int $y1,
        int $x2,
        int $y2,
        ColorInterface $colorStart,
        ColorInterface $colorEnd
    ): void {

        $height = $y2 - $y1;

        if ($height <= 0) return;

        for ($y = $y1; $y < $y2; $y++) {

            $ratio = ($y - $y1) / $height;

            [$r, $g, $b] = $this->colorInterpolator->interpolate($colorStart, $colorEnd, $ratio);

            $color = imagecolorallocate($resource, $r, $g, $b);

            imageline($resource, $x1, $y, $x2, $y, $color);
        }
    }
}

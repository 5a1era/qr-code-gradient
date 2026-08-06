<?php

namespace Vlr\QrCodeGradient\Renderer;

use Endroid\QrCode\Color\ColorInterface;
use GdImage;

interface GradientRendererInterface
{
    public function createGradientImage(
        int $width,
        int $height,
        ColorInterface $startColor,
        ColorInterface $endColor,
        ?ColorInterface $middleColor
    ): GdImage;

    public function applyMask(GdImage $sourceQrResource, GdImage $gradientResource): GdImage;
}

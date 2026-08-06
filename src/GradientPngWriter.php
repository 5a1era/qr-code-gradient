<?php

namespace Vlr\QrCodeGradient;

use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Writer\Result\PngResult;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Override;
use Vlr\QrCodeGradient\Renderer\GradientRendererInterface;

final class GradientPngWriter implements GradientWriterInterface
{
    public function __construct(private GradientRendererInterface $renderer) {}

    #[Override]
    public function writeGradient(
        ColorInterface $startColor,
        ColorInterface $endColor,
        ?ColorInterface $middleColor,
        ResultInterface $result
    ): ResultInterface {

        if (!$result instanceof PngResult) {
            
            throw new \InvalidArgumentException('GradientPngWriter supports only PngResult.');
        }

        $sourceQrResource = imagecreatefromstring($result->getString());

        if (!$sourceQrResource) {

            throw new \RuntimeException('Invalid QR code image data.');
        }
        imagepalettetotruecolor($sourceQrResource);

        $width = imagesx($sourceQrResource);
        $height = imagesy($sourceQrResource);

        $gradientResource = $this->renderer->createGradientImage(
            $width,
            $height,
            $startColor,
            $endColor,
            $middleColor
        );

        $finalImageResource = $this->renderer->applyMask($sourceQrResource, $gradientResource);

        return new PngResult($result->getMatrix(), $finalImageResource);
    }
}

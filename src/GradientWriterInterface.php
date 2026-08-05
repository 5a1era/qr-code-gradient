<?php

namespace Vlr\QrCodeGradient;

use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Color\ColorInterface;

interface GradientWriterInterface
{
    public function writeGradient(
        ColorInterface $startColor,
        ColorInterface $endColor,
        ?ColorInterface $middleColor,
        ResultInterface $result
    ): ResultInterface;
}

<?php

namespace Vlr\QrCodeGradient\Math;

use Endroid\QrCode\Color\ColorInterface;

final class ColorInterpolator
{
    public function interpolate(
        ColorInterface $start,
        ColorInterface $end,
        float $ratio
    ): array {
        $ratio = max(0.0, min(1.0, $ratio));

        return [
            $this->calculateChannel($start->getRed(), $end->getRed(), $ratio),
            $this->calculateChannel($start->getGreen(), $end->getGreen(), $ratio),
            $this->calculateChannel($start->getBlue(), $end->getBlue(), $ratio)
        ];
    }

    private function calculateChannel(int $start, int $end, float $ratio): int
    {
        return (int)($start + ($end - $start) * $ratio);
    }
}

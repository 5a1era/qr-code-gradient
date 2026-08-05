<?php

namespace Vlr\QrCodeGradient;

abstract class GradientAbstract
{
    protected function interpolate(int $start, int $end, float $ratio): int
    {
        return (int)($start + ($end - $start) * $ratio);
    }
}

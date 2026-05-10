<?php

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatPrice(float $price): string
{
    return number_format($price, 0, '.', ',') . ' $ / شهر';
}

function getDefaultPropertyImage(): string
{
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="700" height="420"><rect width="100%" height="100%" fill="#E9ECEF"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6C757D" font-family="Arial,sans-serif" font-size="26">No Image Available</text></svg>');
}

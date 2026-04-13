<?php

namespace App\Support;

class HtmlSanitizer
{
    public static function clean(?string $value): string
    {
        $value ??= '';

        return strip_tags($value, '<p><br><strong><em><ul><ol><li><a><blockquote><code><pre><h3><h4>');
    }
}

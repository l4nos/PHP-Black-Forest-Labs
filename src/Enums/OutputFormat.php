<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Enums;

/**
 * Output format enumeration for image generation.
 *
 * @author Lanos <https://github.com/l4nos>
 */
enum OutputFormat: string
{
    case JPEG = 'jpeg';
    case PNG = 'png';

    /**
     * Get the MIME type for the format.
     */
    public function getMimeType(): string
    {
        return match ($this) {
            self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
        };
    }

    /**
     * Get the file extension for the format.
     */
    public function getExtension(): string
    {
        return match ($this) {
            self::JPEG => '.jpg',
            self::PNG => '.png',
        };
    }
}

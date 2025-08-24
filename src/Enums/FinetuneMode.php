<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Enums;

/**
 * Finetune mode enumeration for training configuration.
 *
 * @author Lanos <https://github.com/l4nos>
 */
enum FinetuneMode: string
{
    case GENERAL = 'general';
    case CHARACTER = 'character';
    case STYLE = 'style';
    case PRODUCT = 'product';

    /**
     * Get a description of what this mode is optimized for.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::GENERAL => 'General purpose fine-tuning for diverse content',
            self::CHARACTER => 'Optimized for character-based training',
            self::STYLE => 'Optimized for artistic style transfer',
            self::PRODUCT => 'Optimized for product photography and commercial use',
        };
    }
}

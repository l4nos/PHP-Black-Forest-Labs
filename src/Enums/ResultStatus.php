<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Enums;

/**
 * Status enumeration for generation tasks
 *
 * @package Lanos\PHPBFL\Enums
 * @author Lanos <https://github.com/l4nos>
 */
enum ResultStatus: string
{
    case TASK_NOT_FOUND = 'Task not found';
    case PENDING = 'Pending';
    case REQUEST_MODERATED = 'Request Moderated';
    case CONTENT_MODERATED = 'Content Moderated';
    case READY = 'Ready';
    case ERROR = 'Error';

    /**
     * Check if the status indicates the task is complete
     */
    public function isComplete(): bool
    {
        return match ($this) {
            self::READY, self::ERROR, self::CONTENT_MODERATED, self::TASK_NOT_FOUND => true,
            default => false,
        };
    }

    /**
     * Check if the status indicates the task failed
     */
    public function isFailed(): bool
    {
        return match ($this) {
            self::ERROR, self::CONTENT_MODERATED, self::TASK_NOT_FOUND => true,
            default => false,
        };
    }

    /**
     * Check if the status indicates the task is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::READY;
    }

    /**
     * Check if the task is still in progress
     */
    public function isInProgress(): bool
    {
        return match ($this) {
            self::PENDING, self::REQUEST_MODERATED => true,
            default => false,
        };
    }
}
<?php

namespace PosAppVN\LarkLogger;

use Monolog\Level;
use Monolog\LogRecord;

class LarkRecord
{
    public const COLOR_DANGER = 'red';
    public const COLOR_WARNING = 'yellow';
    public const COLOR_INFO = 'blue';
    public const COLOR_DEFAULT = 'purple';

    public function __construct(
        private ?string $title
    )
    {
        $this->title = $title;
    }

    public function getLarkData(LogRecord $record)
    {
        return $this->buildMarkdownContent($record);
    }

    /**
     * @param LogRecord $record
     * @return string
     */
    private function buildMarkdownContent(LogRecord $record): string
    {
        $content = "**Message**\n{$record->message}\n**Level**\n";
        $content .= "<text_tag color='{$this->getTitleColor($record->level)}'>{$record->level->getName()}</text_tag>";

        if (!empty($record->context)) {
            foreach ($record->context as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                    $content .= "\n**$key**\n```JSON\n$value\n```";
                } else {
                    $content .= "\n**$key**\n$value";
                }
            }
        }

        if (!empty($record->extra)) {
            foreach ($record->extra as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                    $content .= "\n**$key**\n```JSON\n$value\n```";
                } else {
                    $content .= "\n**$key**\n$value";
                }
            }
        }

        return $content;
    }

    /**
     *  Returns a Slack message attachment color associated with
     *  provided level.
     *
     * @param Level $level
     * @return string
     */
    public function getTitleColor(Level $level): string
    {
        return match ($level) {
            Level::Error, Level::Critical, Level::Alert, Level::Emergency => static::COLOR_DANGER,
            Level::Warning => static::COLOR_WARNING,
            Level::Info, Level::Notice => static::COLOR_INFO,
            Level::Debug => static::COLOR_DEFAULT
        };
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title ?? config('app.name');
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}

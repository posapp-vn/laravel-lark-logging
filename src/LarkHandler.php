<?php

namespace PosAppVN\LarkLogger;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class LarkHandler extends AbstractProcessingHandler
{
    /**
     * Webhook URL
     *
     * @var string
     */
    private ?string $webhookUrl;

    /**
     * Instance of the LarkRecord util class preparing data for Lark API.
     */
    private LarkRecord $larkRecord;

    /**
     * LarkHandler constructor.
     * @param array $config
     */
    public function __construct(
        private array $config
    )
    {
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level, true);

        $this->config = $config;
        $this->webhookUrl = $this->getConfigValue('webhook_url');
        $this->larkRecord = new LarkRecord(
            $this->getConfigValue('title') ?? config('app.name')
        );
    }

    /**
     * @param array $record
     */
    public function write($record): void
    {
        if (empty($this->webhookUrl)) {
            throw new InvalidArgumentException('Webhook URL is not set');
        }

        try {
            $this->sendMessage($record);
        } catch (Exception $e) {
            throw new Exception('Error sending message to Lark: ' . $e->getMessage());
        }
    }

    /**
     * @param LogRecord $record
     * @return array
     */
    private function buildPostData(LogRecord $record): array
    {
        $header = [
            'template' => $this->larkRecord->getTitleColor($record->level),
            'title' => [
                'content' => $this->getTitle(),
                'tag' => 'plain_text',
            ],
        ];

        // Lark uses the card header title as the push notification text,
        // so put the log message there and move the configured title to the subtitle.
        if ($this->isMessageInTitle()) {
            $message = preg_replace('/\s+/', ' ', trim($record->message));

            $header['title']['content'] = '[' . $record->level->getName() . '] ' . Str::limit($message, 100);
            $header['subtitle'] = [
                'content' => $this->getTitle(),
                'tag' => 'plain_text',
            ];
        }

        return [
            'msg_type' => 'interactive',
            'card' => [
                'elements' => [
                    [
                        'tag' => 'markdown',
                        'content' => $this->larkRecord->getLarkData($record),
                    ],
                ],
                'header' => $header,
            ],
        ];
    }

    /**
     * @param LogRecord $record
     * @return void
     */
    private function sendMessage(LogRecord $record): void
    {
        $postData = $this->buildPostData($record);

        $res = Http::withHeaders(['Content-type' => 'application/json'])
            ->retry($this->getConfigValue('retries') ?? 3, 100)
            ->post($this->webhookUrl, $postData);
    }

    /**
     * @param string $key
     * @param string|null $defaultConfigKey
     * @return string
     */
    private function getConfigValue($key, ?string $defaultConfigKey = null): ?string
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return config($defaultConfigKey ?: "lark-logger.$key");
    }

    /**
     * Whether the log message should be shown in the card header title
     * (and therefore in the mobile push notification).
     *
     * @return bool
     */
    private function isMessageInTitle(): bool
    {
        $value = $this->getConfigValue('message_in_title');

        if ($value === null) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->larkRecord->getTitle();
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->larkRecord->setTitle($title);

        return $this;
    }
}

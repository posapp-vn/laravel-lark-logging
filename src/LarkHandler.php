<?php

namespace PosAppVN\LarkLogger;

use Exception;
use Illuminate\Support\Facades\Http;
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
        return [
            'msg_type' => 'interactive',
            'card' => [
                'elements' => [
                    [
                        'tag' => 'markdown',
                        'content' => $this->larkRecord->getLarkData($record),
                    ],
                ],
                'header' => [
                    'template' => $this->larkRecord->getTitleColor($record->level),
                    'title' => [
                        'content' => $this->getTitle(),
                        'tag' => 'plain_text',
                    ],
                ],
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

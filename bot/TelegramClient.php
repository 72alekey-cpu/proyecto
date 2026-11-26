<?php

declare(strict_types=1);

final class TelegramClient
{
    private string $apiBaseUrl;

    public function __construct(string $botToken)
    {
        $this->apiBaseUrl = 'https://api.telegram.org/bot' . $botToken . '/';
    }

    public function getUpdates(int $offset = 0, int $timeout = 25): array
    {
        $query = http_build_query([
            'offset' => $offset,
            'timeout' => $timeout,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]);

        $response = $this->request('getUpdates?' . $query);
        if (!isset($response['ok'], $response['result']) || $response['ok'] !== true || !is_array($response['result'])) {
            return [];
        }

        return $response['result'];
    }

    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = json_encode($replyMarkup, JSON_UNESCAPED_UNICODE);
        }

        $response = $this->request('sendMessage', $payload);

        return isset($response['ok']) && $response['ok'] === true;
    }

    private function request(string $methodAndQuery, ?array $postFields = null): array
    {
        $ch = curl_init($this->apiBaseUrl . $methodAndQuery);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 35,
        ]);

        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            error_log('Error cURL Telegram: ' . curl_error($ch));
            curl_close($ch);
            return [];
        }

        curl_close($ch);

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}

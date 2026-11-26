<?php

declare(strict_types=1);

require_once __DIR__ . '/TelegramClient.php';
require_once __DIR__ . '/BotHandler.php';

$configPath = __DIR__ . '/../config/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Falta config/config.php. Copia config/config.example.php y configura tu token.\n");
    exit(1);
}

$config = require $configPath;
$token = $config['telegram']['bot_token'] ?? '';
$timeout = (int) ($config['telegram']['poll_timeout'] ?? 25);

if ($token === '' || $token === 'PON_AQUI_TU_TOKEN_DE_BOTFATHER') {
    fwrite(STDERR, "Configura un token válido de BotFather en config/config.php.\n");
    exit(1);
}

$telegram = new TelegramClient($token);
$handler = new BotHandler($telegram);
$offset = 0;

fwrite(STDOUT, "Bot iniciado en modo long polling. Presiona Ctrl+C para detener.\n");

while (true) {
    $updates = $telegram->getUpdates($offset, $timeout);

    foreach ($updates as $update) {
        if (!is_array($update) || !isset($update['update_id'])) {
            continue;
        }

        $updateId = (int) $update['update_id'];
        $offset = $updateId + 1;

        try {
            $handler->handleUpdate($update);
        } catch (Throwable $e) {
            error_log('Error procesando update ' . $updateId . ': ' . $e->getMessage());
        }
    }
}
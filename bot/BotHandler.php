<?php

declare(strict_types=1);

final class BotHandler
{
    private TelegramClient $telegram;

    public function __construct(TelegramClient $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['callback_query']) && is_array($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return;
        }

        if (!isset($update['message']) || !is_array($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = isset($message['text']) ? trim((string) $message['text']) : '';
        $firstName = isset($message['from']['first_name']) ? (string) $message['from']['first_name'] : 'usuario';

        if ($chatId === null || $text === '') {
            return;
        }

        $command = strtolower(explode(' ', $text)[0]);
        if (str_contains($command, '@')) {
            $command = explode('@', $command)[0];
        }

        switch ($command) {
            case '/start':
                $this->sendStart($chatId, $firstName);
                break;

            case '/ayuda':
                $this->sendHelp($chatId);
                break;

            default:
                $this->telegram->sendMessage(
                    $chatId,
                    "No reconocí ese comando. Usa /ayuda para ver las opciones disponibles o toca un botón."
                );
                break;
        }
    }

    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $data = isset($callbackQuery['data']) ? (string) $callbackQuery['data'] : '';

        if ($chatId === null) {
            return;
        }

        if ($data === 'cmd_ayuda') {
            $this->sendHelp($chatId);
        }
    }

    private function sendStart(int|string $chatId, string $firstName): void
    {
        $safeName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');

        $text = "Hola, <b>{$safeName}</b>. Bienvenido a <b>Asesoría Bot UP</b>.\n\n"
            . "Si aún no vinculas tu cuenta, genera tu código en la plataforma web y envíalo con:\n"
            . "<code>/vincular CODIGO</code>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Ver ayuda', 'callback_data' => 'cmd_ayuda'],
                ],
            ],
        ];

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    private function sendHelp(int|string $chatId): void
    {
        $text = "<b>Comandos disponibles en Asesoría Bot UP</b>\n\n"
            . "/start — Bienvenida y estado de vinculación\n"
            . "/ayuda — Lista de comandos disponibles\n"
            . "/vincular CODIGO — Vincula tu chat con tu cuenta web\n"
            . "/materias — Consulta materias con asesoría\n"
            . "/horarios — Consulta horarios disponibles\n"
            . "/reservar — Reserva una cita paso a paso\n"
            . "/miscitas — Consulta tus citas activas\n"
            . "/cancelar — Cancela una cita activa";

        $this->telegram->sendMessage($chatId, $text);
    }
}
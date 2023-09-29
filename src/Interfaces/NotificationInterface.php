<?php

namespace LbilTech\TelegramGitNotifier\Interfaces;

use LbilTech\TelegramGitNotifier\Exceptions\InvalidViewTemplateException;
use LbilTech\TelegramGitNotifier\Exceptions\SendNotificationException;
use LbilTech\TelegramGitNotifier\Services\TelegramService;
use Symfony\Component\HttpFoundation\Request;

interface NotificationInterface
{
    /**
     * Notify access denied to other chat ids
     *
     * @param TelegramService $telegramService
     * @param string|null $viewTemplate
     * @param string|null $chatId
     *
     * @return void
     * @throws InvalidViewTemplateException
     */
    public function accessDenied(
        TelegramService $telegramService,
        string $chatId = null,
        string $viewTemplate = null,
    ): void;

    /**
     * Set payload from request
     *
     * @param Request $request
     * @param string $event
     *
     * @return mixed|void
     * @throws InvalidViewTemplateException
     */
    public function setPayload(Request $request, string $event);

    /**
     * Send notification to telegram
     *
     * @param string $chatId
     * @param string|null $message
     *
     * @return bool
     * @throws SendNotificationException
     */
    public function sendNotify(string $chatId, string $message = null): bool;

    /**
     * Get action name of event from payload data
     *
     * @param $payload
     *
     * @return string
     * @see ActionEventTrait::getActionOfEvent()
     */
    public function getActionOfEvent($payload): string;
}

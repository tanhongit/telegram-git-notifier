<?php

namespace LbilTech\TelegramGitNotifier\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LbilTech\TelegramGitNotifier\Constants\EventConstant;
use LbilTech\TelegramGitNotifier\Trait\ActionEventTrait;
use Symfony\Component\HttpFoundation\Request;

class NotificationService
{
    use ActionEventTrait;

    protected mixed $payload;

    protected string $message = '';

    public string $platform = EventConstant::DEFAULT_PLATFORM;

    /**
     * Notify access denied to other chat ids
     *
     * @param TelegramService $telegramService
     * @param string|null $chatId
     *
     * @return void
     */
    public function accessDenied(
        TelegramService $telegramService,
        string $chatId = null
    ): void {
        $telegramService->telegram->sendMessage([
            'chat_id' => config('telegram-bot.chat_id'),
            'text' => view(
                'globals.access_denied',
                ['chatId' => $chatId]
            ),
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Set payload from request
     *
     * @param Request $request
     * @param string $event
     *
     * @return mixed|void
     */
    public function setPayload(Request $request, string $event)
    {
        if ($this->platform === 'gitlab') {
            $this->payload = json_decode($request->getContent());
        } elseif ($this->platform === EventConstant::DEFAULT_PLATFORM) {
            $this->payload = json_decode($request->request->get('payload'));
        }
        $this->setMessage($event);

        return $this->payload;
    }

    /**
     * Set message from payload
     *
     * @param string $typeEvent
     *
     * @return void
     */
    private function setMessage(string $typeEvent): void
    {
        $event = tgn_event_name($typeEvent);

        $action = $this->getActionOfEvent($this->payload);

        if (!empty($action)) {
            $this->message = view(
                "events.{$this->platform}.{$event}.{$action}",
                [
                    'payload' => $this->payload,
                    'event'   => tgn_convert_event_name($typeEvent),
                ]
            );
        } else {
            $this->message = view(
                "events.{$this->platform}.{$event}.default",
                ['payload' => $this->payload]
            );
        }
    }

    /**
     * Send notification to telegram
     *
     * @param string $chatId
     * @param string|null $message
     *
     * @return bool
     */
    public function sendNotify(string $chatId, string $message = null): bool
    {
        if (!is_null($message)) {
            $this->message = $message;
        }

        $method_url = 'https://api.telegram.org/bot'
            . config(
                'telegram-bot.token'
            ) . '/sendMessage';
        $url = $method_url . '?chat_id=' . $chatId
            . '&disable_web_page_preview=1&parse_mode=html&text='
            . tgn_urlencoded_message($this->message);

        $client = new Client();

        try {
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (GuzzleException $e) {
            error_log($e->getMessage());
        }

        return false;
    }
}

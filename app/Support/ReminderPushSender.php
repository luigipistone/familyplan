<?php

namespace App\Support;

use App\Models\PushSubscription;
use App\Models\Reminder;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class ReminderPushSender
{
    public function sendDueReminders(): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        $count = 0;

        Reminder::whereNull('completed_at')
            ->whereNotNull('due_at')
            ->whereNull('push_sent_at')
            ->where('due_at', '<=', now())
            ->orderBy('due_at')
            ->chunkById(50, function ($reminders) use (&$count) {
                foreach ($reminders as $reminder) {
                    $recipients = $this->recipientUsers($reminder);
                    if ($recipients->isEmpty()) {
                        $reminder->forceFill(['push_sent_at' => now()])->save();
                        continue;
                    }

                    $webPush = new WebPush([
                        'VAPID' => [
                            'subject' => config('push.vapid.subject'),
                            'publicKey' => config('push.vapid.public_key'),
                            'privateKey' => config('push.vapid.private_key'),
                        ],
                    ]);

                    $queued = 0;
                    foreach ($recipients as $user) {
                        foreach ($user->pushSubscriptions as $subscription) {
                            $webPush->queueNotification(
                                Subscription::create([
                                    'endpoint' => $subscription->endpoint,
                                    'publicKey' => $subscription->public_key,
                                    'authToken' => $subscription->auth_token,
                                    'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                                ]),
                                json_encode([
                                    'title' => $reminder->title,
                                    'body' => collect([
                                        optional($reminder->due_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                                        $reminder->body ? trim(strip_tags($reminder->body)) : null,
                                    ])->filter()->join(' - '),
                                    'url' => route('reminders', ['open' => 'reminder-'.$reminder->id]),
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            );
                            $queued++;
                        }
                    }

                    foreach ($webPush->flush() as $report) {
                        if (! $report->isSuccess()) {
                            $endpoint = $report->getRequest()?->getUri()?->__toString();
                            if ($endpoint) {
                                PushSubscription::where('endpoint', $endpoint)->delete();
                            }
                        }
                    }

                    $reminder->forceFill(['push_sent_at' => now()])->save();
                    if ($queued > 0) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function isConfigured(): bool
    {
        return filled(config('push.vapid.public_key')) && filled(config('push.vapid.private_key')) && filled(config('push.vapid.subject'));
    }

    private function recipientUsers(Reminder $reminder)
    {
        $ids = collect([$reminder->owner_id, ...($reminder->shared_with ?? [])])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return User::with('pushSubscriptions')
            ->whereIn('id', $ids)
            ->where('push_notifications', true)
            ->where('is_active', true)
            ->get();
    }
}

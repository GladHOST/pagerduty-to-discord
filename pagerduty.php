<?php

require __DIR__ . '/vendor/autoload.php';

use Atakde\DiscordWebhook\DiscordWebhook;
use Atakde\DiscordWebhook\Message\MessageFactory;

require "logger.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $event = $data['event'];

    $messageFactory = new MessageFactory();
    $embedMessage = $messageFactory->create('embed');
    $embedMessage->setUsername("PagerDuty");
    $embedMessage->setAvatarUrl("https://www.pagerduty.com/wp-content/themes/citizens-band/favicon/icons/apple-touch-icon.png");
    $embedMessage->setAuthorIcon("https://www.pagerduty.com/wp-content/themes/citizens-band/favicon/icons/apple-touch-icon.png");
    $embedMessage->setAuthorName("PagerDuty");
    $embedMessage->setUrl($event['data']['html_url']);

    $creation_date = new DateTime($event['data']['created_at']);
    $last_action_date = new DateTime($event['occurred_at']);

    switch ($event['event_type']) {
        case 'incident.triggered':
            $embedMessage->setTitle("[#{$event['data']['number']}] Triggered - {$event['data']['title']}");
            $embedMessage->setColor(0xf50202);
            $embedMessage->setDescription("
            Service: {$event['data']['service']['summary']}
            Created at: {$event['occurred_at']}
            Status: {$event['data']['status']}
            ");
            break;
        case 'incident.acknowledged':
            $embedMessage->setTitle("[#{$event['data']['number']}] Acknowledged - {$event['data']['title']}");
            $embedMessage->setColor(0xff9e68);
            $embedMessage->setDescription("
            Service: {$event['data']['service']['summary']}
            Created at: {$event['data']['created_at']}
            Status: {$event['data']['status']}
            Acknowledged at: {$event['occurred_at']}
            Acknowledged by: {$event['agent']['summary']}
            ");
            break;

        case 'incident.resolved':
            $duration = $last_action_date->diff($creation_date);

            $embedMessage->setTitle("[#{$event['data']['number']}] 
            Resolved - {$event['data']['title']}");
            $embedMessage->setColor(0x3cff00);
            $embedMessage->setDescription("
            Service: {$event['data']['service']['summary']}
            Created at: {$event['data']['created_at']}
            Status: {$event['data']['status']}
            Resolved at: {$event['occurred_at']}
            Resolved by: {$event['agent']['summary']}
            
            Total duration: {$duration->format('%H:%I:%S')}
            ");
            break;

        default:
            logRequest($event);
            die;
    }

    $webhook = new DiscordWebhook($embedMessage);
    $webhook->setWebhookUrl("https://discord.com/{$_SERVER['REQUEST_URI']}");
    $webhook->send();
} else {
    echo "Only POST requests are allowed on this service.";
}
?>

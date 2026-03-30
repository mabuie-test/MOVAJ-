<?php

declare(strict_types=1);

use App\Core\Session;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    Session::start();
    $token = Session::get('_csrf_token');
    if (!$token) {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
    }

    return $token;
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'draft', 'pending_quote' => 'bg-secondary-subtle text-secondary-emphasis',
        'pending_payment', 'payment_processing', 'awaiting_assignment', 'payout_pending' => 'bg-warning-subtle text-warning-emphasis',
        'paid', 'accepted', 'picked_up', 'in_transit', 'near_destination', 'arrived', 'active', 'approved' => 'bg-info-subtle text-info-emphasis',
        'delivered', 'payout_completed', 'completed' => 'bg-success-subtle text-success-emphasis',
        'canceled', 'failed', 'payout_failed', 'blocked', 'inactive' => 'bg-danger-subtle text-danger-emphasis',
        default => 'bg-light text-dark',
    };
}

function status_icon(string $status): string
{
    return match ($status) {
        'awaiting_assignment' => 'fa-hourglass-half',
        'accepted' => 'fa-handshake',
        'picked_up' => 'fa-box',
        'in_transit' => 'fa-motorcycle',
        'near_destination' => 'fa-location-arrow',
        'delivered' => 'fa-circle-check',
        'canceled', 'failed' => 'fa-circle-xmark',
        default => 'fa-circle-info',
    };
}

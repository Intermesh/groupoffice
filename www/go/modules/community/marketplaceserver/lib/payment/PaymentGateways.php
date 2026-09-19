<?php

namespace go\modules\community\marketplaceserver\lib\payment;

use go\modules\community\marketplaceserver\model\Settings;

/**
 * Registry / factory for the available payment gateway drivers. One place maps a
 * gateway id to its driver, so adding GoPay/Comgate/PayPal is a single new entry
 * plus the driver class. The checkout endpoint uses {@see active()}; the webhook
 * endpoint uses {@see byId()} (the driver is named in the webhook URL).
 */
class PaymentGateways
{
    /**
     * Build every known driver bound to the given settings.
     *
     * @param \go\modules\community\marketplaceserver\model\Settings $settings
     * @return array<string, \go\modules\community\marketplaceserver\lib\payment\PaymentGateway> id => driver
     */
    public static function all(Settings $settings): array
    {
        $drivers = [
            new StripeGateway($settings),
            // new GoPayGateway($settings), new ComgateGateway($settings), ...
        ];
        $map = [];
        foreach ($drivers as $d) {
            $map[$d->id()] = $d;
        }
        return $map;
    }

    /**
     * The admin-selected active gateway, or null when none is selected or it is
     * not configured. This is the gateway new checkouts use.
     *
     * @param \go\modules\community\marketplaceserver\model\Settings $settings
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentGateway|null
     */
    public static function active(Settings $settings): ?PaymentGateway
    {
        $id = trim((string) $settings->paymentGateway);
        if ($id === '') {
            return null;
        }
        $gw = self::all($settings)[$id] ?? null;
        return ($gw && $gw->isConfigured()) ? $gw : null;
    }

    /**
     * A gateway by id (for the webhook URL), regardless of whether it is the
     * currently-active one — a webhook for a gateway you just switched away from
     * must still be honoured while its subscriptions wind down.
     *
     * @param \go\modules\community\marketplaceserver\model\Settings $settings
     * @param string $id
     * @return \go\modules\community\marketplaceserver\lib\payment\PaymentGateway|null
     */
    public static function byId(Settings $settings, string $id): ?PaymentGateway
    {
        return self::all($settings)[$id] ?? null;
    }
}

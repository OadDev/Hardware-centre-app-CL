<?php

namespace Database\Seeders;

use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            ['key' => 'email', 'name' => 'Email Notifications', 'description' => 'Order confirmations, shipping updates, password resets', 'enabled' => true],
            ['key' => 'sms', 'name' => 'SMS Notifications', 'description' => 'OTPs, delivery alerts and urgent updates', 'enabled' => true],
            ['key' => 'whatsapp', 'name' => 'WhatsApp Notifications', 'description' => 'Order tracking and promotional messages', 'enabled' => true],
            ['key' => 'push', 'name' => 'Push Notifications', 'description' => 'App alerts for orders, offers and reminders', 'enabled' => false],
        ];

        foreach ($channels as $row) {
            NotificationChannel::updateOrCreate(['key' => $row['key']], $row);
        }

        $templates = [
            ['key' => 'order_confirmation', 'name' => 'Order Confirmation'],
            ['key' => 'order_shipped', 'name' => 'Order Shipped'],
            ['key' => 'order_delivered', 'name' => 'Order Delivered'],
            ['key' => 'order_cancelled', 'name' => 'Order Cancelled'],
            ['key' => 'refund_processed', 'name' => 'Refund Processed'],
            ['key' => 'seller_kyc_approved', 'name' => 'Seller KYC Approved'],
            ['key' => 'settlement_paid', 'name' => 'Settlement Paid'],
            ['key' => 'welcome_email', 'name' => 'Welcome Email'],
        ];

        foreach ($templates as $row) {
            NotificationTemplate::updateOrCreate(
                ['key' => $row['key']],
                $row + [
                    'subject' => $row['name'],
                    'body' => 'Hi {{customer_name}}, your order {{order_id}} update: '.$row['name'].'. Track it anytime from your account.',
                    'variables' => ['customer_name', 'order_id', 'tracking_link'],
                ]
            );
        }
    }
}

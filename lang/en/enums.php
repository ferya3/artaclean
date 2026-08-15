<?php

declare(strict_types=1);

return [
    'power_source' => [
        'electric' => 'Mains electric',
        'battery' => 'Battery',
        'petrol' => 'Petrol',
        'diesel' => 'Diesel',
        'lpg' => 'LPG',
        'pneumatic' => 'Pneumatic',
        'manual' => 'Manual',
    ],

    'operator_type' => [
        'walk_behind' => 'Walk-behind',
        'ride_on' => 'Ride-on',
        'handheld' => 'Handheld',
        'stationary' => 'Stationary',
        'towed' => 'Towed',
    ],

    'stock_status' => [
        'in_stock' => 'In stock',
        'on_order' => 'On order',
        'out_of_stock' => 'Out of stock',
        'discontinued' => 'Discontinued',
    ],

    'lead_status' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ],

    'lead_source' => [
        'quote' => 'Quote request',
        'contact' => 'Contact form',
        'calculator' => 'Sizing calculator',
        'catalog' => 'Catalog download',
        'whatsapp' => 'WhatsApp',
        'rental' => 'Rental',
        'phone' => 'Phone call',
        'other' => 'Other',
    ],

    'quote_status' => [
        'pending' => 'Pending',
        'in_review' => 'In review',
        'sent' => 'Sent',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
    ],

    'quote_type' => [
        'purchase' => 'Purchase',
        'rental' => 'Rental',
    ],

    'rental_period' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ],

    'download_type' => [
        'catalog' => 'Catalog',
        'manual' => 'User manual',
        'datasheet' => 'Datasheet',
        'certificate' => 'Certificate',
        'spare_parts' => 'Spare parts list',
        'software' => 'Software',
    ],

    'order_status' => [
        'draft' => 'Draft',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ],

    'payment_status' => [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially paid',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ],

    'dealer_tier' => [
        'bronze' => 'Bronze',
        'silver' => 'Silver',
        'gold' => 'Gold',
        'platinum' => 'Platinum',
    ],

    'attribute_type' => [
        'select' => 'Single select',
        'multiselect' => 'Multi select',
        'number' => 'Number',
        'boolean' => 'Yes / No',
    ],

    'machine_class' => [
        'manual' => 'Polisher or handheld machine',
        'walk_behind_compact' => 'Compact walk-behind scrubber',
        'walk_behind_large' => 'Industrial walk-behind scrubber',
        'ride_on' => 'Ride-on scrubber',
        'ride_on_heavy' => 'Heavy ride-on sweeper or scrubber',
    ],
];

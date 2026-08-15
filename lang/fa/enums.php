<?php

declare(strict_types=1);

return [
    'power_source' => [
        'electric' => 'برقی',
        'battery' => 'باتری‌شارژی',
        'petrol' => 'بنزینی',
        'diesel' => 'دیزلی',
        'lpg' => 'گازی (LPG)',
        'pneumatic' => 'بادی',
        'manual' => 'دستی',
    ],

    'operator_type' => [
        'walk_behind' => 'دستی (پیاده‌رو)',
        'ride_on' => 'سرنشین‌دار',
        'handheld' => 'قابل حمل',
        'stationary' => 'ثابت',
        'towed' => 'یدک‌کش',
    ],

    'stock_status' => [
        'in_stock' => 'موجود',
        'on_order' => 'سفارشی',
        'out_of_stock' => 'ناموجود',
        'discontinued' => 'توقف تولید',
    ],

    'lead_status' => [
        'new' => 'جدید',
        'contacted' => 'تماس گرفته شد',
        'qualified' => 'واجد شرایط',
        'proposal' => 'ارسال پیشنهاد',
        'negotiation' => 'مذاکره',
        'won' => 'موفق',
        'lost' => 'ناموفق',
    ],

    'lead_source' => [
        'quote' => 'استعلام قیمت',
        'contact' => 'فرم تماس',
        'calculator' => 'محاسبه‌گر',
        'catalog' => 'دانلود کاتالوگ',
        'whatsapp' => 'واتساپ',
        'rental' => 'اجاره',
        'phone' => 'تماس تلفنی',
        'other' => 'سایر',
    ],

    'quote_status' => [
        'pending' => 'در انتظار',
        'in_review' => 'در حال بررسی',
        'sent' => 'ارسال شد',
        'accepted' => 'پذیرفته شد',
        'rejected' => 'رد شد',
        'expired' => 'منقضی',
    ],

    'quote_type' => [
        'purchase' => 'خرید',
        'rental' => 'اجاره',
    ],

    'rental_period' => [
        'daily' => 'روزانه',
        'weekly' => 'هفتگی',
        'monthly' => 'ماهانه',
    ],

    'download_type' => [
        'catalog' => 'کاتالوگ',
        'manual' => 'دفترچه راهنما',
        'datasheet' => 'دیتاشیت فنی',
        'certificate' => 'گواهینامه',
        'spare_parts' => 'لیست قطعات یدکی',
        'software' => 'نرم‌افزار',
    ],

    'order_status' => [
        'draft' => 'پیش‌نویس',
        'confirmed' => 'تأیید شده',
        'processing' => 'در حال آماده‌سازی',
        'shipped' => 'ارسال شده',
        'delivered' => 'تحویل شده',
        'cancelled' => 'لغو شده',
    ],

    'payment_status' => [
        'unpaid' => 'پرداخت نشده',
        'partial' => 'پرداخت جزئی',
        'paid' => 'پرداخت شده',
        'refunded' => 'مسترد شده',
    ],

    'dealer_tier' => [
        'bronze' => 'برنز',
        'silver' => 'نقره‌ای',
        'gold' => 'طلایی',
        'platinum' => 'پلاتینیوم',
    ],

    'attribute_type' => [
        'select' => 'انتخاب تکی',
        'multiselect' => 'انتخاب چندگانه',
        'number' => 'عددی',
        'boolean' => 'بله / خیر',
    ],

    'machine_class' => [
        'manual' => 'پولیشر یا دستگاه دستی',
        'walk_behind_compact' => 'اسکرابر دستی جمع‌وجور',
        'walk_behind_large' => 'اسکرابر دستی صنعتی',
        'ride_on' => 'اسکرابر سرنشین‌دار',
        'ride_on_heavy' => 'سوییپر یا اسکرابر سنگین سرنشین‌دار',
    ],
];

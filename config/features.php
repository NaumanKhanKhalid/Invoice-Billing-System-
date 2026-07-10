<?php

/*
|--------------------------------------------------------------------------
| Feature Toggles Registry
|--------------------------------------------------------------------------
| Har feature ko tenant apni Settings se on/off kar sakta hai.
| 'shop_types' => null  = sab shop types ke liye available
| 'default'    => feature ki default state (jab tak shop ne change na ki ho)
| Setting key format: feature_{key}  (value '1' / '0')
*/

return [
    'receipt_print' => [
        'min_plan'    => 'basic',
        'label'       => 'Receipt Printing',
        'description' => 'Receipts par Print button (thermal/A4 printer ke liye)',
        'icon'        => 'printer',
        'shop_types'  => null,
        'default'     => true,
    ],
    'whatsapp_share' => [
        'min_plan'    => 'pro',
        'label'       => 'WhatsApp Share',
        'description' => 'Bills, receipts aur fee reminders WhatsApp par bhejna',
        'icon'        => 'message-circle',
        'shop_types'  => null,
        'default'     => true,
    ],
    'quotations' => [
        'min_plan'    => 'pro',
        'label'       => 'Quotations',
        'description' => 'Customer ko estimate/quote banana',
        'icon'        => 'file-text',
        'shop_types'  => ['hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'open_tabs' => [
        'min_plan'    => 'pro',
        'label'       => 'Hold Sales (Running Bills)',
        'description' => 'POS par sale hold karo — customer ke items add hote rahen, akhir mein final bill',
        'icon'        => 'receipt',
        'shop_types'  => ['hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'udhar_book' => [
        'min_plan'    => 'basic',
        'label'       => 'Udhar Book',
        'description' => 'Customers ko udhaar dena aur wasooli track karna',
        'icon'        => 'book-open',
        'shop_types'  => ['chicken', 'hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'reports' => [
        'min_plan'    => 'pro',
        'label'       => 'Reports & Analytics',
        'description' => 'Sales, profit, stock aur expense reports',
        'icon'        => 'bar-chart-3',
        'shop_types'  => ['chicken', 'hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'day_closing' => [
        'min_plan'    => 'basic',
        'label'       => 'Day Closing',
        'description' => 'Din ke akhir mein cash milana aur din band karna',
        'icon'        => 'moon',
        'shop_types'  => ['chicken', 'hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'expenses' => [
        'min_plan'    => 'basic',
        'label'       => 'Expenses',
        'description' => 'Rozana kharche record karna',
        'icon'        => 'wallet',
        'shop_types'  => null,
        'default'     => true,
    ],
    'staff_module' => [
        'min_plan'    => 'basic',
        'label'       => 'Staff & Salaries',
        'description' => 'Staff members aur unki salary ka hisaab',
        'icon'        => 'hard-hat',
        'shop_types'  => null,
        'default'     => true,
    ],
    'barcode_scanner' => [
        'min_plan'    => 'pro',
        'label'       => 'Barcode Camera Scanner',
        'description' => 'POS par phone camera se barcode scan karna',
        'icon'        => 'scan-line',
        'shop_types'  => ['hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'stock_guard' => [
        'min_plan'    => 'pro',
        'label'       => 'Stock Guard (Negative Stock Block)',
        'description' => 'Jitna stock hai us se zyada sale nahi hone degi (off = udhaar stock allow)',
        'icon'        => 'shield-check',
        'shop_types'  => ['hardware', 'mobile', 'bike', 'general', 'medical'],
        'default'     => true,
    ],
    'repairs' => [
        'min_plan'    => 'pro',
        'label'       => 'Repair Job Cards',
        'description' => 'Repairing ka kaam — job card banao, advance lo, ready par WhatsApp karo',
        'icon'        => 'wrench',
        'shop_types'  => ['mobile', 'hardware', 'bike'],
        'default'     => true,
    ],
    'audit_log' => [
        'min_plan'    => 'business',
        'label'       => 'Activity Log (Audit Trail)',
        'description' => 'Kis user ne kya add/edit/delete kiya — sab ka record',
        'icon'        => 'history',
        'shop_types'  => null,
        'default'     => true,
    ],
];

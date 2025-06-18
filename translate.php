<?php

function translateBladeFiles($directory) {
    $translations = [
        'Overview' => "{{ __('dashboard.overview') }}",
        'Dashboard' => "{{ __('dashboard.dashboard') }}",
        'Add new Product' => "{{ __('dashboard.add_new_product') }}",
        'Create new order' => "{{ __('dashboard.create_new_order') }}",
        'Products' => "{{ __('dashboard.products') }}",
        'categories' => "{{ __('dashboard.categories') }}",
        'Orders' => "{{ __('dashboard.orders') }}",
        'completed' => "{{ __('dashboard.completed') }}",
        'Purchases' => "{{ __('dashboard.purchases') }}",
        'today' => "{{ __('dashboard.today') }}",
        'Quotations' => "{{ __('dashboard.quotations') }}",
    ];

    $files = glob($directory . '/*.blade.php');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        foreach ($translations as $english => $french) {
            $content = str_replace($english, $french, $content);
        }
        
        file_put_contents($file, $content);
        echo "Traduit : " . basename($file) . "\n";
    }
}

// Utilisation
translateBladeFiles('resources/views');
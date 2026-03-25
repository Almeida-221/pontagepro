<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::whereIn('role', ['agent_securite','gerant_securite','company_admin'])
    ->get(['id','name','phone','pin_code']);

foreach ($users as $u) {
    echo $u->id.' '.$u->name.' ('.$u->phone.')'.PHP_EOL;
    $found = false;
    for ($i = 0; $i <= 9999; $i++) {
        $pin = str_pad($i, 4, '0', STR_PAD_LEFT);
        if (Illuminate\Support\Facades\Hash::check($pin, $u->pin_code)) {
            echo "  => PIN trouvé: $pin".PHP_EOL;
            $found = true;
            break;
        }
    }
    if (!$found) echo "  => PIN introuvable (hash non-standard ?)".PHP_EOL;
}

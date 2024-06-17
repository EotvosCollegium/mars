<?php

return [
    'report' => 'Hiba/kérés bejelentése',
    'report_long_description' => 'Olyan hibákat vagy hiányosságokat jelents itt, amelyek az ' . config('app.name') . ' kódját érintik. Amennyiben konfigurációs probléma van vagy személyes adataid hibásak, keresd a rendszergazdákat <a href="mailto:' . config('contacts.mail_sysadmin') . '">e-mail címükön</a>. Az alábbi formon megejtett bejelentések automatikusan rögzítődnek az ' . config('app.name') . ' <b>nyilvánosan elérhető</b> <a href="https://github.com/' . config('github.repo') . '/issues">fejlesztői hibakövetőjében</a>.',
    'view' => 'Itt nézheted meg és követheted nyomon a bejelentésed állapotát.',
    'select_type' => 'Válaszd ki a bejelentés típusát',
    'type_bug' => 'Hiba bejelentés',
    'type_feature' => 'Új funkció kérés',
];

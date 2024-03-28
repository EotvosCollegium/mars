<?php

return [
    'report' => 'Report an issue',
    'report_long_description' => 'Here you can make bug reports and feature requests regarding the ' . config('app.name') . ' codebase. If you are experiencing configuration errors, or there are problems with your personal information, please report it to the system administrators via e-mail using <a href="mailto:' . config('contacts.mail_sysadmin') . '">their e-mail address</a>. Issues reported on the form below will be automatically published on the <a href="https://github.com/' . config('github.repo') . '">github repo for this software</a>.',
    'view' => 'You can view and track your issue here.',
    'select_type' => 'Select the type of issue you are reporting',
    'type_bug' => 'Bug report',
    'type_feature' => 'Feature request',
];

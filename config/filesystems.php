<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'printing' => [
            'driver' => 'local',
            'root' => storage_path('app/printing_queue'),
        ],

        'epistola' => [
            'driver' => 'local',
            'root' => public_path('img/epistola'),
        ],

        'latex' => [
            'driver' => 'local',
            'root' => storage_path('app/latex'),
        ],

        'google' => [ // student council files
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folder' => env('GOOGLE_DRIVE_FOLDER'), // without folder is root of drive or team drive
            //'teamDriveId' => env('GOOGLE_DRIVE_TEAM_DRIVE_ID'),
        ],

        'google_admin' => [ // sys admin files - mostly backups
            'driver' => 'google',
            'clientId' => env('GOOGLE_ADMIN_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_ADMIN_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_ADMIN_DRIVE_REFRESH_TOKEN'),
            'folder' => env('GOOGLE_ADMIN_DRIVE_FOLDER'), // without folder is root of drive or team drive
            //'teamDriveId' => env('GOOGLE_DRIVE_TEAM_DRIVE_ID'),
        ],

        'backup' => [
            'driver' => 'local',
            'root' => env('BACKUP_PATH')
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

    ],

    /*
    * Symbolic links
    */

    'links' => [
        public_path('avatars')  => storage_path('app/avatars'),
        public_path('receipts') => storage_path('app/receipts'),
        public_path('uploads')  => storage_path('app/uploads'),
    ]

];

<?php

/**
 *  app config
 *      - 所有 /core/config/ 面的設定值將有機會被 /config.php 覆蓋
 *      - 請不要將私密的資料放置於此
 *
 *  example:
 *      conf('app.env');
 *
 */
return [

    /**
     *  Environment
     *
     *      training    - 開發者環境
     *      production  - 正式環境
     */
    'env' => 'production',

    /**
     *  project info
     */
    'project' => [
        'name' => 'Project Name',
    ],

    /**
     *  timezone
     *
     *      +0 => UTC
     *      -7 => America/Los_Angeles
     *      +8 => Asia/Taipei
     */
    'timezone' => 'America/Los_Angeles',

    /**
     *  backup path
     */
    'backup_path' => '/var/backup',

];

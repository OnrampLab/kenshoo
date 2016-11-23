<?php

    /**
     *  kenshoo 當發生問題中斷之後
     *  可以呼叫該程式
     *  該程式會在一段時間後重新再執行一次 kenshoo 程式
     */
    header('Content-Type: text/html; charset=utf-8');

    //
    include_once '../config.php';
    include_once 'QueueBrg.php';
    include_once 'QueueBrgGearmanWorker.php';

    // job core
    include_once 'failCall.core.php';

    //
    $worker = QueueBrg::factoryWorker();
    $worker->addFunction('failCall');
    $worker->run();

    function failCall_worker( $job )
    {
        perform( unserialize($job->workload()) );
    }


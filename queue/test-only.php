<?php

    header('Content-Type: text/html; charset=utf-8');

    //
    include_once '../config.php';
    include_once 'QueueBrg.php';
    include_once 'QueueBrgGearmanClient.php';

    $client = QueueBrg::factoryClient();
    $client->push('failCall', array() );


<?php

    $client = QueueBrg::factoryClient();
    $client->push('failCall', array() );


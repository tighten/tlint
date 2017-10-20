<?php

namespace Tighten;

function version()
{
    return json_decode(file_get_contents(__DIR__ . '/../composer.json'), true)['version'];
}

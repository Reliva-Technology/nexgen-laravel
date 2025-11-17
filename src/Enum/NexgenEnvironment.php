<?php

namespace Reliva\Nexgen\Enum;

enum NexgenEnvironment: string
{
    case SANDBOX = 'sandbox';
    case PRODUCTION = 'production';
    case CUSTOM = 'custom';
}
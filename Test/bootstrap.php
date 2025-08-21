<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

// Register this module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Aligent_LlmsTxt',
    dirname(__DIR__)
);

// Set timezone for tests
date_default_timezone_set('UTC');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Memory limit
ini_set('memory_limit', '-1');
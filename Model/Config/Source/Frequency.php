<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Frequency implements OptionSourceInterface
{
    public const string FREQUENCY_DAILY = 'daily';
    public const string FREQUENCY_WEEKLY = 'weekly';
    public const string FREQUENCY_MONTHLY = 'monthly';
    public const string FREQUENCY_YEARLY = 'yearly';
    public const string FREQUENCY_NEVER = 'never';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::FREQUENCY_NEVER, 'label' => __('Never (Manual Only)')],
            ['value' => self::FREQUENCY_DAILY, 'label' => __('Daily')],
            ['value' => self::FREQUENCY_WEEKLY, 'label' => __('Weekly')],
            ['value' => self::FREQUENCY_MONTHLY, 'label' => __('Monthly')],
            ['value' => self::FREQUENCY_YEARLY, 'label' => __('Yearly')]
        ];
    }
}

<?php

namespace App\Constants;

class  ChartAttributes
{
    public const LAST_EDITOR = 1;
    public const OWNER = 2;
    public const SITE = 3;
    public const SITE_GROUP = 4;
    public const DATE_CONDUCTED = 5;
    public const ASSET = 6;
    public const STATUS = 7;
    public const TEMPLATE = 8;
    public const QUESTION = 9;
    public const ASSIGNEE = 10;
    public const START_DATE = 11;
    public const SCHEDULE = 12;
    public const SCHEDULE_STATUS = 13;
    public const PRIORITY = 14;
    public const DATE_CREATED = 15;
    public const TASK_STATUS = 16;
    public const ISSUE_DATE_CREATED = 17;
    public const ISSUE_CATEGORY = 18;
    public const ISSUE_PRIORITY = 19;
    public const ISSUE_STATUS = 20;
    public const RESOURCE_DATE = 21;

    public static function toArray(): array
    {
        return [
            ['last_editor' => self::LAST_EDITOR],
            ['owner' => self::OWNER],
            ['site' => self::SITE],
            ['site_group' => self::SITE_GROUP],
            ['date_conducted' => self::DATE_CONDUCTED],
            ['asset' => self::ASSET],
            ['status' => self::STATUS],
            ['template' => self::TEMPLATE],
            ['question' => self::QUESTION],
            ['assignee' => self::ASSIGNEE],
            ['start_date' => self::START_DATE],
            ['schedule' => self::SCHEDULE],
            ['schedule_status' => self::SCHEDULE_STATUS],
        ];
    }
}

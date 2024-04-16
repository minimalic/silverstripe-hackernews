<?php

namespace minimalic\HackerNews\Models;

use SilverStripe\ORM\DataObject;

class HackerNewsItem extends DataObject
{

    private static string $table_name = 'HackerNewsItem';

    private static $singular_name = 'Hacker News Item';
    private static $plural_name = 'Hacker News Items';

    private static $db = [
        'OriginID' => 'Int',
        'Title' => 'Varchar',
        'PostDatetime' => 'Datetime',
        'Url' => 'Varchar',
        'Color' => 'Varchar',
    ];

    private static $summary_fields = [
        'OriginID' => 'News ID',
        'Title' => 'Title',
        'Url' => 'URL',
        'PostDatetime' => 'Date',
        'Color' => 'Color HSL',
    ];

    // use ASC for ascendent order
    // private static $default_sort = 'PostDatetime DESC';

    public function getHoverColor() {
        $hoverColor = '0,0%,0%';
        $baseColor = $this->Color;
        if ($baseColor) {
            $hsl = explode(',', $baseColor);
            $l = str_replace('%', '', $hsl[2]);
            $l -= 4;
            $hoverColor = "{$hsl[0]},{$hsl[1]},{$l}%";
        }
        return $hoverColor;
    }

    // public function getColor() {
    //     $colors = [
    //         'black',
    //         'blue',
    //         'green',
    //         'pink',
    //         'yellow',
    //     ];
    //     shuffle($colors);
    //     $color = $colors[0];
    //     return $color;
    // }

}

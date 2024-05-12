<?php

namespace minimalic\HackerNews;

use SilverStripe\View\ViewableData;
use minimalic\HackerNews\Models\HackerNewsItem;

class HackerNewsBanner extends ViewableData {

    /**
     * Base scroll speed. Default is 60 if not set.
     *
     * @int
     */
    private static $banner_speed;

    // return list of items
    public function getHackerNewsItems() {
        $newsItems = HackerNewsItem::get();
        if (count($newsItems) > 0) {
            return $newsItems;
        }
        return null;
    }

    // return news list into template
    public function HackerNews() {
        $hackerNewsItems = $this->getHackerNewsItems();
        $hackerNewsBannerSpeed = 60;

        if (intval($this->config()->get('banner_speed')) > 0) {
            $hackerNewsBannerSpeed = intval($this->config()->get('banner_speed'));
        }

        return $this->customise([
            'HackerNewsItems' => $hackerNewsItems,
            'HackerNewsBannerSpeed' => $hackerNewsBannerSpeed,
        ])->renderWith('Layout/HackerNews');
    }

}

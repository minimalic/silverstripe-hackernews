<?php

namespace minimalic\HackerNews;

use SilverStripe\View\ViewableData;
use minimalic\HackerNews\Models\HackerNewsItem;

class HackerNewsBanner extends ViewableData {

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
        return $this->customise(['HackerNewsItems' => $hackerNewsItems])->renderWith('Layout/HackerNews');
    }

}

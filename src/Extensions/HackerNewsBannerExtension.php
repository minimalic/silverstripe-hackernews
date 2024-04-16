<?php

namespace minimalic\HackerNews\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

use minimalic\HackerNews\HackerNewsBanner;

class HackerNewsBannerExtension extends Extension {

    public function HackerNews() {
        $banner = new HackerNewsBanner();

        if ($banner->HackerNews()) {

            Requirements::javascript('minimalic/silverstripe-hackernews: client/dist/js/hackernewsscroll.js');
            Requirements::css('minimalic/silverstripe-hackernews: client/dist/css/hackernews.css');

            return $banner->HackerNews();
        }
    }

}

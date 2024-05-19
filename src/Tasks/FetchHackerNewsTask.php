<?php

namespace minimalic\HackerNews\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\Control\Email\Email;
// use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\GraphQL\Schema\Logger;

use minimalic\HackerNews\Models\HackerNewsItem;

// COLORS FROM: silverstripe-graphql/src/Schema/Logger.php
// const BLACK = "\033[30m";
// const RED = "\033[31m";
// const GREEN = "\033[32m";
// const YELLOW = "\033[33m";
// const BLUE = "\033[34m";
// const MAGENTA = "\033[35m";
// const CYAN = "\033[36m";
// const WHITE = "\033[37m";
// const RESET = "\033[0m";


class FetchHackerNewsTask extends BuildTask {
    protected $title = "Fetch Hacker News Stories";
    protected $description = "Fetches the latest news stories from Hacker News API and stores them in the database.";

    private static $segment = 'FetchHackerNewsTask';

    // block next request for n minutes
    private static $repeat_block_minutes = 2;

    // posts count
    private static $posts_to_load = 20;

    private static $email_message_enable = false;

    // send the email only at night hours (between 12 pm and 6 am, server time)
    private static $email_message_nightly = false;

    private static $email_message_from;

    private static $email_message_to;

    private $apiBaseUrl = "https://hacker-news.firebaseio.com/v0";

    public function run($request) {

        $fetchLog = "";
        $logger = Injector::inst()->get(Logger::class);

        $logger->output("Starting task...\n");

        // check time difference to last fetch
        $repeatTime = $this->config()->get('repeat_block_minutes');
        $mostRecentItem = HackerNewsItem::get()->sort('Created', 'DESC')->first();
        // if ($mostRecentItem && strtotime($mostRecentItem->Created) > strtotime('-6 hours')) {
        if ($mostRecentItem && strtotime($mostRecentItem->Created) > strtotime('-' . $repeatTime . ' minutes')) {
            $logger->info("It has been less than {$repeatTime} minutes since the last fetch at {$mostRecentItem->Created}. Exiting to avoid too many requests.\n");
            return;
        }

        // get story list
        $topStoriesUrl = "{$this->apiBaseUrl}/topstories.json";
        $topStoryIDs = json_decode(file_get_contents($topStoriesUrl), true);

        if (!is_array($topStoryIDs)) {
            $logger->alert("No stories loaded");
            return;
        }

        $storiesCount = count($topStoryIDs);
        $fetchLog .= "Fetched {$storiesCount} stories array<br><br>";
        $logger->info("Fetched {$storiesCount} stories array");

        // clear existing items to overwrite with fresh data
        DB::query("TRUNCATE TABLE \"HackerNewsItem\"");

        $fetchLog .= "Removed legacy stories from database<br><br>";
        $logger->notice("Removed legacy stories from database");

        // process only the first x stories
        $storiesLimit = $this->config()->get('posts_to_load');
        $topStoryIDs = array_slice($topStoryIDs, 0, $storiesLimit);

        $fetchLog .= "Fetching first {$storiesLimit} stories...<br><br>";
        $logger->info("Fetching first {$storiesLimit} stories...");

        // loop over stories
        foreach ($topStoryIDs as $storyID) {
            $storyUrl = "{$this->apiBaseUrl}/item/{$storyID}.json";
            $storyData = json_decode(file_get_contents($storyUrl), true);

            // ensure valid story data
            if (!is_array($storyData)) {
                continue;
            }

            if (!array_key_exists('url', $storyData) || !array_key_exists('time', $storyData) || !array_key_exists('title', $storyData) ||  !array_key_exists('id', $storyData)) {
                $fetchLog .= "Story {$storyID} hasn't enough data, skipping...<br><br>";
                $logger->warning("Story {$storyID} hasn't enough data, skipping...");
                continue;
            }

            // convert *NIX timestamp to a valid DateTime format
            // $postDate = DB::get_conn()->formattedDatetime(strtotime("@{$storyData['time']}"), 'UTC');
            $postDate = date('Y-m-d H:i:s', $storyData['time']);
            $postColor = $this->generateColor($storyData['title']);

            $fetchLog .= "Writing story with ID <strong>" . $storyData['id'] . "</strong> into database: <a href=\"" . $storyData['url'] . "\">" . $storyData['title'] . "</a><br>";
            // print("Writing story with ID " . $storyData['id'] . " into database: " . $storyData['title'] . "<br>\n\n");
            $logger->output("Writing story with ID {$storyData['id']} into database: {$storyData['title']}");

            // create DB item and populate data
            $newItem = HackerNewsItem::create();
            $newItem->OriginID = $storyData['id'];
            $newItem->Title = $storyData['title'];
            $newItem->Url = $storyData['url'] ?? '';
            $newItem->PostDatetime = $postDate;
            $newItem->Color = $postColor;
            $newItem->write();

            $existingItem = HackerNewsItem::get()->find('OriginID', $storyData['id']);
            if (!$existingItem) {
                $fetchLog .= "WARNING: story with ID <strong>" . $storyData['id'] . "</strong> is not beeing written into database!<br><br>";
                $logger->warning("Story with ID {$storyData['id']} is not beeing written into database!");
            } else {
                $fetchLog .= "Story with ID <strong>" . $storyData['id'] . "</strong> successfuly written.<br><br>";
                $logger->info("Story with ID {$storyData['id']} successfuly written.");
            }
        }

        $fetchLog .= "Stories array fetched...<br><br>";
        $logger->output("Stories array fetched...", "SUCCESS", Logger::GREEN);

        // check for night hour
        $allowEmail = true;

        if ($this->config()->get('email_message_nightly')) {
            $currentHour = intval(date('H'));

            if ($currentHour < 0 || $currentHour > 5) {
                $allowEmail = false;

                $fetchLog .= "Skipping email - only allowed at night time.<br>";
                $logger->info("Skipping email - only allowed at night time.");
            }
        }

        // send email with fetched stories
        if ($allowEmail && $this->config()->get('email_message_enable') && $this->config()->get('email_message_to') != '') {
            $logger->output("Trying to compose an email...");

            $emailFrom = $this->config()->get('email_message_from');
            if (!$emailFrom || $emailFrom == '') {
                $emailFrom = Email::config()->get('admin_email');
            }

            $emailTo = $this->config()->get('email_message_to');
            if (!$emailTo || $emailTo == '') {
                $logger->warning("No receiver email address provided. Please config the: custom_email_to");
            }

            $emailSubject = 'Fetch Hacker News';
            $emailBody = "News fetch done:<br><br>" . $fetchLog . "<br>";

            if ($emailFrom && $emailFrom != '' && $emailTo && $emailSubject && $emailBody) {
                $logger->output("Trying to send an email...");

                $email = Email::create($emailFrom, $emailTo, $emailSubject, $emailBody);
                $email->send();
            }
        }

        $logger->output("Task done.\n");
    }

    // generate HSL colors for known brands
    public function generateColor($postTitle) {
        $h = rand(0, 360);
        $s = rand(80, 100);
        $l = rand(20, 45);
        $hsl = "{$h},{$s}%,{$l}%";

        $title = strtolower($postTitle);
        $title = ' ' . $title . ' ';
        if (str_contains($title, 'apple')) {
            $hsl = "0,0%,10%";
        } elseif (str_contains($title, 'nvidia') || str_contains($title, ' cuda ')) {
            $hsl = "82,100%,36%";
        } elseif (str_contains($title, ' amd ')) {
            $hsl = "0,0%,10%";
        } elseif (str_contains($title, ' intel ')) {
            $hsl = "203,86%,41%";
        } elseif (str_contains($title, ' ibm ')) {
            $hsl = "200,100%,30%";
        } elseif (str_contains($title, 'redhat') || str_contains($title, 'red hat')) {
            $hsl = "360,100%,40%";
        } elseif (str_contains($title, ' arch ')) {
            $hsl = "200,80%,46%";
        } elseif (str_contains($title, 'cloudflare')) {
            $hsl = "27,90%,54%";
        }

        return $hsl;
    }

}

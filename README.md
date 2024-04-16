# Silverstripe HackerNews - slider with Y Combinator's Hacker News

Silverstripe HackerNews is a news ticker (animated banner) with tech news fetched by using Hacker News API.

Working demo on on [iloveunix.com](https://iloveunix.com) at site's bottom.

## Requirements

* Compatible with Silverstripe versions 4 and 5
* Optionally Bootstrap


## Installation


### Composer

```sh
composer require minimalic/silverstripe-hackernews
```


### Rebuild DB

Rebuild DB with your URL and `dev/build?flush=all` or shell:

```sh
vendor/bin/sake dev/build "flush=all"
```


### Fetch Hacker News

Fetch first news with your URL and `dev/tasks/FetchHackerNewsTask` or shell:
```sh
vendor/bin/sake dev/tasks/FetchHackerNewsTask
```


### optional Bootstrap extension

Install using Composer:
```sh
composer require minimalic/silverstripe-bootloader
```

## Usage

Simply put `$HackerNews` inside your template, for example `Footer.ss`:

```html
<div class="container-fluid g-0 f-hacker-news">
    $HackerNews
    <div class="m-2 text-end text-black-50 f-hacker-news-contribution">
        Hacker News by <a href="https://news.ycombinator.com/news" class="text-reset">Y Combinator</a>
    </div>
</div>
```


## Configuration

Website's configuration YAML (e.g. `app/_config/hackernews.yml`):

```yaml
minimalic\HackerNews\Tasks\FetchHackerNewsTask:
  posts_to_load: 40
  email_message_enable: true
  email_message_from:
    server@domain.com: 'Server Name'
  email_message_to:
    my.mail@domain.com: 'My Name'
```


## Automatic daily news fetch

Set up cronjob (shell):

```sh
crontab -e
```

For news fetch every 6 hours starting at 0:42 local server time type in:

```cron
42 */6 * * * ~/websitepath/vendor/bin/sake dev/tasks/FetchHackerNewsTask
```

(the `~/websitepath/` is a relative path from your web user starting point - you can also use an absolute path like `/var/www/mywebsite/`)

## Contribution

Compile SCSS to CSS (developing only, shell):

```sh
cd vendor/minimalic/silverstripe-hackernews/client/src/
npm run-script watch
```


## License

See [License](LICENSE)

Copyright (c) 2024, minimalic.com - Sebastian Finke
All rights reserved.

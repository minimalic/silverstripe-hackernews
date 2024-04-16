# Silverstripe HackerNews - A Tech News Slider

News scrolling banner (animated) for Silverstripe CMS.
The extension can automatically fetch tech news/posts by using Y Combinator's Hacker News API.

Working demo on [iloveunix.com](https://iloveunix.com) at site's footer.

## Requirements

* Compatible with Silverstripe versions 4 and 5
* Bootstrap (optional, e.g. [silverstripe-bootloader](https://github.com/minimalic/silverstripe-bootloader))


## Installation


### Composer

```sh
composer require minimalic/silverstripe-hackernews
```


### Rebuild DB

Rebuild DB by appending `dev/build?flush=all` to your website's URL or by using shell:

```sh
vendor/bin/sake dev/build "flush=all"
```


### Fetch Hacker News

Fetch first news by appending `dev/tasks/FetchHackerNewsTask` to your website's URL or by using shell:

```sh
vendor/bin/sake dev/tasks/FetchHackerNewsTask
```


### Bootstrap extension (optional)

Bootstrap (CSS library) isn't required, but this extension is using Bootstrap's markups for templating.
Own Bootstrap implementation can be used as well as extensions like `silverstripe-bootloader`:

```sh
composer require minimalic/silverstripe-bootloader
```


## Usage

Simply put `$HackerNews` inside your template, for example `Footer.ss`:

```html
<div class="container-fluid g-0 f-hacker-news">
    $HackerNews
</div>
```


## Configuration

Configuration YAML (e.g. `app/_config/hackernews.yml`):

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

Set up cronjob (shell, as web user):

```sh
crontab -e
```

To fetch news every 6 hours starting at 0:42 local server time type in:

```cron
42 */6 * * * ~/mywebsite/vendor/bin/sake dev/tasks/FetchHackerNewsTask
```

(the `~/mywebsite/` is a relative path from your web user starting point - you can also use an absolute path like `/var/www/mywebsite/`)


## Contribution

To compile SCSS to CSS install `sass`, `nodemon` and `postcss` by using `npm` and run (developing only, shell):

```sh
cd vendor/minimalic/silverstripe-hackernews/client/src/
npm run-script watch
```


## License

See [License](LICENSE)

Copyright (c) 2024, minimalic.com - Sebastian Finke
All rights reserved.

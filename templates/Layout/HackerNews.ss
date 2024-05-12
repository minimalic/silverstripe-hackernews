<% if $HackerNewsItems %>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
<symbol id="boxArrowUpRight" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
</symbol>
</svg>

<style>
<% loop $HackerNewsItems.limit(50) %>
.ticker-item-{$ID} a.ticker-item-link { background-color: hsl({$Color}); }
.ticker-item-{$ID} a.ticker-item-link:hover,
.ticker-item-{$ID} a.ticker-item-link:focus { background-color: hsl({$HoverColor}); }
<% end_loop %>
</style>

<div id="newsTicker" class="news-ticker" data-hackernews-scrollspeed="{$HackerNewsBannerSpeed}">
    <div class="ticker-wrap">
        <% loop $HackerNewsItems.limit(50) %>
        <div id="tickerItem{$ID}" class="ticker-item ticker-item-{$ID}">
            <a href="{$Url}" target="_blank" class="d-flex align-items-center nav-link px-5 py-2 ticker-item-link" style="">
              <div class="">$Title</div>
              <div class="ps-2 pb-2"><svg width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right"><use xlink:href="#boxArrowUpRight"/></svg></div>
            </a>
        </div>
        <% end_loop %>
    </div>
</div>

<% end_if %>

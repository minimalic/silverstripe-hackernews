
document.addEventListener("DOMContentLoaded", function() {
    const newsTicker = document.getElementById('newsTicker'); // root container
    const tickerWrap = newsTicker.querySelector('.ticker-wrap'); // holder for ticker items
    const scrollSpeed = 70; // pixels per second

    let isPaused = false; // pausing on events
    let currentTranslateX = 0; // animation (for each item)
    let lastTimestamp = 0; // used for main scrolling animation
    let cloneCounter = 0; // used for unique IDs while cloning

    // get width of the browser
    function getTotalWidth(excludeFirstItem = false) {
        return Array.from(tickerWrap.children).slice(excludeFirstItem ? 1 : 0).reduce((total, item) => total + item.offsetWidth, 0);
    }

    // clone items to make sure there is enough items to display
    function duplicateItems() {
        const totalWidthNeeded = window.innerWidth * 2;
        let totalWidth = getTotalWidth(true);

        while (totalWidth < totalWidthNeeded) {
            const items = tickerWrap.querySelectorAll('.ticker-item');
            items.forEach((item) => {
                const clone = item.cloneNode(true);
                const baseId = item.id.replace(/clone_\d+_/i, ''); // remove existing clone prefix
                clone.id = `clone_${cloneCounter++}_${baseId}`; // construct a new unique ID
                tickerWrap.appendChild(clone);
                totalWidth += item.offsetWidth;
            });
        }
    }

    // looping items
    function reorderItems(lastSeenItemId) {
        let itemFound = false;
        const items = tickerWrap.querySelectorAll('.ticker-item');

        while (!itemFound && items.length > 0) {
            const currentItem = tickerWrap.querySelector('.ticker-item');
            if (currentItem.id === lastSeenItemId) {
                itemFound = true;
            } else {
                tickerWrap.appendChild(currentItem);
            }
        }
    }

    // start at saved position if there is any
    function setInitialScrollPosition() {
        const lastSeenItemId = localStorage.getItem('lastSeenItemId');
        if (lastSeenItemId) {
            const lastSeenItem = document.getElementById(lastSeenItemId);
            if (lastSeenItem) {
                reorderItems(lastSeenItemId);

                const savedTranslateX = parseFloat(localStorage.getItem('tickerTranslateX')) || 0;

                if (savedTranslateX < 0 && Math.abs(savedTranslateX) < lastSeenItem.offsetWidth) {
                    currentTranslateX = savedTranslateX;
                    tickerWrap.style.transform = `translateX(${currentTranslateX}px)`;
                }
            }
        }
    }

    // main scrolling function
    function scrollTicker(timestamp) {
        if (isPaused) {
            lastTimestamp = timestamp; // reset last timestamp but don't proceed with animation
            requestAnimationFrame(scrollTicker); // still need this call to check for pause state change
            return; // exit the function to avoid moving the banner
        }

        const deltaTime = lastTimestamp ? (timestamp - lastTimestamp) / 1000 : 0;
        const distance = scrollSpeed * deltaTime;

        currentTranslateX -= distance;
        tickerWrap.style.transform = `translateX(${currentTranslateX}px)`;

        const firstItem = tickerWrap.querySelector('.ticker-item');

        if (-currentTranslateX >= firstItem.offsetWidth) {
            tickerWrap.appendChild(firstItem);
            currentTranslateX += firstItem.offsetWidth;
            tickerWrap.style.transform = `translateX(${currentTranslateX}px)`;
        }

        lastTimestamp = timestamp;
        requestAnimationFrame(scrollTicker);
    }

    // stops scrolling
    function pauseScrolling() {
        isPaused = true;
    }

    // resumes scrolling
    function resumeScrolling() {
        isPaused = false;
    }


    // pause the banner while on hidden browser tab
    document.addEventListener("visibilitychange", function() {
        isPaused = (document.visibilityState !== 'visible');
        if (document.visibilityState === 'visible') {
            lastTimestamp = 0; // reset the timestamp to prevent jump to scrolled item (while in background)
        }
        // console.log(isPaused);
    });

    // run main tasks
    duplicateItems();
    setInitialScrollPosition();
    requestAnimationFrame(scrollTicker);

    // pause the banner while mouse hover
    newsTicker.addEventListener('mouseenter', pauseScrolling);
    newsTicker.addEventListener('mouseleave', resumeScrolling);

    // pause the banner on accessibility control (needs to be adjusted for tab key)
    Array.from(tickerWrap.querySelectorAll('.ticker-item')).forEach(item => {
        // item.setAttribute('tabindex', '0'); // make items focusable
        item.addEventListener('focus', pauseScrolling);
        item.addEventListener('blur', resumeScrolling);
    });

    // make sure there is enough items to display on resize
    window.addEventListener('resize', duplicateItems);

    // save current state while browsing through pages
    window.addEventListener('beforeunload', function() {
        const firstItem = tickerWrap.querySelector('.ticker-item');
        localStorage.setItem('lastSeenItemId', firstItem.id);
        localStorage.setItem('tickerTranslateX', currentTranslateX.toString());
    });
});

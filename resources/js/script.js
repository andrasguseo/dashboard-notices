const timeout = setTimeout(updateNotices, 200);

const selectors = [
    ".wrap > .notice:not(.updated):not(.hidden)",   // Generic
    "#wpbody-content > .notice:not(.updated)"       // WordPress update
];

function updateNotices() {
    // Collect notices.
    let num = document.querySelectorAll(selectors.toString());

    // Count how many notices were collected.
    let numberOfNotices = num.length;

    // Need to remove our own.
    if ( document.getElementById('notice--dashboard-notices') != null ) {
        numberOfNotices--;
    }

    // Update the number of notices in the bubbles.
    if (numberOfNotices > 0) {
        document.getElementById('notice-count').innerHTML = numberOfNotices;
        document.getElementById('admin-bar-notice-count').innerHTML = numberOfNotices;

        // Hide "hurray" if there are notices.
        const onpage = document.querySelector('.dashboard-notices #hurray');
        if ( onpage != null ) {
            onpage.remove();
        }
    } else {
        // Hide bubble in menu.
        document.getElementById('notice-count').style.display = 'none';
        // Hide bubble in toolbar.
        document.getElementById('admin-bar-notice-count').style.display = 'none';
        // Hide "Notices" notice.
        document.getElementById('notice--dashboard-notices').style.display = 'none';
    }
}
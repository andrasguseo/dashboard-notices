const timeout = setTimeout(updateNotices, 200);

const selectors = [
    ".wrap > .notice:not(.updated):not(.hidden)",   // Generic
    "#wpbody-content > .notice:not(.updated)"       // WordPress update
];

function updateNotices() {
    let num = document.querySelectorAll(selectors.toString());

    // Need to remove our own.
    let numberOfNotices = num.length-1;

    // Update the number of notices in the bubbles.
    if (num.length > 0) {
        document.getElementById('notification-count').innerHTML = num.length;
        document.getElementById('admin-bar-notification-count').innerHTML = num.length;

        // Hide "hurray" if there are notices.
        const onpage = document.querySelector('.dashboard-notices #content');
        if ( onpage != null ) {
            onpage.remove();
        }
    } else {
        // Hide bubble in menu.
        document.getElementById('notification-count').style.display = 'none';
        // Hide bubble in toolbar.
        document.getElementById('admin-bar-notification-count').style.display = 'none';
        // Hide "Notices" notice.
        document.getElementById('notice--dashboard-notices').style.display = 'none';
    }
}
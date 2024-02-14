const timeout = setTimeout(updateNotices, 200);

function updateNotices() {
    const selectors = [
        ".wrap > .notice:not(.updated):not(.hidden)",   // Generic
        "#wpbody-content > .notice:not(.updated)"       // WordPress update
    ];
    let num = document.querySelectorAll(selectors.toString());
    if (num.length > 0) {
        document.getElementById('notification-count').innerHTML = num.length;
        document.getElementById('admin-bar-notification-count').innerHTML = num.length;

        const onpage = document.querySelector('.dashboard-notifications #content');
        if ( onpage != null ) {
            onpage.remove();
        }
    } else {
        document.getElementById('notification-count').style.display = 'none';
        document.getElementById('admin-bar-notification-count').style.display = 'none';
    }
}
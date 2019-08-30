presentURL = window.location.href;
if (!presentURL.endsWith('test_start') && !presentURL.endsWith('select_customer') && !presentURL.endsWith('lab_cart')) {
    let url = document.head.querySelector('meta[name="siteurl"]');
    
    if (!url) {
        console.error('server request url not set in meta data!');
    }
    var completed = 1;
    const inter = setInterval(() => {
        if (completed == 1) {
            completed = 0;
            $.ajax({
                url: url.content,
                type: 'get',
                dataType: 'JSON',
                success: (d) => {
                    console.log('total pending tests returned from server: ', d);
                    var previouslyPendingTotal = JSON.parse(localStorage.getItem('totalPending'));
                    localStorage.setItem('totalPending', JSON.stringify(d));
                    if (d > previouslyPendingTotal) {
                        var diff = d - previouslyPendingTotal;
                        alert(`Heads up! You have ${diff} new pending tests to be attended to!`);
                    }
                },
                fail: (f) => {
                    console.log('failed to get tests status', f);
                },
                error: (e) => {
                    console.log('Unable to fetch test status from server!', e);
                },
                complete: () => {
                    completed = 1;
                }
            });
        }
    }, 1000);
}
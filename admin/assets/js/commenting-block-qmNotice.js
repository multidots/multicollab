(function (wp) {
    const { dispatch } = wp.data;
    const NOTICE_KEY = 'cf_qm_notice_dismissed';
    const DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000; // 7 days in ms

    function shouldShowNotice() {
        const dismissedAt = localStorage.getItem(NOTICE_KEY);
        if (!dismissedAt) {
            return true; // Never dismissed before
        }
        const elapsed = Date.now() - parseInt(dismissedAt, 10);
        return elapsed > DISMISS_DURATION;
    }

    function showNotice() {
        const notice = dispatch('core/notices').createNotice(
            'warning', // success, info, warning, error
            '⚠ Query Monitor is active and may impact editor performance.', // Message
            {
                isDismissible: true,
                type: 'snackbar', // snackbar | default
                onDismiss: () => {
                    // Save dismissal time
                    localStorage.setItem(NOTICE_KEY, Date.now().toString());
                },
            }
        );
        return notice;
    }

    if (shouldShowNotice()) {
        showNotice();
    }
})(window.wp);

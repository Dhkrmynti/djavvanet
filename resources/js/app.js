import './bootstrap';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// NProgress Configuration
NProgress.configure({ 
    showSpinner: false,
    trickleSpeed: 200,
    minimum: 0.1
});

// Start NProgress on page load
document.addEventListener('DOMContentLoaded', () => {
    NProgress.done();
});

// Show NProgress on any link click (except external/hash)
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (link && 
        link.href && 
        link.target !== '_blank' && 
        !link.href.includes('#') &&
        link.origin === window.location.origin
    ) {
        NProgress.start();
    }
});

// Show NProgress on form submissions
document.addEventListener('submit', () => {
    NProgress.start();
});

// Ensure it finishes if page is hung or navigation is cancelled
window.addEventListener('pageshow', () => {
    NProgress.done();
});

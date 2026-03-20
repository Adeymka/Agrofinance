import './bootstrap';

(function () {
    var meta = document.querySelector('meta[name="api-token"]');
    var token = meta && meta.getAttribute('content');
    if (token) {
        localStorage.setItem('agrofinance_token', token);
    } else {
        localStorage.removeItem('agrofinance_token');
    }
})();

import './bootstrap';
import { enqueuePending, initOfflineTransactions, randomUuidV4, refreshOfflineBanner } from './offline-transactions';

(function () {
    var meta = document.querySelector('meta[name="api-token"]');
    var token = meta && meta.getAttribute('content');
    if (token) {
        localStorage.setItem('agrofinance_token', token);
    } else {
        localStorage.removeItem('agrofinance_token');
    }
})();

(function () {
    if (!document.querySelector('meta[name="api-token"]')?.getAttribute('content')) {
        return;
    }
    initOfflineTransactions();

    window.__AF_enqueueOfflineTransaction = async function (payload) {
        const uuid = randomUuidV4();
        const body = {
            ...payload,
            client_uuid: uuid,
        };
        await enqueuePending(uuid, body);
        await refreshOfflineBanner();
    };
})();

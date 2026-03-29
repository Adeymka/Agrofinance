/**
 * File d'attente IndexedDB pour transactions créées hors ligne, synchro via API Sanctum.
 * Idempotence : client_uuid (UUID v4) côté client, traité par POST /api/v1/transactions.
 */
import axios from 'axios';

/**
 * UUID v4 — utilisable en http://IP locale (crypto.randomUUID exige souvent HTTPS ou localhost).
 */
export function randomUuidV4() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        try {
            return crypto.randomUUID();
        } catch {
            /* contexte non sécurisé */
        }
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;

        return v.toString(16);
    });
}

const DB_NAME = 'agrofinance_offline_v1';
const STORE = 'pending_transactions';
const DB_VERSION = 1;

function apiBase() {
    const meta = document.querySelector('meta[name="api-base"]');
    return (meta && meta.getAttribute('content')) ? meta.getAttribute('content').replace(/\/$/, '') : '';
}

function bearer() {
    return localStorage.getItem('agrofinance_token') || '';
}

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onerror = () => reject(req.error);
        req.onsuccess = () => resolve(req.result);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE)) {
                db.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

export async function countPending() {
    try {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readonly');
            const q = tx.objectStore(STORE).count();
            q.onsuccess = () => resolve(q.result);
            q.onerror = () => reject(q.error);
        });
    } catch {
        return 0;
    }
}

/**
 * @param {object} payload Une entrée au format API transactions[]
 */
export async function enqueuePending(clientUuid, payload) {
    if (!window.indexedDB) {
        throw new Error('IndexedDB indisponible sur ce navigateur.');
    }
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        tx.onerror = () => reject(tx.error || new Error('Transaction IndexedDB échouée'));
        const rec = {
            client_uuid: clientUuid,
            payload,
            created_at: new Date().toISOString(),
            attempts: 0,
        };
        const req = tx.objectStore(STORE).add(rec);
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error || new Error('Enregistrement local échoué'));
    });
}

async function deletePending(id) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        tx.objectStore(STORE).delete(id);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function getAllPending() {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readonly');
        const q = tx.objectStore(STORE).getAll();
        q.onsuccess = () => resolve(q.result || []);
        q.onerror = () => reject(q.error);
    });
}

/** @type {string} */
let syncExtraHint = '';

function setSyncExtraHint(msg) {
    syncExtraHint = typeof msg === 'string' ? msg : '';
}

function updateBanner(count) {
    const wrap = document.getElementById('afPendingSyncBanner');
    const main = document.getElementById('afPendingSyncMain');
    const hint = document.getElementById('afPendingSyncHint');
    const btn = document.getElementById('afPendingSyncRetry');
    if (!wrap) return;

    const hasPending = count > 0;
    const showBar = hasPending || syncExtraHint.length > 0;

    if (!showBar) {
        wrap.hidden = true;
        if (main) main.textContent = '';
        if (hint) {
            hint.textContent = '';
            hint.hidden = true;
        }
        if (btn) btn.hidden = true;

        return;
    }

    wrap.hidden = false;
    if (main) {
        main.textContent = hasPending
            ? (count === 1
                ? '1 dépense ou recette en attente d’envoi sur le serveur'
                : `${count} dépenses ou recettes en attente d’envoi sur le serveur`)
            : '';
    }
    if (hint) {
        hint.textContent = syncExtraHint;
        hint.hidden = syncExtraHint.length === 0;
    }
    if (btn) {
        btn.hidden = !hasPending;
    }
}

export async function refreshOfflineBanner() {
    const n = await countPending();
    updateBanner(n);
}

/**
 * Envoie la file vers l’API (une requête par entrée pour isoler les erreurs).
 */
export async function syncPendingQueue() {
    const base = apiBase();
    const token = bearer();
    setSyncExtraHint('');

    if (!base || !token || !navigator.onLine) {
        await refreshOfflineBanner();

        return;
    }

    const rows = await getAllPending();
    if (!rows.length) {
        await refreshOfflineBanner();

        return;
    }

    const headers = {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    for (const row of rows) {
        try {
            await axios.post(
                `${base}/transactions`,
                { transactions: [row.payload] },
                { headers }
            );
            await deletePending(row.id);
        } catch (e) {
            const status = e.response?.status;
            if (status === 401) {
                setSyncExtraHint('Session expirée. Reconnectez-vous pour envoyer les données en attente.');
                break;
            }
            if (status === 422 || status === 403) {
                await deletePending(row.id);
            }
            if (!e.response && navigator.onLine) {
                setSyncExtraHint('Connexion instable. Réessayez quand le réseau revient.');
            }
            break;
        }
    }

    await refreshOfflineBanner();
}

export function initOfflineTransactions() {
    const retryBtn = document.getElementById('afPendingSyncRetry');
    if (retryBtn) {
        retryBtn.addEventListener('click', () => {
            setSyncExtraHint('');
            syncPendingQueue();
        });
    }

    window.__AF_syncPendingQueue = syncPendingQueue;

    window.addEventListener('online', () => {
        setSyncExtraHint('');
        syncPendingQueue();
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            refreshOfflineBanner();
            if (navigator.onLine) {
                syncPendingQueue();
            }
        });
    } else {
        refreshOfflineBanner();
        if (navigator.onLine) {
            syncPendingQueue();
        }
    }
}

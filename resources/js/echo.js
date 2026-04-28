import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const configuredHost = import.meta.env.VITE_REVERB_HOST;
const resolvedHost = ['localhost', '127.0.0.1', '::1'].includes(configuredHost)
    ? window.location.hostname
    : (configuredHost || window.location.hostname);
const configuredPort = Number(import.meta.env.VITE_REVERB_PORT || 8080);
const forceTLS = (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    //wsPath: '/app',
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    //disableStats: true,
    enabledTransports: ['ws', 'wss'],
    //authEndpoint: '/broadcasting/auth',
    //withCredentials: true,
});

import axios from 'axios';

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export async function getPermissionState() {
  if (!('Notification' in window)) return 'unsupported';
  return Notification.permission;
}

export async function shouldAskForPush() {
    if (!('serviceWorker' in navigator)) return false;
    if (!('PushManager' in window)) return false;

    // Check if user dismissed it before
    if (localStorage.getItem('push_prompt_dismissed')) return false;

    // Check current permission
    const permission = Notification.permission;
    if (permission !== 'default') return false; // Already granted or denied

    // Check if standalone PWA
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    // Also consider standard fullscreen or minimal-ui as indication
    if (isStandalone || navigator.standalone) {
        return true;
    }

    // For testing/development, you might want to force it or ask always if 'default'
    // But requirement says "When started as PWA app".
    return isStandalone;
}

export async function subscribeUser() {
  if (!('serviceWorker' in navigator)) throw new Error('No Service Worker support');

  const registration = await navigator.serviceWorker.ready;

  const res = await axios.get('/api/push.php?action=vapid_public_key');
  const publicKey = res.data.publicKey;

  if(!publicKey) throw new Error('Server VAPID key missing');

  const convertedVapidKey = urlBase64ToUint8Array(publicKey);

  const subscription = await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: convertedVapidKey
  });

  await axios.post('/api/push.php', {
    action: 'subscribe',
    subscription: subscription
  });

  return subscription;
}

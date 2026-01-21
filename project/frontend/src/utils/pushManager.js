export default {
  async subscribeUser() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Push messaging is not supported');
        return false;
    }

    try {
      const registration = await navigator.serviceWorker.ready;
      const existingSubscription = await registration.pushManager.getSubscription();
      if (existingSubscription) {
          return true; // Already subscribed
      }

      // VAPID Public Key - This should ideally come from API, but for now placeholder
      // User will need to configure this in the final output
      const response = await fetch('/api/push.php?action=vapid_public_key');
      const data = await response.json();
      const applicationServerKey = this.urlBase64ToUint8Array(data.publicKey);

      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
      });

      console.log('User is subscribed:', subscription);

      // Send subscription to backend
      await fetch('/api/push.php?action=subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(subscription),
      });
      return true;

    } catch (err) {
      console.error('Failed to subscribe the user: ', err);
      return false;
    }
  },

  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }
}

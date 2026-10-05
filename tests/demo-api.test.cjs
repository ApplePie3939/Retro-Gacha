const test = require('node:test');
const assert = require('node:assert/strict');
const { createDemoApi, createPhpApi } = require('../static/js/api-client.js');

function memoryStorage() {
    const values = new Map();
    return {
        getItem: (key) => values.has(key) ? values.get(key) : null,
        setItem: (key, value) => values.set(key, String(value)),
        removeItem: (key) => values.delete(key)
    };
}

test('registration, login and logout persist in demo storage', async () => {
    const api = createDemoApi(memoryStorage());
    assert.equal((await api.getUser()).logged_in, false);
    assert.equal((await api.register('demo', 'secret')).success, true);
    assert.equal((await api.register('demo', 'other')).success, false);
    assert.equal((await api.logout()).success, true);
    assert.equal((await api.login('demo', 'wrong')).success, false);
    assert.equal((await api.login('demo', 'secret')).user.username, 'demo');
});

test('QR rewards are granted once and can purchase a coupon', async () => {
    const api = createDemoApi(memoryStorage());
    await api.register('walker', 'secret');
    assert.equal((await api.buyCoupon(3)).success, false);
    const reward = await api.scanShop(1);
    assert.equal(reward.coins, 100);
    assert.equal((await api.scanShop(1)).success, false);
    const purchase = await api.buyCoupon(3);
    assert.equal(purchase.remaining_coins, 0);
    const owned = await api.getMyCoupons();
    assert.equal(owned.my_coupons.length, 1);
    assert.equal(owned.my_coupons[0].name, '大盛り無料券');
});

test('gacha returns one of the configured shops for a logged-in user', async () => {
    const api = createDemoApi(memoryStorage());
    assert.equal((await api.drawGacha()).success, false);
    await api.register('gacha-user', 'secret');
    const result = await api.drawGacha();
    assert.equal(result.success, true);
    assert.ok(result.shop.id >= 1 && result.shop.id <= 8);
});

test('PHP adapter keeps using the existing relative API endpoints', async () => {
    const originalFetch = global.fetch;
    const requests = [];
    global.fetch = async (url, options) => {
        requests.push({ url, options });
        return { json: async () => ({ success: true }) };
    };

    try {
        const api = createPhpApi({ apiBase: '../api' });
        await api.getUser();
        await api.login('demo', 'secret');
        await api.scanShop(2);
        assert.deepEqual(requests.map((request) => request.url), [
            '../api/user.php',
            '../api/login.php',
            '../api/scan_qr.php'
        ]);
        assert.equal(JSON.parse(requests[2].options.body).shop_id, 2);
    } finally {
        global.fetch = originalFetch;
    }
});

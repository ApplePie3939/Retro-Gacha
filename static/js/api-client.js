(function (global) {
    'use strict';

    const STORAGE_KEY = 'retro-gacha-demo-state-v1';

    const SHOPS = [
        { id: 1, name: '魚と地酒とワイン りべら', genre: '酒', image: 'images/localsake_ribera.jpg', description: '落ち着いた空間で頂く和食と地酒、そしてワイン。', address: '新潟市中央区古町通9番町1475-4' },
        { id: 2, name: '豚米（とんべい）', genre: 'おにぎり', image: 'images/tonbei.jpg', description: '「人生最高のおにぎり」を掲げる、ごちそうおにぎりと豚汁の専門店。', address: '新潟県新潟市中央区古町通4番町632' },
        { id: 3, name: '麺亭', genre: 'ラーメン', image: 'images/mentei.jpg', description: '夜遅くまで営業し、飲み歩きの後や深夜の小腹を満たすのにぴったりな町中華', address: '新潟県新潟市中央区古町通8-1452-2藤和ビル1F' },
        { id: 4, name: 'シン鉄板ビストロ', genre: 'お肉', image: 'images/bisutoro.jpg', description: '品質にこだわった新潟県産和牛を鉄板で焼き上げるライブ感をお楽しみください！', address: '新潟県新潟市中央区古町通8番町1493' },
        { id: 5, name: '喜ぐち', genre: '居酒屋', image: 'images/kiguti.jpg', description: '創業は昭和40年、世代を超えて愛され続けるローカル酒場', address: '新潟県新潟市中央区古町通10番町1720' },
        { id: 6, name: 'おまかせ食堂 ななや', genre: '定食屋', image: 'images/nanaya.jpg', description: '古町で味わう“お母さん”の味', address: '新潟県新潟市中央区古町通7番町1005-3' },
        { id: 7, name: '角田屋', genre: '和菓子', image: 'images/kakudaya.jpg', description: 'ほどよい塩気の「豆大福」や、新潟らしい「笹団子」、そしてお祝い事に欠かせない「赤飯」が有名です。', address: '新潟県新潟市中央区本町通7番町1093-3' },
        { id: 8, name: 'Cafeふぅ', genre: 'カフェ', image: 'images/cafe_fuu.jpg', description: '商店街の喧騒から少し離れて、店名の通り「ふぅ」と一息つける、ゆったりとした時間が流れています。', address: '新潟県新潟市中央区本町通6番町1102-1' }
    ];

    const COUPONS = [
        { id: 1, name: '全店共通 100円引き', description: '全てのお店で使える100円割引券です。', coin_price: 300 },
        { id: 2, name: 'ワンドリンクサービス', description: '対象店舗でドリンクが1杯無料になります。', coin_price: 450 },
        { id: 3, name: '大盛り無料券', description: 'ラーメンや定食のご飯を大盛りにできます。', coin_price: 100 },
        { id: 4, name: '地酒 試飲券', description: '酒場で特別な地酒を一口試飲できます。', coin_price: 600 }
    ];

    function initialState() {
        return { version: 1, nextUserId: 1, nextUserCouponId: 1, currentUserId: null, users: [] };
    }

    function createDemoApi(storage) {
        function load() {
            try {
                const parsed = JSON.parse(storage.getItem(STORAGE_KEY));
                if (parsed && parsed.version === 1 && Array.isArray(parsed.users)) return parsed;
            } catch (error) {
                // 壊れたデモデータは安全に初期化する。
            }
            const state = initialState();
            save(state);
            return state;
        }

        function save(state) {
            storage.setItem(STORAGE_KEY, JSON.stringify(state));
        }

        function currentUser(state) {
            return state.users.find((user) => user.id === state.currentUserId) || null;
        }

        function publicUser(user) {
            return { id: user.id, username: user.username, coins: user.coins };
        }

        function requireUser(state) {
            const user = currentUser(state);
            return user ? { user } : { error: { success: false, message: 'ログインが必要です' } };
        }

        return {
            async register(username, password) {
                const state = load();
                if (!username || !password) return { success: false, message: 'ユーザー名とパスワードを入力してください' };
                if (state.users.some((user) => user.username === username)) return { success: false, message: 'そのユーザー名は既に使われています' };
                const user = { id: state.nextUserId++, username, password, coins: 0, scannedShopIds: [], coupons: [], gachaHistory: [] };
                state.users.push(user);
                state.currentUserId = user.id;
                save(state);
                return { success: true, message: '登録成功', user: publicUser(user) };
            },

            async login(username, password) {
                const state = load();
                if (!username || !password) return { success: false, message: 'ユーザー名とパスワードを入力してください' };
                const user = state.users.find((candidate) => candidate.username === username && candidate.password === password);
                if (!user) return { success: false, message: 'ユーザー名またはパスワードが違います' };
                state.currentUserId = user.id;
                save(state);
                return { success: true, message: 'ログイン成功', user: publicUser(user) };
            },

            async logout() {
                const state = load();
                state.currentUserId = null;
                save(state);
                return { success: true, message: 'ログアウトしました' };
            },

            async getUser() {
                const state = load();
                const user = currentUser(state);
                return { logged_in: Boolean(user), user: user ? publicUser(user) : null };
            },

            async drawGacha() {
                const state = load();
                const auth = requireUser(state);
                if (auth.error) return auth.error;
                const shop = SHOPS[Math.floor(Math.random() * SHOPS.length)];
                auth.user.gachaHistory.push({ shop_id: shop.id, created_at: new Date().toISOString() });
                save(state);
                return { success: true, shop: { ...shop } };
            },

            async scanShop(shopId) {
                const state = load();
                const auth = requireUser(state);
                if (auth.error) return auth.error;
                const numericId = Number(shopId);
                const shop = SHOPS.find((candidate) => candidate.id === numericId);
                if (!shop) return { success: false, message: '存在しない店舗です' };
                if (auth.user.scannedShopIds.includes(numericId)) return { success: false, message: '既にこのお店のコインは取得済みです' };
                auth.user.scannedShopIds.push(numericId);
                auth.user.coins += 100;
                save(state);
                return { success: true, message: `「${shop.name}」のQRコードを読み取り、100コインを獲得しました！`, coins: auth.user.coins, shop_name: shop.name };
            },

            async getCoupons() {
                const state = load();
                const auth = requireUser(state);
                if (auth.error) return auth.error;
                return { success: true, coupons: COUPONS.map((coupon) => ({ ...coupon })) };
            },

            async getMyCoupons() {
                const state = load();
                const auth = requireUser(state);
                if (auth.error) return auth.error;
                const myCoupons = auth.user.coupons.map((owned) => {
                    const coupon = COUPONS.find((candidate) => candidate.id === owned.coupon_id);
                    return { user_coupon_id: owned.id, is_used: owned.is_used, created_at: owned.created_at, ...coupon };
                });
                return { success: true, my_coupons: myCoupons.reverse() };
            },

            async buyCoupon(couponId) {
                const state = load();
                const auth = requireUser(state);
                if (auth.error) return auth.error;
                const coupon = COUPONS.find((candidate) => candidate.id === Number(couponId));
                if (!coupon) return { success: false, message: 'クーポンが見つかりません' };
                if (auth.user.coins < coupon.coin_price) return { success: false, message: 'コインが足りません' };
                auth.user.coins -= coupon.coin_price;
                auth.user.coupons.push({ id: state.nextUserCouponId++, coupon_id: coupon.id, is_used: 0, created_at: new Date().toISOString() });
                save(state);
                return { success: true, message: `「${coupon.name}」を購入しました！`, remaining_coins: auth.user.coins };
            }
        };
    }

    function createPhpApi(config) {
        async function request(path, options) {
            const response = await fetch(`${config.apiBase || '../api'}/${path}`, options);
            return response.json();
        }

        return {
            register: (username, password) => request('register.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username, password }) }),
            login: (username, password) => request('login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username, password }) }),
            logout: () => request('logout.php'),
            getUser: () => request('user.php'),
            drawGacha: () => request('gacha.php'),
            scanShop: (shopId) => request('scan_qr.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ shop_id: shopId }) }),
            getCoupons: () => request('coupons.php'),
            getMyCoupons: () => request('my_coupons.php'),
            buyCoupon: (couponId) => request('buy_coupon.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ coupon_id: couponId }) })
        };
    }

    const config = global.RETRO_GACHA_CONFIG || { backend: 'php', apiBase: '../api' };
    const api = config.backend === 'demo' ? createDemoApi(global.localStorage) : createPhpApi(config);
    api.mode = config.backend;
    api.assetUrl = (path) => new URL(`../${String(path).replace(/^\/+/, '')}`, global.document.baseURI).href;
    global.RetroGachaAPI = api;

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { STORAGE_KEY, SHOPS, COUPONS, createDemoApi, createPhpApi };
    }
}(typeof window !== 'undefined' ? window : globalThis));

import { img, isWeixinBrowser, currRoute, currShareRoute } from '@/utils/common'
import { onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app'
import { getShareInfo } from '@/app/api/diy';
import useSystemStore from '@/stores/system';

// #ifdef H5
import wechat from '@/utils/wechat'
// #endif

export const useShare = () => {
    let wechatOptions: any = {};
    let weappOptions: any = {};
    
    const systemStore = useSystemStore()

    const wechatInit = () => {
        if (!isWeixinBrowser()) return;
        // 初始化sdk
        wechat.init();
    }

    // 微信公众号分享
    const wechatShare = () => {
        if (!isWeixinBrowser()) return;
        wechat.share(wechatOptions);
    }

    const getQuery = () => {
        let query: any = currShareRoute().params;
        let wap_member_id = uni.getStorageSync('wap_member_id');
        if (wap_member_id) {
            query.mid = wap_member_id;
        }

        let queryStr = [];
        for (let key in query) {
            queryStr.push(key + '=' + query[key]);
        }

        return queryStr

    }

    const setShare = (options: any = {}) => {
        if (currRoute() == '' || currRoute().indexOf('app/pages/index/close') != -1 || currRoute().indexOf('app/pages/index/nosite') != -1) return;

        let queryStr = getQuery();
        let h5Link = '';
        // #ifdef H5
        h5Link = location.origin + location.pathname + (queryStr.length > 0 ? '?' + queryStr.join('&') : '');
        wechatOptions = {
            link: h5Link
        }
        // #endif
        
        // #ifdef APP-PLUS
        h5Link = systemStore.site.wap_url + currShareRoute().path + (queryStr.length > 0 ? '?' + queryStr.join('&') : '');
        wechatOptions = {
            link: h5Link
        }
        // #endif

        weappOptions = {
            path: '/' + currRoute() + (queryStr.length > 0 ? '?' + queryStr.join('&') : ''),
            query: queryStr.join('&'),
        }
        
        if (options && Object.keys(options).length) {
            if (options.wechat) {
                wechatOptions.title = options.wechat.title || ''
                wechatOptions.link = options.wechat.link || h5Link
                wechatOptions.desc = options.wechat.desc || ''
                wechatOptions.imgUrl = options.wechat.url ? img(options.wechat.url) : ''
                // wechatOptions.success = options.wechat.callback || null;
                // useSystemStore().shareCallback = options.wechat.callback || null;
                // #ifdef H5
                wechatShare()
                // #endif
            }

            if (options.weapp) {
                weappOptions.title = options.weapp.title || ''
                if (options.weapp.path) weappOptions.path = options.weapp.path
                weappOptions.imageUrl = options.weapp.url ? img(options.weapp.url) : ''
                useSystemStore().shareCallback = options.weapp.callback || null;
                // #ifdef MP-WEIXIN
                uni.setStorageSync('weappOptions', weappOptions)
                // #endif
            }
        } 
        getShareInfo({
               route: '/' + currRoute(),
               params: JSON.stringify(currShareRoute().params)
           }).then((res: any) => {
            let data = res.data;

            let wechat = data.wechat;
            if (wechat) {
                wechatOptions.title = wechat.title
                wechatOptions.desc = wechat.desc
                wechatOptions.imgUrl = wechat.url ? img(wechat.url) : ''
            } else {
                wechatOptions.title = document ? document.title : ''
                wechatOptions.desc = ''
            }
            // #ifdef H5
            wechatShare()
            // #endif

            let weapp = data.weapp;
            if (weapp) {
                weappOptions.title = weapp.title
                weappOptions.imageUrl = weapp.url ? img(weapp.url) : ''
            }
            // #ifdef MP
            if(!weappOptions.title && !weappOptions.imageUrl){
                uni.setStorageSync('weappOptions', {})
                return;
            }
            uni.setStorageSync('weappOptions', weappOptions)
            // #endif
            
            useSystemStore().$patch((state) => {
                state.shareOptions = {
                    wechatOptions,
                    weappOptions
                }
            })
        })
        
        useSystemStore().$patch((state) => {
            state.shareOptions = {
                wechatOptions,
                weappOptions
            }
        })
    }

    // 小程序分享，分享给好友
    const shareApp = (options = {}) => {
        // #ifdef MP
        return onShareAppMessage(() => {
            let config: any = uni.getStorageSync('weappOptions')
            if (!config) config = {}
            if (systemStore.shareCallback) systemStore.shareCallback();
            return {
                ...config,
                ...options
            }
        })
        // #endif
        
        // #ifdef APP-PLUS
        const weappOptions = systemStore.shareOptions.weappOptions
        const wechatOptions = systemStore.shareOptions.wechatOptions
        
        if (!weappOptions.title && !wechatOptions.title) return
        
        const shareOptions: any = {}
        if (weappOptions.title && systemStore.appConfig.weapp_original) {
            shareOptions.type = 5
            shareOptions.title = weappOptions.title
            shareOptions.imageUrl = weappOptions.imageUrl
            shareOptions.miniProgram = {
                id: systemStore.appConfig.weapp_original,
                path: weappOptions.path,
                type: 0,
                webUrl: wechatOptions.link
            }
        } else {
            shareOptions.type = 0
            shareOptions.href = wechatOptions.link
            shareOptions.title = wechatOptions.title
            shareOptions.summary = wechatOptions.desc
            shareOptions.imageUrl = wechatOptions.imgUrl
        }
        
        uni.share({
        	provider: "weixin",
            scene: "WXSceneSession",
            success: () => {
                 if (systemStore.shareCallback) systemStore.shareCallback();
            },
            ...shareOptions
        });
        // #endif
    }

    // 小程序分享，分享到朋友圈
    const shareTime = (options = {}) => {
        // #ifdef MP
        return onShareTimeline(() => {
            let config: any = uni.getStorageSync('weappOptions')
            if (!config) config = {}
            if (systemStore.shareCallback) systemStore.shareCallback();
            return {
                ...config,
                ...options
            }
        })
        // #endif
        
        // #ifdef APP-PLUS
        const wechatOptions = systemStore.shareOptions.wechatOptions
        if (wechatOptions.title) {
            uni.share({
            	provider: "weixin",
                scene: "WXSceneTimeline",
                type: 0,
                href: wechatOptions.link,
                title: wechatOptions.title,
                summary: wechatOptions.desc,
                imageUrl: wechatOptions.imgUrl,
                success: () => {
                    if (systemStore.shareCallback) systemStore.shareCallback();
                }
            });
        }
        // #endif
    }

    // 禁用当前页面的分享功能（同时支持小程序和公众号）
    const disableShare = () => {
        // 公众号（H5）禁用分享
        // #ifdef H5
        if (isWeixinBrowser()) {
            // 确保SDK初始化后再禁用
            wechat.init(() => {
                wechat.disableShare();
            });
        }
        // #endif

        // 小程序禁用分享
        // #ifdef MP-WEIXIN
        // 隐藏分享菜单（转发给朋友、朋友圈）
        uni.hideShareMenu({
            menus: ['shareAppMessage', 'shareTimeline'],
            success: () => {
                console.log('小程序分享已禁用');
            },
            fail: (err) => {
                console.error('小程序禁用分享失败：', err);
            }
        });
        // 覆盖分享方法，返回空对象
        onShareAppMessage(() => ({}));
        onShareTimeline(() => ({}));
        // #endif
    };

    return {
        wechatInit: wechatInit,
        setShare: setShare,
        onShareAppMessage: shareApp,
        onShareTimeline: shareTime,
        disableShare: disableShare,
    }
}

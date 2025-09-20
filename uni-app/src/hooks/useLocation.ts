import { onLoad } from '@dcloudio/uni-app';
import { isWeixinBrowser } from '@/utils/common';
import { getAddressByLatlng } from '@/app/api/system';
import manifestJson from '@/manifest.json';
import wechat from '@/utils/wechat'
import useSystemStore from '@/stores/system';

export function useLocation(isOpenLocation: any) {
    const systemStore = useSystemStore();

    // 表示，定位是否开启
    let isOpen = isOpenLocation;
    if (systemStore.mapConfig.is_open != 1) {
        isOpen = false;
    }

    // 监听页面加载
    const onLoadLifeCycle = (callback: any = '') => {
        onLoad((option: any) => {
            if (option && option.latng) {
                getAddressByLatlngFn(option.latng)
            }

            uni.removeStorageSync('manually_select_location_from_map');
            typeof callback == 'function' && callback(option);
        });
    }

    const init = () => {
        if (!isOpen) return false;
        // 排除手动地图选择位置的情况
        if (!uni.getStorageSync('manually_select_location_from_map')) {
            // #ifdef H5
            if (isWeixinBrowser() && systemStore.mapConfig.is_open && !uni.getStorageSync('location_address')) {
                wechat.init(() => {
                    wechat.getLocation(res => {
                        let latlng = res.latitude + ',' + res.longitude; // 纬度（浮点数，范围为90 ~ -90），经度（浮点数，范围为180 ~ -180）
                        getAddressByLatlngFn(latlng)
                    })
                });

            }
            // #endif

            // #ifndef H5
            if (systemStore.mapConfig.is_open && !uni.getStorageSync('location_address')) {
                uni.getLocation({
                    // #ifndef APP-PLUS
                    type: 'gcj02',
                    // #endif
                    success: res => {
                        let latlng = res.latitude + ',' + res.longitude; // 纬度（浮点数，范围为90 ~ -90），经度（浮点数，范围为180 ~ -180）
                        getAddressByLatlngFn(latlng);
                    },
                    fail: (res) => {
                        uni.showToast({ title: res.errMsg, icon: 'none', duration: 15000 })
                        systemStore.defaultPositionAddress = '定位失败';
                        if (res.errno) {
                            if (res.errno == 104) {
                                let msg = '用户未授权隐私权限，获取位置失败';
                                uni.showToast({ title: msg, icon: 'none' })
                            } else if (res.errno == 112) {
                                let msg = '隐私协议中未声明，获取位置失败';
                                uni.showToast({ title: msg, icon: 'none' })
                            }
                        }
                        if (res.errMsg) {
                            if (res.errMsg.indexOf('getLocation:fail') != -1 || res.errMsg.indexOf('deny') != -1 || res.errMsg.indexOf('denied') != -1) {
                                let msg = '用户未授权获取位置权限，将无法提供服务';
                                uni.showToast({ title: msg, icon: 'none' })
                            } else {
                                uni.showToast({ title: res.errMsg, icon: 'none' })
                            }
                        }
                    }
                });
            }
            // #endif
        }
    }

    // 刷新页面时需要调用的
    const refreshLocation = () => {
        if (!isOpen) return false;
        if (!uni.getStorageSync('manually_select_location_from_map') && uni.getStorageSync('location_address')) {
            if (locationStorage() && !locationStorage().is_expired) {
                systemStore.setAddressInfo(uni.getStorageSync('location_address'))
            } else {
                uni.removeStorageSync('location_address');
            }

        }

        // 定位信息过期后，重新获取定位
        if (!uni.getStorageSync('manually_select_location_from_map') && locationStorage() && locationStorage().is_expired) {
            init();
        }
    }

    // 通过经纬度获取地址信息
    const getAddressByLatlngFn = (latlng: any = '') => {
        if (!isOpen) return false;
        let data = { latlng: '' };
        if (latlng) {
            data.latlng = latlng;
        } else {
            data.latlng = systemStore.diyAddressInfo.latitude + ',' + systemStore.diyAddressInfo.longitude;
        }
        getAddressByLatlng(data).then((res: any) => {
            if (res.data && Object.keys(res.data).length) {
                let obj: any = {};

                let latlngArr = data.latlng.split(',');

                obj.latitude = latlngArr[0];
                obj.longitude = latlngArr[1];
                obj.full_address = res.data.province != undefined ? res.data.province : '';
                obj.full_address += res.data.city != undefined ? res.data.city : '';
                obj.full_address += res.data.district != undefined ? res.data.district : '';
                obj.full_address += res.data.community != undefined ? res.data.community : '';

                obj.province_id = res.data.province_id;
                obj.province = res.data.province;
                obj.city_id = res.data.city_id;
                obj.city = res.data.city;
                obj.district_id = res.data.district_id;
                obj.district = res.data.district;
                obj.community = res.data.community;
                obj.formatted_addresses = res.data.formatted_addresses;
                systemStore.setAddressInfo(obj)
            } else {
                systemStore.setAddressInfo()
            }

            // 手动选择地图位置后，清除标识
            setTimeout(() => {
                // 由于异步请求速度影响，需要延迟删除
                uni.removeStorageSync('manually_select_location_from_map');
            }, 500);
        })
    }


    // 打开地图重新选择位置
    const reposition = () => {
        if (!isOpen) return false;

        let latitude = systemStore.diyAddressInfo ? systemStore.diyAddressInfo.latitude : '';
        let longitude = systemStore.diyAddressInfo ? systemStore.diyAddressInfo.longitude : '';

        // #ifndef H5
        uni.chooseLocation({
            latitude,
            longitude,
            success: (res) => {
                uni.setStorageSync('manually_select_location_from_map', true)
                let latng = res.latitude + ',' + res.longitude;
                getAddressByLatlngFn(latng)
            },
            fail: (res) => {
                // 在隐私协议中没有声明chooseLocation:fail api作用域
                if (res.errMsg && res.errno) {
                    if (res.errno == 104) {
                        let msg = '用户未授权隐私权限，选择位置失败';
                        uni.showToast({ title: msg, icon: 'none' })
                    } else if (res.errno == 112) {
                        let msg = '隐私协议中未声明，打开地图选择位置失败';
                        uni.showToast({ title: msg, icon: 'none' })
                    } else {
                        uni.showToast({ title: res.errMsg, icon: 'none' })
                    }
                }
            }
        });
        // #endif

        // #ifdef H5
        uni.setStorageSync('manually_select_location_from_map', true)
        let backurl = location.origin + location.pathname;
        window.location.href = 'https://apis.map.qq.com/tools/locpicker?search=1&type=0&backurl=' + encodeURIComponent(backurl) + '&key=' + manifestJson.h5.sdkConfigs.maps.qqmap.key + '&referer=myapp';
        // #endif
    }

    /**
     * 定位信息（缓存）
     */
    const locationStorage = () => {
        let data = uni.getStorageSync('location_address');
        if (data) {
            const date = new Date();
            if (systemStore.mapConfig.valid_time > 0) {
                data.is_expired = (date.getTime() / 1000) > data.valid_time; // 是否过期
            } else {
                data.is_expired = false;
            }
        } else {
            data = {
                is_expired: false
            };
        }
        return data;
    }

    return {
        init: init,
        onLoad: onLoadLifeCycle,
        refresh: refreshLocation,
        reposition: reposition
    }
}

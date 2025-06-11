//字符串拼接
function strFormat(str) {
    return str < 10 ? `0${ str }` : str
}

// 获取当前时间
export function currentTime() {
    const myDate = new Date();
    const y = myDate.getFullYear()
    const m = myDate.getMonth() + 1;
    const d = myDate.getDate();
    const date = y + '-' + strFormat(m) + '-' + strFormat(d)//年月日
    const mdTime = strFormat(m) + '-' + strFormat(d)
    const md = strFormat(m) + '月' + strFormat(d) + '日'
    const hour = myDate.getHours()
    const min = myDate.getMinutes()
    const secon = myDate.getSeconds()
    const seconTime = Math.floor(Date.now() / 1000)
    const time = strFormat(hour) + ':' + strFormat(min) + ':' + strFormat(secon);
    return {
        y,
        md,
        mdTime,
        date,
        time,
        seconTime
    }
}

//时间戳转日期
export function timeStamp(time, isQuantum) {
    const dates = new Date(time)
    const year = dates.getFullYear()
    const month = dates.getMonth() + 1
    const date = dates.getDate()
    const day = dates.getDay()
    const hour = dates.getHours()
    const min = dates.getMinutes()
    const days = ['日', '一', '二', '三', '四', '五', '六']
    const xinqi = [0, 1, 2, 3, 4, 5, 6]
    return {
        allDate: `${ year }/${ strFormat(month) }/${ strFormat(date) }`,
        date: `${ strFormat(year) }-${ strFormat(month) }-${ strFormat(date) }`, //返回的日期 2000-07-01
        md: `${ strFormat(month) }月${ strFormat(date) }日`,//返回的日期 07月01日
        mdTime: `${ strFormat(month) }-${ strFormat(date) }`, //返回的日期 07-01
        day: `周${ days[day] }`, //返回的礼拜天数  星期一
        dayNum: `${ xinqi[day] }`, //返回的礼拜天数 用于判断左侧设置
        hour: strFormat(hour) + ':' + strFormat(min) + (isQuantum ? "" : ':00') //返回的时钟 08:00
    }
}

//获取最近7天的日期和礼拜天数
export function initData() {
    const time = []
    const date = new Date()

    const now = date.getTime() //获取当前日期的时间戳
    let timeStr = 3600 * 24 * 1000 //一天的时间戳
    let obj = {
        0: "今天",
        1: "明天",
        2: "后天"
    }
    for (let i = 0; i < 7; i++) {
        time.push({
            date: timeStamp(now + timeStr * i).date, //保存日期(年月日)
            mdTime: timeStamp(now + timeStr * i).md,
            md: obj[i] ? obj[i] : timeStamp(now + timeStr * i).md,
            timeStamp: now + timeStr * i, //保存时间戳
            week: timeStamp(now + timeStr * i).day,
            dayNum: timeStamp(now + timeStr * i).dayNum,
            disable: false
        })
    }
    return time
}

//时间数组
export function initTime(trade_time_json, timeInterval = 0.5, isQuantum = true) {
    const time = [];
    const timeStr = 3600 * timeInterval; // 间隔时间转换成秒

    trade_time_json.forEach(slot => {
        let start = slot.start_time;
        let end = slot.end_time;

        for (let i = start; i < end; i += timeStr) {
            let nextTime = i + timeStr > end ? end : i + timeStr;

            if (isQuantum) {
                time.push({
                    begin: timestampTransition(i),
                    end: timestampTransition(nextTime),
                    disable: false
                });
            } else {
                time.push({
                    time: timestampTransition(i),
                    disable: false
                });
            }
        }
    });

    return time;
}

// 时间戳转时间
export function timestampTransition(timeStamp) {
    let hour = Math.floor(timeStamp / (60 * 60))
    let minute = Math.floor(timeStamp / 60) - (hour * 60)
    let second = timeStamp % 60
    hour = hour < 10 ? ('0' + hour) : hour
    minute = minute < 10 ? ('0' + minute) : minute
    // second = second < 10 ? ( '0' + second ) : second
    return hour + ':' + minute
}

// 时间转时间戳
export function timeTransition(time) {
    const arr = time.split(':');
    const hours = Number(arr[0]) * 60 * 60;
    const minutes = Number(arr[1]) * 60;
    return hours + minutes;
}

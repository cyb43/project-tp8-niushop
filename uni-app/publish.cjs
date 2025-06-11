const fs = require('fs')
const { spawn } = require('child_process');
const path = require('path');

const main = () => {
    const params = process.argv.slice(2) || []
    const port = params[0] || ''
    const mode = params[1] || ''

    switch (port) {
        case 'h5':
            publish()
            break;
        case 'mp-weixin':
            if (mode == 'build') {
                handleWeappAddonComponents(mode)
                handleWeappLanguage(mode)
            } else if (mode == 'dev') {
                listenWeappRunDev()
            }
            break;
    }
}

const publish = () => {
    const src = './dist/build/h5'
    const dest = '../niucloud/public/wap'

    solve()

    // 目标目录不存在停止复制
    try {
        const dir = fs.readdirSync(dest)
    } catch (e) {
        return
    }

    // 删除目标目录下文件
    fs.rm(dest, { recursive: true }, err => {
        if(err) {
            console.log(err)
            return
        }

        fs.cp(src, dest, { recursive: true }, (err) => {
            if (err) {
                console.error(err)
            }
        })
    })
}

const solve = () => {
    const src = './dist/build/h5/assets'
    const filemaps = fs.readdirSync(src)

    filemaps.forEach(file => {
        if (/^(index-)(\w{8})(.js)$/.test(file)) {
            const path = `${src}/${file}`
            let content = fs.readFileSync(path, 'utf-8')
            const first = 'const match = location.href.match(/\\/wap\\/(\\d*)\\//);'

            if (content.indexOf(first) == -1) {
                content = first + content
                const replace = 'router:{mode:"history",base: match ? `/wap/${match[1]}/` : "/wap/",assets:"assets",routerBase: match ? `/wap/${match[1]}/` : "/wap/"},darkmode'
                content = content.replace(/router:{(.*?)},darkmode/s, replace)
                fs.writeFileSync(path, content, 'utf8')
            }
        }
    })
}

const handleWeappAddonComponents = (mode) => {
    const files = [
        `./dist/${mode}/mp-weixin/addon/components/diy/group/index.json`,
        `./dist/${mode}/mp-weixin/app/pages/index/tabbar.json`
    ]

    files.forEach(src => {
        try {
            const data = JSON.parse(fs.readFileSync(src, 'utf8'));
            data.componentPlaceholder = {};

            Object.keys(data.usingComponents).map(key => {
                data.componentPlaceholder[key] = "view";
            })
            fs.writeFileSync(src, JSON.stringify(data))
        } catch (err) {
        }
    })
}

const handleWeappLanguage = (mode) => {
    const src = `./dist/${mode}/mp-weixin/locale/language.js`

    try {
        let content = fs.readFileSync(src, 'utf8');
        content = content.replace(/Promise\.resolve\(require\(("[^"]+")\)\)/g, 'require.async($1)')
        fs.writeFileSync(src, content)
    } catch (err) {
        console.log(err)
    }
}

const listenWeappRunDev = () => {
    const devProcess = spawn('npm', ['run', 'dev:niu-mp-weixin'], {
        stdio: ['pipe', 'pipe', 'pipe'],
        shell: true
    });

    let serverReady = false;

    // 监听 stdout 输出
    devProcess.stdout.on('data', (data) => {
        const message = data.toString();
        console.log(message)
        if (!serverReady && message.includes('DONE  Build complete')) {
            serverReady = true;
            handleWeappAddonComponents('dev')
            handleWeappLanguage('dev')
        }
    });

    // 监听 stderr 输出，用于捕获错误信息
    devProcess.stderr.on('data', (data) => {
        console.error(data.toString());
    });

    // 监听子进程退出事件
    devProcess.on('close', (code) => {
        if (code !== 0) { // 如果退出码不是0，则认为发生了错误
            console.error(`Child process exited with code ${code}`);
        } else {
            console.log('Child process exited successfully.');
        }
    });
}

main()

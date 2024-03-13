import webfontsGenerator from '@furkot/webfonts-generator';
import {globby} from 'globby';

(async () => {
    const paths = await globby('/home/bun/app/webfont/src/*.svg')

    console.log(paths.length + ' icons found')

    await webfontsGenerator({
        files: paths,
        dest: 'public/webfont/',
        html: true,
        htmlPath: 'public/webfont/',
        codepoints: {
            hurricane: 0xF222
        }
    })

    console.log('CSS generated')
})()

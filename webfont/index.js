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
        cssTemplate: 'webfont/template/css.hbs',
        codepoints: {
            'user-circle': 0xF200,
            'place': 0xF201,
            'video': 0xF202,
            'handout': 0xF203,
            'export': 0xF300,
            'check': 0xF301
        }
    })

    console.log('CSS generated')
})()

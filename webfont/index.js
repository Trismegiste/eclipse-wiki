const webfontsGenerator = require('@furkot/webfonts-generator');

(async () => {
    await webfontsGenerator({
        files: [
            'webfont/src/hurricane.svg',
            'webfont/src/cloud-rain.svg',
        ],
        dest: 'public/webfont/',
        html: true,
        htmlPath: 'public/webfont/',
        codepoints: {
            hurricane: 0xF222
        }
    })
})()

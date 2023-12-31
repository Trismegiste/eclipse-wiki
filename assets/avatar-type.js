/*
 * For CropperType form type
 */
import Croppie from 'croppie';

export default (avatarSize) => ({
        size: avatarSize,
        uploadCrop: null,

        init() {
            this.uploadCrop = new Croppie(this.$refs.cropperWidget, {
                viewport: {
                    width: this.size,
                    height: this.size,
                    type: 'circle'
                },
                boundary: {width: this.size, height: this.size},
                enableExif: true
            })

            // Copy-Paste subscribing
            window.addEventListener('paste', e => {
                if (e.clipboardData.files.length > 0) {
                    this.uploadCrop.bind(URL.createObjectURL(e.clipboardData.files[0]))
                }
            })

            // default picture
            if (this.$refs.cropperWidget.dataset.defaultPicture.length) {
                this.uploadCrop.bind({url: this.$refs.cropperWidget.dataset.defaultPicture})
            }
        },

        readFile(input) {
            const reader = new FileReader();
            reader.onload = event => {
                this.uploadCrop.bind({url: event.target.result})
            }

            reader.readAsDataURL(input.files[0]);
        },

        update(event) {
            this.uploadCrop.result('blob').then(blob => {
                let file = new File([blob], 'avatar.png', {type: "image/png", lastModified: new Date().getTime()}, 'utf-8')
                let container = new DataTransfer()
                container.items.add(file)
                this.$refs.fileWidget.files = container.files
            })
        },

        bindPicture(detail) {
            this.uploadCrop.bind({url: detail.dataUrl})
        }

    })
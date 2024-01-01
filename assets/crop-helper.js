/*
 * eclipse-wiki
 */

// convert a DataURL to File for sending to symfony Form
export function dataURLtoFile(dataurl, filename) {
    var arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]),
            n = bstr.length,
            u8arr = new Uint8Array(n);

    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }

    return new File([u8arr], filename, {type: mime});
}

// adding height and width attributes of SVG root element to fix a bug in Firefox
export function fixSvgDimension(svgCode, side) {
    let parser = new DOMParser()
    let doc = parser.parseFromString(svgCode, "image/svg+xml")
    doc.rootElement.setAttribute('width', side)
    doc.rootElement.setAttribute('height', side)

    return doc
}

// convert a SVG DOMDocument to data url
export function svgContentToDataUrl(doc) {
    let exporter = new XMLSerializer()

    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(exporter.serializeToString(doc))
}
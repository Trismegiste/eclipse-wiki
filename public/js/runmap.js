/*
 * Eclipse Wiki
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('spa', () => ({
            battlemap: null,
            fogEnabled: true,
            legendFilter: null,
            newCharacter: null,
            newPictogram: null,

            init() {
                let svgElem = document.querySelector('#map svg')
                this.battlemap = SVG(svgElem)
                this.battlemap.panZoom({zoomMin: 5, zoomMax: 100, zoomFactor: 0.2})

                // remove fog of war for one room on a ctrl+click event
                document.querySelectorAll('#map g.fog-of-war').forEach(function (item) {
                    item.addEventListener('click', function (e) {
                        if (e.ctrlKey) {
                            item.remove()
                        }
                    })
                })

                // make all NPC draggable and removable on ctrl+click
                document.querySelectorAll('svg #layer-npc use').forEach(item => {
                    SVG(item)
                            .draggable()
                            .click(e => {
                                if (e.ctrlKey) {
                                    item.remove()
                                }
                            })
                            .click(e => {
                                if (e.shiftKey) {
                                    this.updateCharacterInfo(item.dataset.npcTitle)
                                }
                            })
                })

                // Broadcast current view with a key
                Mousetrap.bind('p', (e) => {
                    this.pushSvgToBroadcast(e)
                })
            },

            toggleFog() {
                let fogLayer = document.querySelector('#map #gm-fogofwar')
                fogLayer.setAttribute('class', this.fogEnabled ? '' : 'disabled-fog')
            },

            updateCharacterInfo(title) {
                fetch(Route.miniCard + title)
                        .then(response => {
                            return response.text()
                        })
                        .then(content => {
                            this.$refs.characterCard.innerHTML = content
                            Pushable_subscribe(document.querySelectorAll('.pushable a'))
                        })
            },

            searchLegend() {
                let xpath = '//svg:svg/svg:g[@id="legend"]/svg:text[normalize-space()="' + this.legendFilter.trim().toUpperCase() + '"]'
                let legend = document.evaluate(xpath, document, function (prefix) {
                    const ns = {
                        'xhtml': 'http://www.w3.org/1999/xhtml',
                        'svg': 'http://www.w3.org/2000/svg'
                    }
                    return ns[prefix] || null
                }, XPathResult.ANY_UNORDERED_NODE_TYPE).singleNodeValue

                if (legend) {
                    let cx = legend.getAttribute('x')
                    let cy = legend.getAttribute('y')
                    this.battlemap
                            .zoom(1) // uses center of viewport by default
                            .animate()
                            .zoom(75, {x: cx, y: cy})
                }
            },

            characterAdd(npcTitle) {
                fetch(Route.getToken + npcTitle).then(response => {
                    return response.text()
                }).then((content) => {
                    let bbox = this.battlemap.viewbox()
                    let token = this.battlemap.group()
                    token.svg(content)
                    token.data('npc-title', npcTitle, true)
                            .move(bbox.x + bbox.width / 2, bbox.y + bbox.height / 2)
                            .draggable()
                    // remove on ctrl+click & info on shift+click
                    token
                            .click(e => {
                                if (e.ctrlKey) {
                                    token.remove()
                                }
                            })
                            .click(e => {
                                if (e.shiftKey) {
                                    this.updateCharacterInfo(npcTitle)
                                }
                            })
                })

                this.newCharacter = null
            },

            pictogramAdd() {
                fetch(Route.getPicto + this.newPictogram).then(response => {
                    return response.text()
                }).then(content => {
                    let bbox = this.battlemap.viewbox()
                    let token = this.battlemap.group()
                    token.svg(content)

                    if (token.bbox().width > token.bbox().height) {
                        token.size(0.8)
                    } else {
                        token.size(null, 0.8)
                    }

                    token.move(bbox.x + bbox.width / 2, bbox.y + bbox.height / 2)
                            .draggable()
                    // remove on ctrl+click
                    token.click(e => {
                        if (e.ctrlKey) {
                            token.remove()
                        }
                    })
                })

                this.newPictogram = null
            },

            playerBroadcast(e) {
                e.preventDefault()
                this.pushSvgToBroadcast(e)
            },

            pushSvgToBroadcast(e) {
                const formData = new FormData()
                formData.append('svg', new Blob([this.battlemap.svg()], {type: 'image/svg+xml'}))

                fetch(Route.pushPlayer, {
                    method: 'post',
                    body: formData,
                    redirect: 'manual'
                }).then(function (response) {
                    return response.json()
                }).then(function (json) {
                    pushFlash(e.target, 'success', json.message)
                })
            }
        }))
})
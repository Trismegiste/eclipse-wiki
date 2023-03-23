/*
 * eclipse-wiki
 */

class BattlemapBuilder
{
    spriteManager = {}
    wheelSpeed = 1.4
    scene = null
    cameraMinZ = 0.35

    // hexagonal torus selector when clicking on tile :
    tileSelector
    // hexagonal cursor when hovering on tiles :
    tileCursor

    constructor(scene) {
        this.scene = scene
        scene.collisionsEnabled = true;
        scene.fogColor = scene.clearColor = BABYLON.Color3.Black()
        scene.fogDensity = 0.07
    }

    getDoc() {
        return this.scene.metadata
    }

    setCamera() {
        const camera = this.scene.getCameraByName('gm-camera')
        camera.position = new BABYLON.Vector3(this.getDoc().side / 2, this.getDoc().side, -this.getDoc().side / 2 - 1)
        camera.setTarget(new BABYLON.Vector3(this.getDoc().side / 2, 0, -this.getDoc().side / 2))
        camera.minZ = this.cameraMinZ
        camera.maxZ = this.getDoc().side * 2
        camera.fov = 60 / 180 * Math.PI
        // Then apply collisions to the active camera
        camera.checkCollisions = true;
        //Set the ellipsoid around the camera (e.g. your player's size)
        camera.ellipsoid = new BABYLON.Vector3(0.1, this.getDoc().wallHeight / 3, 0.1)
        camera.inputs.removeByType("FreeCameraKeyboardMoveInput")

        this.scene.onPointerObservable.add((pointerInfo) => {
            switch (pointerInfo.type) {
                case BABYLON.PointerEventTypes.POINTERWHEEL:
                    const event = pointerInfo.event
                    event.preventDefault()
                    if (event.wheelDelta > 0) {
                        camera.position.y /= this.wheelSpeed
                    } else {
                        camera.position.y *= this.wheelSpeed
                    }
                    const minHeight = 2 * this.getDoc().wallHeight / 3
                    if (camera.position.y < minHeight) {
                        camera.position.y = minHeight
                        this.scene.fogMode = BABYLON.Scene.FOGMODE_EXP
                    } else {
                        this.scene.fogMode = BABYLON.Scene.FOGMODE_NONE
                    }

                    break;
            }
        })

        this.scene.onKeyboardObservable.add(kbInfo => {
            switch (kbInfo.type) {
                case BABYLON.KeyboardEventTypes.KEYUP:
                    switch (kbInfo.event.keyCode) {
                        case 32:
                            if (this.scene.metadata.selectedOnTileIndex !== null) {
                                this.postScreenshotFrom(this.scene.metadata.selectedOnTileIndex)
                            }
                            break
                    }
                    break
            }
        })
    }

    async postScreenshotFrom(idx) {
        this.getDoc().playerViewOnTileIndex = idx
        const tileWithSelect = this.getGroundTileByIndex(idx)
        const center = tileWithSelect.position.clone()
        center.y = 2 * this.getDoc().wallHeight / 3
        let pov = new BABYLON.UniversalCamera("npc-camera", center, this.scene)
        pov.minZ = this.cameraMinZ
        pov.maxZ = this.getDoc().side * 2
        pov.fov = 90 / 180 * Math.PI

        let target = []
        for (let k = 0; k < 6; k++) {
            target[k] = center.clone()
        }
        target[0].z++
        target[1].z--
        target[2].x++
        target[3].x--
        target[4].y++
        target[5].y--
        this.scene.activeCamera = pov
        // save temporary state
        const groundSelector = this.tileCursor
        const itemSelector = this.tileSelector
        groundSelector.isVisible = false
        itemSelector.isVisible = false
        const currentFog = this.scene.fogMode
        this.scene.fogMode = BABYLON.Scene.FOGMODE_EXP

        const formElem = document.querySelector('form[name=cubemap_broadcast]')
        const formData = new FormData(formElem)
        for (let k = 0; k < 6; k++) {
            pov.setTarget(target[k])
            this.scene.render()
            let data = await BABYLON.ScreenshotTools.CreateScreenshotUsingRenderTargetAsync(this.scene.getEngine(), pov, 1280, "image/png", 1, true, null, true)
            formData.append(`cubemap_broadcast[picture][${k}]`, new Blob([BABYLON.DecodeBase64UrlToBinary(data)], {type: 'image/png'}))
        }

        // post the form
        fetch(formElem.action, {
            method: 'post',
            body: formData,
            redirect: 'manual'
        }).finally(res => {
            // restore camera
            this.scene.activeCamera = this.scene.getCameraByName('gm-camera')
            pov.dispose()
            // restore original state
            groundSelector.isVisible = true
            itemSelector.isVisible = true
            this.scene.fogMode = currentFog
        })
    }

    setLight() {
        // Creates a light, aiming 0,1,0 - to the sky
        const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), this.scene)
        light.intensity = 1.5

        const headlight = new BABYLON.PointLight('headlight', new BABYLON.Vector3(0, 2 * this.getDoc().wallHeight / 3, 0), this.scene)
        headlight.diffuse = BABYLON.Color3.White()
        headlight.range = 8

        this.scene.registerBeforeRender(() => {
            if (this.scene.metadata.selectedOnTileIndex !== null) {
                const selector = this.tileSelector
                headlight.position.x = selector.position.x
                headlight.position.z = selector.position.z
                headlight.setEnabled(true)
            } else {
                headlight.setEnabled(false)
            }
        })
    }

    declareGround() {
        // Ground templates
        this.getDoc().texture.forEach((key) => {
            const tile = BABYLON.MeshBuilder.CreateDisc("hexagon-" + key, {tessellation: 6, radius: 2 / 3 - 0.01}, this.scene)
            tile.rotation.z = Math.PI / 6
            tile.rotation.x = Math.PI / 2
            tile.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-ground-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/ground/" + key + ".webp", this.scene)
            myMaterial.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/ground/" + key + "-bump.webp", this.scene)
            tile.material = myMaterial
        })
    }

    declareWall() {
        // Wall templates
        this.getDoc().texture.forEach((key) => {
            const wall = BABYLON.MeshBuilder.CreatePlane("wall-" + key, {width: 2 / 3, height: this.getDoc().wallHeight})
            wall.position.y = this.getDoc().wallHeight / 2
            wall.position.x = 2 / 3 * Math.cos(Math.PI / 6)
            wall.rotation.y = Math.PI / 2
            wall.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-wall-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/wall/" + key + ".webp", this.scene)
            myMaterial.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/wall/" + key + "-bump.webp", this.scene)
            wall.material = myMaterial
        })
    }

    declareGroundCursor() {
        // selector
        const groundSelector = BABYLON.MeshBuilder.CreateDisc("selector-ground", {tessellation: 6, radius: 2 / 3}, this.scene)
        groundSelector.rotation.z = Math.PI / 6
        groundSelector.rotation.x = Math.PI / 2
        groundSelector.position.y = 0.01
        groundSelector.isVisible = false
        const selectorMat = new BABYLON.StandardMaterial('mat-selector')
        selectorMat.diffuseColor = new BABYLON.Color3(1, 0, 0)
        selectorMat.alpha = 0.3
        groundSelector.material = selectorMat
        this.tileCursor = groundSelector

        groundSelector.actionManager = new BABYLON.ActionManager(this.scene);

        // right click behavior
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnRightPickTrigger, e => {
                    let objToAnimate;
                    switch (this.scene.metadata.viewMode) {
                        // move camera
                        case 'fps':
                            objToAnimate = this.scene.getCameraByName('gm-camera')
                            break
                        case 'rts':
                            if (this.scene.metadata.selectedOnTileIndex === null) {
                                return;
                            }

                            // a NPC must be present at the selected tile :
                            const sourceTileInfo = this.getTileInfo(this.scene.metadata.selectedOnTileIndex)
                            if (sourceTileInfo.npc === null) {
                                return;
                            }

                            // no NPC must be present at the selected tile :
                            const targetTileInfo = this.getTileInfo(groundSelector.metadata)
                            if (targetTileInfo.npc !== null) {
                                return;
                            }

                            objToAnimate = sourceTileInfo.npc.npcSpritePtr
                            // move NPC in model :
                            targetTileInfo.npc = sourceTileInfo.npc
                            sourceTileInfo.npc = null
                            // move item selector :
                            this.scene.metadata.selectedOnTileIndex = groundSelector.metadata
                            const itemSelector = this.tileSelector
                            itemSelector.position.x = groundSelector.position.x
                            itemSelector.position.z = groundSelector.position.z
                            break
                        case 'delete':
                        case 'populate':
                            return
                    }

                    const target = e.meshUnderPointer.position.clone()
                    target.y = objToAnimate.position.y

                    const frameRate = 10
                    const moving = new BABYLON.Animation("moving", "position", frameRate, BABYLON.Animation.ANIMATIONTYPE_VECTOR3)
                    moving.setKeys([
                        {frame: 0, value: objToAnimate.position},
                        {frame: frameRate, value: target}
                    ])
                    objToAnimate.animations.push(moving)
                    this.scene.beginAnimation(objToAnimate, 0, frameRate)
                })
                )

        // left click behavior
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnLeftPickTrigger, e => {
                    const selector = this.tileSelector
                    let metadata = this.getTileInfo(groundSelector.metadata)

                    switch (this.scene.metadata.viewMode) {
                        case 'fps':
                        case 'rts':
                            selector.isVisible = true
                            selector.position.x = groundSelector.position.x
                            selector.position.z = groundSelector.position.z
                            this.scene.metadata.selectedOnTileIndex = groundSelector.metadata

                            // fire an event for alpinejs :
                            const detail = {...metadata}
                            detail.x = e.meshUnderPointer.position.x
                            detail.y = e.meshUnderPointer.position.z
                            detail.cellIndex = groundSelector.metadata
                            document.querySelector('canvas').dispatchEvent(new CustomEvent('selectcell', {"bubbles": true, detail}))
                            break;
                        case 'populate':
                            if ((metadata.npc === null) && (this.scene.metadata.populateWithNpc !== null)) {
                                const npcTitle = this.scene.metadata.populateWithNpc
                                metadata.npc = {label: npcTitle}  // immediately update model to prevent double insertion
                                // does spritemanager exist ?
                                if (this.spriteManager[npcTitle] === undefined) {
                                    // append missing sprite manager
                                    fetch('/npc/show.json?title=' + npcTitle).then(resp => {
                                        return resp.json()
                                    }).then(npcInfo => {
                                        const sp = new BABYLON.SpriteManager('token-' + npcTitle, '/picture/get/' + npcInfo.tokenPic, 2000, 504)
                                        this.spriteManager[npcTitle] = sp
                                        this.getDoc().npcToken.push({label: npcTitle, picture: npcInfo.tokenPic})
                                        // append sprite
                                        this.appendNpcAt(metadata.npc, groundSelector.position.x, groundSelector.position.z)
                                    })
                                } else {
                                    // append sprite
                                    this.appendNpcAt(metadata.npc, groundSelector.position.x, groundSelector.position.z)
                                }
                            }
                            break
                        case 'delete':
                            if (metadata.npc === null) {
                                return;
                            }
                            const sp = metadata.npc.npcSpritePtr
                            sp.dispose()
                            metadata.npc = null
                    }
                })
                )
    }

    declareSelector() {
        const itemSelector = BABYLON.MeshBuilder.CreateTorus("selector-item", {
            tessellation: 6,
            diameter: 1.2,
            thickness: 0.1
        }, this.scene)

        const selectorMat = new BABYLON.StandardMaterial('mat-selector')
        selectorMat.emissiveColor = new BABYLON.Color3(0, 1, 0)
        selectorMat.alpha = 0.8
        selectorMat.disableLighting = true
        itemSelector.material = selectorMat
        itemSelector.isPickable = false
        this.tileSelector = itemSelector
    }

    declarePlayerCursor() {
        let sphere = BABYLON.MeshBuilder.CreateSphere('player-cursor')
        sphere.position.y = 0.5
        this.playerCursor = sphere
        const selectorMat = new BABYLON.StandardMaterial('mat-player-cursor')
        selectorMat.emissiveColor = new BABYLON.Color3(1, 1, 0)
        selectorMat.alpha = 0
        selectorMat.disableLighting = true
        sphere.material = selectorMat
        sphere.isPickable = false
        // inject new method for player cursor
        this.scene.movePlayerCursor = function (x, y) {
            sphere.position.x = x
            sphere.position.z = y
            sphere.material.alpha = 0

            const frameRate = 10
            const blinking = new BABYLON.Animation("blinking", "material.alpha", frameRate, BABYLON.Animation.ANIMATIONTYPE_FLOAT)
            blinking.setKeys([
                {frame: 0, value: 1},
                {frame: frameRate, value: 0}
            ])
            sphere.animations.push(blinking)
            this.beginAnimation(sphere, 0, frameRate)
        }
    }

    getGroundTileByIndex(idx) {
        return this.scene.getMeshByName('ground-' + idx)
    }

    getTileInfo(idx) {
        return this.scene.metadata.grid[idx].content
    }

    declareDoor() {
        // Generic door
        const door = BABYLON.MeshBuilder.CreatePlane('door', {width: 2 / 3, height: this.getDoc().wallHeight})
        door.position.y = this.getDoc().wallHeight / 2
        door.position.x = 2 / 3 * Math.cos(Math.PI / 6)
        door.rotation.y = Math.PI / 2
        door.isVisible = false

        const doorMat = new BABYLON.StandardMaterial('mat-door', this.scene)
        doorMat.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door.webp", this.scene)
        doorMat.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door-bump.webp", this.scene)
        door.material = doorMat
    }

    buildGrid() {
        // Grid of HexaCell
        this.getDoc().grid.forEach((cell, k) => {
            const ground = this.scene.getMeshByName('hexagon-' + cell.content.template).createInstance("ground-" + k)
            ground.position.x = cell.x
            ground.position.z = -cell.y
            ground.checkCollisions = true
            // keep track of index
            ground.metadata = k

            ground.actionManager = new BABYLON.ActionManager(this.scene);
            ground.actionManager.registerAction(
                    new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPointerOverTrigger, e => {
                        const selector = this.tileCursor
                        const current = e.meshUnderPointer
                        selector.isVisible = true
                        selector.position.x = current.position.x
                        selector.position.z = current.position.z
                        // we store the cell index into the metadata of the selector
                        selector.metadata = current.metadata
                    })
                    )

            for (let dir = 0; dir < 6; dir++) {
                if (cell.content.wall[dir]) {
                    const handle = new BABYLON.TransformNode("handle" + k + '-' + dir)
                    const meshName = cell.content.door[dir] ? 'door' : 'wall-' + cell.content.template
                    const tmpWall = this.scene.getMeshByName(meshName).createInstance("wall-" + k + '-' + dir)
                    tmpWall.parent = handle
                    tmpWall.checkCollisions = !cell.content.door[dir]
                    handle.rotation.y = -dir * Math.PI / 3
                    handle.position.x = cell.x
                    handle.position.z = -cell.y

                    // clickable door
                    if (cell.content.door[dir]) {
                        tmpWall.actionManager = new BABYLON.ActionManager(this.scene);
                        tmpWall.actionManager.registerAction(new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnLeftPickTrigger, e => {
                            const current = e.meshUnderPointer
                            current.isVisible = false
                        }))
                    }
                }
            }

            // token if any
            if (cell.content.npc) {
                this.appendNpcAt(cell.content.npc, cell.x, -cell.y)
            }

            // legend if any
            if (cell.content.legend) {
                this.scene.setLegendAtCell(k, cell.content.legend)
            }
        })
    }

    declareNpcToken() {
        // map token
        this.getDoc().npcToken.forEach(npc => {
            const sp = new BABYLON.SpriteManager('token-' + npc.label, '/picture/get/' + npc.picture, 2000, 504)
            this.spriteManager[npc.label] = sp
        })
    }

    appendNpcAt(npcContent, x, y) {
        const manager = this.spriteManager[npcContent.label]
        const name = npcContent.label + Math.random()
        const sprite = new BABYLON.Sprite(name, manager)
        sprite.width = 0.6
        sprite.height = 0.6
        sprite.position = new BABYLON.Vector3(x, 0.7, y)
        npcContent.npcSpritePtr = sprite
    }

    drawCeiling() {
        const width = (this.getDoc().side + 1) * (2 * Math.sqrt(3) / 3)
        const height = (this.getDoc().side + 1)
        // ceiling
        const ceiling = BABYLON.MeshBuilder.CreateTiledPlane("ceiling", {
            sideOrientation: BABYLON.Mesh.BACKSIDE,
            width,
            height,
            tileSize: 1,
            tileWidth: 1
        })
        ceiling.isPickable = false
        ceiling.translate(new BABYLON.Vector3(width / 2 - 1, this.getDoc().wallHeight, 1 - height / 2), 1, BABYLON.Space.WORLD)
        ceiling.rotation.x = Math.PI / 2
        const ceilingMat = new BABYLON.StandardMaterial('mat-ceiling', this.scene)
        ceilingMat.emissiveColor = BABYLON.Color3.White()
        ceilingMat.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/ceiling.webp", this.scene)
        ceilingMat.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/ceiling-bump.webp", this.scene)
        ceiling.material = ceilingMat
    }

    declareWriter() {
        // Writer extension
        this.scene.TextWriter = BABYLON.MeshWriter(this.scene)
        this.scene.setLegendAtCell = function (cellIndex, message) {
            const cell = this.metadata.grid[cellIndex]

            if (cell.content.legendPtr) {
                cell.content.legendPtr.dispose()
            }

            if (message.length > 0) {
                const legendTxt = new this.TextWriter(message, {
                    anchor: "center",
                    "letter-height": 0.1,
                    "letter-thickness": 0.001,
                    "colors": {diffuse: "#00ff00"},
                    position: {
                        x: cell.x,
                        y: 0.02,
                        z: -cell.y
                    }
                })
                cell.content.legend = message
                cell.content.legendPtr = legendTxt
            } else {
                cell.content.legend = null
                cell.content.legendPtr = null
            }
        }
    }

    create() {
        // build the scene from the model
        this.declareWriter()
        this.setCamera()
        this.setLight()
        this.declareGround()
        this.declareWall()
        this.declareGroundCursor()
        this.declareSelector()
        this.declarePlayerCursor()
        this.declareDoor()
        this.declareNpcToken()
        this.buildGrid()
        this.drawCeiling()
    }
}

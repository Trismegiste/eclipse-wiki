/*
 * eclipse-wiki
 */

class BattlemapBuilder
{
    side;
    wallHeight;
    texture;
    scene;
    npc = []
    spriteManager = {}
    spriteDictionary = {}
    wheelSpeed = 1.4

    constructor(scene) {
        this.scene = scene
        this.scene.metadata = new BattlemapPlayer()
    }

    setCamera() {
        const camera = this.scene.getCameraByName('gm-camera')
        camera.position = new BABYLON.Vector3(this.side / 2, this.side, -this.side / 2)
        camera.setTarget(new BABYLON.Vector3(this.side / 2, 0, -this.side / 2))
        camera.minZ = 0.3
        camera.maxZ = this.side * 2
        camera.fov = 60 / 180 * Math.PI
        // Then apply collisions and gravity to the active camera
        camera.checkCollisions = true;
        camera.applyGravity = true;
        //Set the ellipsoid around the camera (e.g. your player's size)
        camera.ellipsoid = new BABYLON.Vector3(0.1, this.wallHeight / 3, 0.1)
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
                    const minHeight = 2 * this.wallHeight / 3
                    if (camera.position.y < minHeight) {
                        camera.position.y = minHeight
                    }
                    break;
            }
        })
    }

    setLight() {
        // Creates a light, aiming 0,1,0 - to the sky
        const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), this.scene)
        light.intensity = 1.5
    }

    declareGround() {
        // Ground templates
        this.texture.forEach((key) => {
            const tile = BABYLON.MeshBuilder.CreateDisc("hexagon-" + key, {tessellation: 6, radius: 2 / 3 - 0.01}, this.scene)
            tile.rotation.z = Math.PI / 6
            tile.rotation.x = Math.PI / 2
            tile.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-ground-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/habitat/ground/" + key + ".webp", this.scene)
            tile.material = myMaterial
        })
    }

    declareWall() {
        // Wall templates
        this.texture.forEach((key) => {
            const wall = BABYLON.MeshBuilder.CreatePlane("wall-" + key, {width: 2 / 3, height: this.wallHeight})
            wall.position.y = this.wallHeight / 2
            wall.position.x = 2 / 3 * Math.cos(Math.PI / 6)
            wall.rotation.y = Math.PI / 2
            wall.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-wall-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/habitat/wall/" + key + ".webp", this.scene)
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
        selectorMat.alpha = 0.4
        groundSelector.material = selectorMat

        groundSelector.actionManager = new BABYLON.ActionManager(this.scene);
        // click to move
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnRightPickTrigger, e => {
                    let objToAnimate;
                    if (this.scene.metadata.viewMode === 'fps') {
                        // move camera
                        objToAnimate = this.scene.getCameraByName('gm-camera')
                    } else {
                        if (this.scene.metadata.selectedOnTileIndex === null) {
                            return;
                        }

                        const sourceTileInfo = this.getTileInfo(this.scene.metadata.selectedOnTileIndex)
                        const targetTileInfo = this.getTileInfo(groundSelector.metadata)
                        if (targetTileInfo.npc !== null) {
                            return;
                        }

                        objToAnimate = this.spriteDictionary[sourceTileInfo.npc.npcName]
                        // move NPC in model :
                        targetTileInfo.npc = sourceTileInfo.npc
                        sourceTileInfo.npc = null
                        // move item selector :
                        this.scene.metadata.selectedOnTileIndex = groundSelector.metadata
                        const itemSelector = this.scene.getMeshByName('selector-item')
                        itemSelector.position.x = groundSelector.position.x
                        itemSelector.position.z = groundSelector.position.z
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

        // click to info
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnLeftPickTrigger, e => {
                    const selector = this.scene.getMeshByName('selector-item')
                    let metadata = this.getTileInfo(groundSelector.metadata)

                    switch (this.scene.metadata.viewMode) {
                        case 'fps':
                        case 'rts':
                            if (metadata.npc !== null) {
                                selector.isVisible = true
                                selector.position.x = groundSelector.position.x
                                selector.position.z = groundSelector.position.z
                                this.scene.metadata.selectedOnTileIndex = groundSelector.metadata
                            } else {
                                selector.isVisible = false
                                this.scene.metadata.selectedOnTileIndex = null
                            }
                            // in any case, fire an event for alpinejs :
                            const detail = {...metadata}
                            detail.x = e.meshUnderPointer.position.x
                            detail.y = e.meshUnderPointer.position.z
                            document.querySelector('canvas').dispatchEvent(new CustomEvent('selectcell', {"bubbles": true, detail}))
                            break;
                        case 'populate':
                            if (this.scene.metadata.populateWithNpc !== null) {
                                const key = this.scene.metadata.populateWithNpc
                                console.log(key, this.spriteManager[key])
                                const uniqueName = this.appendNpcAt({label: key}, groundSelector.position.x, groundSelector.position.z)
                                metadata.npc = {label: key, npcName: uniqueName}
                            }
                            break;
                    }
                })
                )
    }

    declareSelector() {
        const itemSelector = BABYLON.MeshBuilder.CreateTorus("selector-item", {
            tessellation: 36,
            diameter: 0.8,
            thickness: 0.15
        }, this.scene)

        const selectorMat = new BABYLON.StandardMaterial('mat-selector')
        selectorMat.diffuseColor = new BABYLON.Color3(0, 1, 0)
        selectorMat.alpha = 0.8
        itemSelector.material = selectorMat
        itemSelector.isPickable = false
    }

    getGroundTileByIndex(idx) {
        return this.scene.getMeshByName('ground-' + idx)
    }

    getTileInfo(idx) {
        return this.getGroundTileByIndex(idx).metadata
    }

    declareDoor() {
        // Generic door
        const door = BABYLON.MeshBuilder.CreatePlane('door', {width: 2 / 3, height: this.wallHeight})
        door.position.y = this.wallHeight / 2
        door.position.x = 2 / 3 * Math.cos(Math.PI / 6)
        door.rotation.y = Math.PI / 2
        door.isVisible = false

        const doorMat = new BABYLON.StandardMaterial('mat-door', this.scene)
        doorMat.diffuseTexture = new BABYLON.Texture("/texture/habitat/door.webp", this.scene)
        door.material = doorMat
    }

    buildGrid() {
        // Grid of HexaCell
        this.grid.forEach((cell, k) => {
            const ground = this.scene.getMeshByName('hexagon-' + cell.content.template).createInstance("ground-" + k)
            ground.position.x = cell.x
            ground.position.z = -cell.y
            ground.checkCollisions = true
            // model injected into the tile
            ground.metadata = cell.content
            ground.metadata.cellIndex = k

            ground.actionManager = new BABYLON.ActionManager(this.scene);
            ground.actionManager.registerAction(
                    new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPointerOverTrigger, e => {
                        const selector = this.scene.getMeshByName('selector-ground')
                        const current = e.meshUnderPointer
                        selector.isVisible = true
                        selector.position.x = current.position.x
                        selector.position.z = current.position.z
                        // we store the cell index into the metadata of the selector
                        selector.metadata = current.metadata.cellIndex
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
                ground.metadata.npc.npcName = this.appendNpcAt(cell.content.npc, cell.x, -cell.y)
            }
        })
    }

    declareToken() {
        // map token
        this.npc.forEach((npc) => {
            const sp = new BABYLON.SpriteManager('token-' + npc.label, '/picture/get/' + npc.picture, 2000, 504)
            this.spriteManager[npc.label] = sp
        })
    }

    appendNpcAt(token, x, y) {
        const manager = this.spriteManager[token.label]
        const name = token.label + ' ' + manager.sprites.length
        const npc = new BABYLON.Sprite(name, manager)
        npc.width = 0.6
        npc.height = 0.6
        npc.position = new BABYLON.Vector3(x, 0.7, y)
        this.spriteDictionary[name] = npc

        return name
    }

    drawCeiling() {
        const width = (this.side + 1) * (2 * Math.sqrt(3) / 3)
        const height = (this.side + 1)
        // ceiling
        const ceiling = BABYLON.MeshBuilder.CreateTiledPlane("ceiling", {
            sideOrientation: BABYLON.Mesh.BACKSIDE,
            width,
            height,
            tileSize: 1,
            tileWidth: 1
        })
        ceiling.isPickable = false
        ceiling.translate(new BABYLON.Vector3(width / 2 - 1, this.wallHeight, 1 - height / 2), 1, BABYLON.Space.WORLD)
        ceiling.rotation.x = Math.PI / 2
        const ceilingMat = new BABYLON.StandardMaterial('mat-ceiling', this.scene)
        ceilingMat.emissiveTexture = new BABYLON.Texture("/texture/habitat/ceiling.webp", this.scene)
        ceiling.material = ceilingMat
    }

    drawUI() {
        const advancedTexture = BABYLON.GUI.AdvancedDynamicTexture.CreateFullscreenUI("UI")
        advancedTexture.idealWidth = 1000
        const panel = new BABYLON.GUI.StackPanel()
        advancedTexture.addControl(panel)
        panel.verticalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_TOP
        panel.left = '5px'
        panel.top = '5px'

        const fps = BABYLON.GUI.RadioButton.AddRadioButtonWithHeader('FPS', 'view', false, (widget, value) => {
            if (value) {
                this.scene.metadata.viewMode = 'fps'
            }
        })
        fps.height = '25px'
        fps.horizontalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT
        panel.addControl(fps)

        const rts = BABYLON.GUI.RadioButton.AddRadioButtonWithHeader('RTS', 'view', false, (widget, value) => {
            if (value) {
                this.scene.metadata.viewMode = 'rts'
            }
        })
        rts.height = '25px'
        rts.horizontalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT
        panel.addControl(rts)

        const npcSelector = document.querySelector('form select')
        npcSelector.addEventListener('change', e => {
            const npcTitle = e.target.value
            if (this.spriteManager[npcTitle] === undefined) {
                // append missing sprite manager
                fetch('/npc/show.json?title=' + npcTitle).then(resp => {
                    return resp.json()
                }).then(npc => {
                    const sp = new BABYLON.SpriteManager('token-' + npcTitle, '/picture/get/' + npc.tokenPic, 2000, 504)
                    this.spriteManager[npcTitle] = sp
                    this.scene.metadata.viewMode = 'populate'
                    this.scene.metadata.populateWithNpc = npcTitle
                })
            }
        })
    }

    create() {
        this.setCamera()
        this.setLight()
        this.declareGround()
        this.declareWall()
        this.declareGroundCursor()
        this.declareSelector()
        this.declareDoor()
        this.declareToken()
        this.buildGrid()
        this.drawCeiling()
        this.drawUI()
    }
}

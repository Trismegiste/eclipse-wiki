/*
 * eclipse-wiki
 */
"use strict";

import BABYLON from 'babylonjs';
import PluginMeshWriter from "meshwriter";
let {MeshWriter} = PluginMeshWriter;

export class BattlemapBuilder
{
    wheelSpeed = 1.4
    scene = null
    cameraMinZ = 0.35

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
                            if (kbInfo.event.shiftKey) {
                                this.postScreenshotFrom(this.getSelectedTileIndex())
                            } else {
                                this.postGmView()
                            }
                            break
                        case 73:
                            this.postDepthMap()
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
        const groundSelector = this.scene.tileCursor
        const itemSelector = this.scene.tileSelector
        groundSelector.isVisible = false
        itemSelector.isVisible = false
        const currentFog = this.scene.fogMode
        this.scene.fogMode = BABYLON.Scene.FOGMODE_EXP

        const formElem = document.querySelector('form[name=cubemap_broadcast]')
        const formData = new FormData(formElem)
        for (let k = 0; k < 6; k++) {
            pov.setTarget(target[k])
            this.scene.render()
            let data = await BABYLON.ScreenshotTools.CreateScreenshotUsingRenderTargetAsync(this.scene.getEngine(), pov, 800, "image/png", 1, true, null, true)
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

    async postGmView() {
        const camera = this.scene.getCameraByName('gm-camera')
        const formElem = document.querySelector('form[name=gm_view_broadcast]')
        const formData = new FormData(formElem)

        // save temporary state
        const groundSelector = this.scene.tileCursor
        const itemSelector = this.scene.tileSelector
        groundSelector.isVisible = false
        itemSelector.isVisible = false

        // screenshot
        let data = await BABYLON.ScreenshotTools.CreateScreenshotUsingRenderTargetAsync(this.scene.getEngine(), camera, {width: 1920, height: 1080}, "image/png", 1, true, null, true)
        formData.append(`gm_view_broadcast[picture]`, new Blob([BABYLON.DecodeBase64UrlToBinary(data)], {type: 'image/png'}))

        // post the form
        fetch(formElem.action, {
            method: 'post',
            body: formData,
            redirect: 'manual'
        }).finally(res => {
            // restore original state
            groundSelector.isVisible = true
            itemSelector.isVisible = true
        })
    }

    async postDepthMap() {
        const renderer = this.scene.enableDepthRenderer()
        const map = renderer.getDepthMap()
        const buffer = await map.readPixels()
        const picture = await BABYLON.DumpDataAsync(map.getRenderWidth(), map.getRenderHeight(), buffer, 'image/png', 'depth.png', true, true)
        this.scene.disableDepthRenderer()
    }

    setLight() {
        // Creates a light, aiming 0,1,0 - to the sky
        const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), this.scene)
        light.intensity = 1.5

        const headlight = new BABYLON.PointLight('headlight', new BABYLON.Vector3(0, 2 * this.getDoc().wallHeight / 3, 0), this.scene)
        headlight.diffuse = BABYLON.Color3.White()
        headlight.range = 8

        this.scene.registerBeforeRender(() => {
            const selector = this.scene.tileSelector
            headlight.position.x = selector.position.x
            headlight.position.z = selector.position.z
            headlight.setEnabled(true)
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
        this.scene.tileCursor = groundSelector

        groundSelector.actionManager = new BABYLON.ActionManager(this.scene);

        // right click behavior
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnRightPickTrigger, event => {
                    let objToAnimate

                    if (event.sourceEvent.ctrlKey) {
                        // move the camera
                        objToAnimate = this.scene.getCameraByName('gm-camera')
                    } else {
                        // a NPC must be present at the selected tile :
                        const sourceTileInfo = this.getTileContent(this.getSelectedTileIndex())
                        if (sourceTileInfo.npc === null) {
                            return;
                        }

                        // no NPC must be present at the right-clicked target tile :
                        const targetTileInfo = this.getTileContent(groundSelector.metadata)
                        if (targetTileInfo.npc !== null) {
                            return;
                        }

                        objToAnimate = sourceTileInfo.npc.npcSpritePtr
                        // move NPC in model :
                        targetTileInfo.npc = sourceTileInfo.npc
                        sourceTileInfo.npc = null
                        // move item selector : 
                        this.scene.moveSelectorToIndex(groundSelector.metadata)
                    }

                    const target = event.meshUnderPointer.position.clone()
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
                    this.scene.moveSelectorToIndex(groundSelector.metadata)
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
        itemSelector.metadata = 0 // default index
        this.scene.tileSelector = itemSelector
    }

    getSelectedTileIndex() {
        return this.scene.tileSelector.metadata
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
        this.scene.blinkingCursor = sphere
    }

    getGroundTileByIndex(idx) {
        return this.scene.getMeshByName('ground-' + idx)
    }

    getTileContent(idx) {
        return this.scene.metadata.grid[idx].content
    }

    declareDoor() {
        // Generic door
        const door = BABYLON.MeshBuilder.CreatePlane('door', {width: 2 / 3, height: this.getDoc().wallHeight, sideOrientation: BABYLON.Mesh.DOUBLESIDE})
        door.position.y = this.getDoc().wallHeight / 2
        door.position.x = 2 / 3 * Math.cos(Math.PI / 6)
        door.rotation.y = Math.PI / 2
        door.isVisible = false

        const doorMat = new BABYLON.StandardMaterial('mat-door', this.scene)
        doorMat.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door.webp", this.scene)
        doorMat.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door-bump.webp", this.scene)
        door.material = doorMat

        // generic open door
        const openDoor = door.clone('open-door')
        const openDoorMat = new BABYLON.StandardMaterial('mat-opendoor', this.scene)
        openDoorMat.diffuseTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door-open.webp", this.scene)
        openDoorMat.diffuseTexture.hasAlpha = true
        openDoorMat.bumpTexture = new BABYLON.Texture("/texture/" + this.getDoc().theme + "/door-bump.webp", this.scene)
        openDoor.material = openDoorMat
        openDoor.isVisible = false
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
                        const selector = this.scene.tileCursor
                        const current = e.meshUnderPointer
                        selector.isVisible = true
                        selector.position.x = current.position.x
                        selector.position.z = current.position.z
                        // we store the cell index into the metadata of the selector
                        selector.metadata = current.metadata
                        const detail = {cursor: current.metadata}
                        document.querySelector('canvas').dispatchEvent(new CustomEvent('cursormove', {"bubbles": true, detail}))
                    })
                    )

            for (let dir = 0; dir < 6; dir++) {
                if (cell.content.wall[dir]) {
                    const handle = new BABYLON.TransformNode("handle" + k + '-' + dir)
                    if (cell.content.door[dir] && (dir < 3)) {
                        continue
                    }
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
                            const openDoor = this.scene.getMeshByName('open-door').createInstance("open-" + current.name)
                            openDoor.position = current.position
                            openDoor.rotation = current.rotation
                            openDoor.parent = handle
                            openDoor.isVisible = true
                            openDoor.isPickable = false
                            current.dispose()
                        }))
                    }
                }
            }

            // token if any
            if (cell.content.npc) {
                this.scene.appendNpcAt(cell.content.npc, cell.x, -cell.y)
            }

            // legend if any
            if (cell.content.legend) {
                this.scene.setLegendAtCell(k, cell.content.legend)
            }

            // pictogram if any
            if (cell.content.pictogram) {
                this.scene.setPictogramAtCell(k, cell.content.pictogram, cell.content.markerColor)
            }
        })
    }

    declareNpcToken() {
        // map token
        this.getDoc().npcToken.forEach(npc => {
            this.scene.createSpriteManager(npc.label, npc.picture)
        })
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

    create() {
        // build the scene from the model
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

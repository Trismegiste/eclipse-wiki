/*
 * Eclipse Wiki
 */

"use strict";

import BABYLON from 'babylonjs';
import PluginMeshWriter from "meshwriter";
let {MeshWriter} = PluginMeshWriter;

export class BattlemapEditor extends BABYLON.Scene {

    // sprite dictionary
    spriteManager = new Map()
    // hexagonal torus selector when clicking on tile :
    tileSelector = null
    // hexagonal cursor when hovering on tiles :
    tileCursor = null
    // pictogram dictionary
    pictogram = new Map()
    // blinking cursor for highlighting
    blinkingCursor = null

    constructor(engine) {
        super(engine)
        this.TextWriter = MeshWriter(this)
    }

    /**
     * Injects the JSON Battlemap document
     * @param {BattlemapDocument} doc
     */
    setDocument(doc) {
        this.metadata = doc
    }

    injectNpcAt(cellIndex, npcTitle) {
        const metadata = this.getTileContent(cellIndex)
        const selectedTile = this.getGroundTileByIndex(cellIndex)
        if (metadata.npc === null) {
            metadata.npc = {label: npcTitle}  // immediately update model to prevent double insertion
            // does spritemanager exist ?
            if (!this.spriteManager.has(npcTitle)) {
                // append missing sprite manager
                fetch('/npc/show.json?title=' + npcTitle).then(resp => {
                    return resp.json()
                }).then(npcInfo => {
                    this.createSpriteManager(npcTitle, npcInfo.tokenPic)
                    this.metadata.npcToken.push({label: npcTitle, picture: npcInfo.tokenPic})
                    // append sprite
                    this.appendNpcAt(metadata.npc, selectedTile.position.x, selectedTile.position.z)
                })
            } else {
                // append sprite
                // Warnin : Don't refactorize this method call with that method call just above : that method call is in a Promise
                this.appendNpcAt(metadata.npc, selectedTile.position.x, selectedTile.position.z)
            }
        }
    }

    createSpriteManager(label, picture) {
        const sp = new BABYLON.SpriteManager('token-' + label, '/picture/get/' + picture, 2000, 504)
        sp.metadata = picture
        this.spriteManager.set(label, sp)
    }

    appendNpcAt(npcContent, x, y) {
        const manager = this.spriteManager.get(npcContent.label)
        const name = npcContent.label + Math.random()
        const sprite = new BABYLON.Sprite(name, manager)
        sprite.width = 0.6
        sprite.height = 0.6
        sprite.position = new BABYLON.Vector3(x, 0.7, y)
        npcContent.npcSpritePtr = sprite
        npcContent.picture = manager.metadata
    }

    getTileContent(idx) {
        return this.metadata.grid[idx].content
    }

    getGroundTileByIndex(idx) {
        return this.getMeshByName('ground-' + idx)
    }

    deleteNpcAt(cellIndex) {
        const metadata = this.getTileContent(cellIndex)
        if (metadata.npc === null) {
            return;
        }
        const sp = metadata.npc.npcSpritePtr
        sp.dispose()
        metadata.npc = null
    }

    setPictogramAtCell(cellIndex, title, color) {
        const cell = this.metadata.grid[cellIndex]

        let picto = this.getMeshByName('picto-' + cellIndex)

        // if the new picto title is not empty
        if (title && (title.length > 0)) {

            // if the mesh for the picto does not exist, create it
            if (!picto) {
                picto = BABYLON.MeshBuilder.CreatePlane("picto-" + cellIndex, {width: 0.8, height: 0.8})
                picto.position = new BABYLON.Vector3(cell.x, 0.011, -cell.y)
                picto.rotation.x = Math.PI / 2
                picto.isPickable = false
            }

            // load the texture if non-existing
            if (!this.pictogram.has(title)) {
                const svg = new BABYLON.Texture("/picto/get?title=" + title, this.scene)
                this.pictogram.set(title, svg)
            }

            // set the material
            const mat = new BABYLON.StandardMaterial('mat-picto-' + cellIndex, this.scene)
            mat.emissiveColor = BABYLON.Color3.FromHexString(color)
            mat.opacityTexture = this.pictogram.get(title)
            mat.disableLighting = true
            picto.material = mat
            // model
            cell.content.pictogram = title
            cell.content.markerColor = color
        } else {
            // else reset
            if (picto) {
                picto.dispose()
            }
            // model
            cell.content.pictogram = null
            cell.content.markerColor = null
        }
    }

    moveSelectorToIndex(idx) {
        const cell = this.metadata.grid[idx]
        this.tileSelector.position.x = cell.x
        this.tileSelector.position.z = -cell.y
        this.tileSelector.metadata = idx

        let metadata = cell.content
        // fire an event for alpinejs :
        const detail = {...metadata}
        detail.x = cell.x
        detail.y = -cell.y
        detail.cellIndex = idx
        document.querySelector('canvas').dispatchEvent(new CustomEvent('selectcell', {"bubbles": true, detail}))
    }

    setLegendAtCell(cellIndex, message) {
        const cell = this.metadata.grid[cellIndex]

        if (cell.content.legendPtr) {
            cell.content.legendPtr.dispose()
        }

        if (message && (message.length > 0)) {
            const legendTxt = new this.TextWriter(message, {
                anchor: "center",
                "letter-height": 0.1,
                "letter-thickness": 0.001,
                "colors": {
                    diffuse: "#00ff00",
                    emissive: "#00ff00"
                },
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

    getDistance(idx1, idx2) {
        const cell1 = this.metadata.grid[idx1]
        const cell2 = this.metadata.grid[idx2]
        let dx = cell1.x - cell2.x
        let dy = cell1.y - cell2.y

        return Math.ceil(Math.sqrt(dx * dx + dy * dy) / (2 * Math.sqrt(3) / 3) - 0.05)
    }

    paintRoomAt(idx, template) {
        const roomId = this.metadata.grid[idx].content.uid

        this.metadata.grid.forEach((cell, k) => {
            if (cell.content.uid === roomId) {
                let old = this.getMeshByName("ground-" + k)
                let x = old.position.x
                let z = old.position.z
                old.dispose()
                old = null
                const ground = this.getMeshByName('hexagon-' + template).createInstance("ground-" + k)
                ground.position.x = x
                ground.position.z = z
                ground.checkCollisions = true
                // keep track of index
                ground.metadata = k
                // update document
                cell.content.template = template
            }
        })
    }

    animateBlinkingCursor(x, y) {
        this.blinkingCursor.position.x = x
        this.blinkingCursor.position.z = y
        this.blinkingCursor.material.alpha = 0

        const frameRate = 10

        const blinking = new BABYLON.Animation("blinking", "material.alpha", frameRate, BABYLON.Animation.ANIMATIONTYPE_FLOAT)
        blinking.setKeys([
            {frame: 0, value: 1},
            {frame: frameRate, value: 1},
            {frame: 3 * frameRate, value: 0}
        ])

        const growing = new BABYLON.Animation("growing", "scaling", frameRate, BABYLON.Animation.ANIMATIONTYPE_VECTOR3)
        growing.setKeys([
            {frame: 0, value: new BABYLON.Vector3(1, 1, 1)},
            {frame: frameRate, value: new BABYLON.Vector3(1, 1, 1)},
            {frame: 3 * frameRate, value: new BABYLON.Vector3(15, 15, 15)}
        ])

        this.beginDirectAnimation(this.blinkingCursor, [blinking, growing], 0, 3 * frameRate)
    }

    dumpDocumentJson() {
        const excluded = ['npcSpritePtr', 'legendPtr']
        return JSON.stringify(this.metadata, (key, value) => {
            return (-1 === excluded.indexOf(key)) ? value : undefined
        })
    }
}

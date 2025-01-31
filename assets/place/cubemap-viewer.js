/*
 * cubemap viewer - side effect
 */

import { MercureClient } from 'mercure-client';
import BABYLON from 'babylonjs';
import PluginGUI from 'babylonjs-gui';
BABYLON.GUI = PluginGUI

// global to this module
let mat = null
let scene = null

export function createCubemap(doc, canvas, jwt) {
    const centerCell = doc.grid[(doc.side + 1) * (doc.side - 1) / 2]

    const engine = new BABYLON.Engine(canvas) // Generate the BABYLON 3D engine

    // Creates Scene object
    scene = new BABYLON.Scene(engine)
    const eyePosition = new BABYLON.Vector3(0, doc.wallHeight * 2 / 3, 0)
    const camera = new BABYLON.UniversalCamera("camera1", eyePosition, scene)
    const target = eyePosition.clone()
    target.x++
    camera.setTarget(target)
    camera.minZ = 0.1
    camera.fov = Math.PI / 3
    camera.attachControl(canvas, true)
    camera.inputs.removeByType("FreeCameraKeyboardMoveInput")
    camera.inputs.attached.mouse.touchEnabled = true;
    camera.inputs.remove(camera.inputs.attached.touch);
    mat = new BABYLON.StandardMaterial("mat", scene)
    mat.emissiveTexture = new BABYLON.Texture("/img/cubemap.png", scene)

    // mapping cubemap
    var faceUV = new Array(6);
    for (let i = 0; i < 6; i++) {
        faceUV[i] = new BABYLON.Vector4((i + 1) / 6, 0, i / 6, 1);
    }

    // wrap set
    var options = {
        faceUV: faceUV,
        wrap: true,
        sideOrientation: BABYLON.Mesh.BACKSIDE,
        size: 50
    }

    const box = BABYLON.MeshBuilder.CreateBox('box', options, scene);
    box.material = mat
    box.position = eyePosition

    const tile = BABYLON.MeshBuilder.CreateDisc("hexagon", {tessellation: 6, radius: 2 / 3 - 0.01}, scene)
    tile.rotation.z = Math.PI / 6
    tile.rotation.x = Math.PI / 2
    tile.isVisible = false

    doc.grid.forEach((cell, k) => {
        const ground = tile.clone("ground-" + k)
        ground.isVisible = true
        ground.position.x = cell.x - centerCell.x
        ground.position.z = -(cell.y - centerCell.y)
        const selected = new BABYLON.StandardMaterial('selected-' + k)
        selected.emissiveColor = new BABYLON.Color3(0, 1, 0)
        selected.alpha = 0
        ground.material = selected

        ground.actionManager = new BABYLON.ActionManager(scene)
        ground.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPickTrigger, e => {
                    const current = e.meshUnderPointer
                    const client = new MercureClient(jwt)
                    client.publish('ping-position', 'relative', {deltaX: current.position.x, deltaY: current.position.z})
                    const frameRate = 10
                    const blinking = new BABYLON.Animation("blinking", "material.alpha", frameRate, BABYLON.Animation.ANIMATIONTYPE_FLOAT)
                    blinking.setKeys([
                        {frame: 0, value: 1},
                        {frame: frameRate, value: 0}
                    ])
                    current.animations.push(blinking)
                    scene.beginAnimation(current, 0, frameRate)
                })
                )
    })

    // GUI
    const playerUI = BABYLON.GUI.AdvancedDynamicTexture.CreateFullscreenUI("playerUI")
    const button = BABYLON.GUI.Button.CreateSimpleButton("but", "Fullscreen")
    button.width = "120px"
    button.height = "60px"
    button.color = "white"
    button.horizontalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT
    button.verticalAlignment = BABYLON.GUI.Control.VERTICAL_ALIGNMENT_BOTTOM
    button.onPointerClickObservable.add(() => {
        if (!engine.isFullscreen) {
            engine.enterFullscreen()
        } else {
            engine.exitFullscreen()
        }
    })
    playerUI.addControl(button)

    // Register a render loop to repeatedly render the scene
    engine.runRenderLoop(function () {
        scene.render()
    })
    // Watch for browser/canvas resize events
    window.addEventListener("resize", function () {
        engine.resize()
    })
}

/**
 * Update the texture of the cubemap
 */
export function updateEnvironment(msg) {
    mat.emissiveTexture.dispose()
    mat.emissiveTexture = BABYLON.RawTexture.LoadFromDataString('tmp', msg.data, scene)
}
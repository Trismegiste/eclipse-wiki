/*
 * Eclipse Wiki
 */

import BABYLON from 'babylonjs';
import battlemapLoader from 'battlemap-loader';
import { BattlemapEditor } from 'battlemap-editor';
BABYLON.SceneLoader.RegisterPlugin(battlemapLoader)

export default function (canvas, mapUrl, sseUrl) {
    // Generate the BABYLON 3D engine
    const engine = new BABYLON.Engine(canvas)
    // Creates Scene object
    const scene = new BattlemapEditor(engine)

    // Creates and positions a free camera for GM
    const camera = new BABYLON.UniversalCamera("gm-camera", new BABYLON.Vector3(0, 0, 0), scene)
    camera.setTarget(new BABYLON.Vector3(0, 0, -1))
    camera.attachControl(canvas)

    // Loads battlemap
    const urlPart = mapUrl.match(/(.+\/)([^\/]+)$/)
    BABYLON.SceneLoader.Append(urlPart[1], urlPart[2], scene, function (scene) { })

    // Register a render loop to repeatedly render the scene
    engine.runRenderLoop(function () {
        scene.render()
    })
    // Watch for browser/canvas resize events
    window.addEventListener("resize", function () {
        engine.resize()
    })

    // subscribing to SSE socket server for highlithing position on the map
    const feedbackSocket = new EventSource(sseUrl)
    feedbackSocket.addEventListener('relative', (msg) => {
        const position = JSON.parse(msg.data)
        const idx = scene.metadata.playerViewOnTileIndex
        if (idx !== null) {
            const ground = scene.metadata.grid[idx]
            scene.animateBlinkingCursor(ground.x + position.deltaX, position.deltaY - ground.y)
        }
    })
    feedbackSocket.addEventListener('indexed', (msg) => {
        const position = JSON.parse(msg.data)
        const ground = scene.metadata.grid[parseInt(position.cell)]
        scene.animateBlinkingCursor(ground.x, -ground.y)
    })

    return scene
}

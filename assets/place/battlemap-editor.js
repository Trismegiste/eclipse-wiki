/*
 * Eclipse Wiki
 */

import BABYLON from 'babylonjs';
import battlemapLoader from 'battlemap-loader';
BABYLON.SceneLoader.RegisterPlugin(battlemapLoader)

export default function (canvas, mapUrl) {
    const urlPart = mapUrl.match(/(.+\/)([^\/]+)$/)
    const engine = new BABYLON.Engine(canvas) // Generate the BABYLON 3D engine

    // Creates Scene object
    const scene = new BABYLON.Scene(engine)

    // Creates and positions a free camera for GM
    const camera = new BABYLON.UniversalCamera("gm-camera", new BABYLON.Vector3(0, 0, 0), scene)
    camera.setTarget(new BABYLON.Vector3(0, 0, -1))
    camera.attachControl(canvas)

    BABYLON.SceneLoader.Append(urlPart[1], urlPart[2], scene, function (scene) { })

    // Register a render loop to repeatedly render the scene
    engine.runRenderLoop(function () {
        scene.render()
    })
    // Watch for browser/canvas resize events
    window.addEventListener("resize", function () {
        engine.resize()
    })

    return scene
}
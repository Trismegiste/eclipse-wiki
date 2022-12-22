/*
 * eclipse-wiki
 */

const battlemapLoader = {
    extensions: ".battlemap",
    importMesh: function (meshesNames, scene, data, rootUrl, meshes, particleSystems, skeletons) {
        return true
    },
    loadAssets(scene, data, rootUrl) {
        var container = new AssetContainer(scene)
        return container
    },
    load: function (scene, data, rootUrl) {
        const battlemap = JSON.parse(data)

        this.setCamera(scene, battlemap.side)
        this.setLight(scene)

        // map token
        let spriteManager = {}
        battlemap.npc.forEach((npc) => {
            spriteManager[npc.label] = new BABYLON.SpriteManager('token-' + npc.label, '/picture/get/' + npc.picture, 2000, 504)
        })

        // Grid of HexaCell
        battlemap.grid.forEach((cell, k) => {
            const ground = scene.getMeshByName('hexagon-' + cell.obj.template).createInstance("ground" + k)
            ground.position.x = cell.x
            ground.position.z = -cell.y
            ground.checkCollisions = true

            ground.actionManager = new BABYLON.ActionManager(scene);
            ground.actionManager.registerAction(
                    new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPointerOverTrigger, (e) => {
                        const selector = scene.getMeshByName('selector-ground')
                        const current = e.meshUnderPointer
                        selector.isVisible = true
                        selector.position.x = current.position.x
                        selector.position.z = current.position.z
                    })
                    )

            for (let dir = 0; dir < 6; dir++) {
                if (cell.obj.wall[dir]) {
                    const handle = new BABYLON.TransformNode("handle" + k + '-' + dir)
                    const meshName = cell.obj.door[dir] ? 'door' : 'wall-' + cell.obj.template
                    const tmpWall = scene.getMeshByName(meshName).createInstance("wall-" + k + '-' + dir)
                    tmpWall.parent = handle
                    tmpWall.checkCollisions = !cell.obj.door[dir]
                    handle.rotation.y = -dir * Math.PI / 3
                    handle.position.x = cell.x
                    handle.position.z = -cell.y

                    // clickable door
                    if (cell.obj.door[dir]) {
                        tmpWall.actionManager = new BABYLON.ActionManager(scene);
                        tmpWall.actionManager.registerAction(new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPickTrigger, (e) => {
                            const current = e.meshUnderPointer
                            current.isVisible = false
                        }))
                    }
                }
            }

            // token if any
            if (cell.obj.npc) {
                const manager = spriteManager[cell.obj.npc.label]
                const npc = new BABYLON.Sprite("npc-" + k, manager)
                npc.width = 0.5
                npc.height = 0.5
                npc.position = new BABYLON.Vector3(cell.x, 0.75, -cell.y)
            }
        })

        return true
    },
    setCamera: function (scene, side) {
        const camera = scene.getCameraByName('player-camera')
        camera.position = new BABYLON.Vector3(side / 2, side, -side / 2)
        camera.setTarget(new BABYLON.Vector3(side / 2, 0, -side / 2))
        camera.minZ = 0.01
        camera.maxZ = side * 2
        camera.fov = 60 / 180 * Math.PI
        // Then apply collisions and gravity to the active camera
        camera.checkCollisions = true;
        camera.applyGravity = true;
        //Set the ellipsoid around the camera (e.g. your player's size)
        camera.ellipsoid = new BABYLON.Vector3(0.1, wallHeight / 3, 0.1);
    },
    setLight: function (scene) {
        // Creates a light, aiming 0,1,0 - to the sky
        const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), scene)
        light.intensity = 0.9
    }
}

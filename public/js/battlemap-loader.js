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

        this.createCamera(scene, battlemap.side)

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
    createCamera: function (scene, side) {
        const camera = scene.getCameraByName('player-camera')
        camera.position.x = side / 2
        camera.position.y = side
        camera.position.z = -side / 2
        camera.setTarget(new BABYLON.Vector3(side / 2, 0, -side / 2));
        camera.maxZ = side * 2;

    }
}

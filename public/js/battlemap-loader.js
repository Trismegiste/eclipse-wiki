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
        const layer = scene.getHighlightLayerByName('highlighting')

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
                    const tmpWall = scene.getMeshByName('wall-' + cell.obj.template).createInstance("wall-" + k + '-' + dir)
                    tmpWall.parent = handle
                    //tmpWall.checkCollisions = true
                    handle.rotation.y = -dir * Math.PI / 3
                    handle.position.x = cell.x
                    handle.position.z = -cell.y
                }
            }
        })

        return true
    }
}
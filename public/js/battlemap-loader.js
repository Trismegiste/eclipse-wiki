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
        const battlemap = Object.assign(new Battlemap(), JSON.parse(data))

        battlemap.setCamera(scene)
        battlemap.setLight(scene)
        battlemap.createGround(scene)
        battlemap.createWall(scene)
        battlemap.createSelector(scene)
        battlemap.createDoor(scene)
        battlemap.build(scene)

        return true
    }
}

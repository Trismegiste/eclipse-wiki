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
        const battlemap = Object.assign(new BattlemapBuilder(scene), JSON.parse(data))

        battlemap.create()

        return true
    }
}